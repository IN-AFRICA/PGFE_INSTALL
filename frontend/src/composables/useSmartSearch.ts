import { ref, computed, watch, type Ref, type ComputedRef } from 'vue'

export interface SmartSearchOptions<T> {
  data: Ref<T[] | null | undefined>
  searchableFields?: (keyof T)[]
  mode?: 'local' | 'backend' | 'hybrid'
  debounceMs?: number
  onBackendSearch?: (query: string) => void | Promise<void>
  caseSensitive?: boolean
  minQueryLength?: number
}

export interface SmartSearchReturn<T> {
  query: Ref<string>
  filteredData: ComputedRef<T[]>
  isSearching: Ref<boolean>
  clearSearch: () => void
  hasResults: ComputedRef<boolean>
  resultCount: ComputedRef<number>
}

export function useSmartSearch<T extends Record<string, any>>(
  options: SmartSearchOptions<T>,
): SmartSearchReturn<T> {
  const {
    data,
    searchableFields,
    mode = 'local',
    debounceMs = 300,
    onBackendSearch,
    caseSensitive = false,
    minQueryLength = 1,
  } = options

  const query = ref<string>('')
  const isSearching = ref(false)
  let debounceTimeout: ReturnType<typeof setTimeout> | null = null

  const normalizeValue = (value: any): string => {
    if (value === null || value === undefined) return ''
    if (typeof value === 'object') {
      if (value.name) return String(value.name)
      if (value.label) return String(value.label)
      return JSON.stringify(value)
    }
    return String(value)
  }

  const matchesQuery = (item: T, searchQuery: string): boolean => {
    const normalizedQuery = caseSensitive ? searchQuery : searchQuery.toLowerCase()

    if (searchableFields && searchableFields.length > 0) {
      return searchableFields.some((field) => {
        const value = normalizeValue(item[field])
        const normalizedValue = caseSensitive ? value : value.toLowerCase()
        return normalizedValue.includes(normalizedQuery)
      })
    }

    return Object.values(item).some((value) => {
      const normalizedValue = caseSensitive
        ? normalizeValue(value)
        : normalizeValue(value).toLowerCase()
      return normalizedValue.includes(normalizedQuery)
    })
  }

  const filteredData = computed<T[]>(() => {
    const items = data.value
    if (!items || !Array.isArray(items)) return []

    const searchQuery = query.value.trim()

    if (!searchQuery || searchQuery.length < minQueryLength) {
      return items
    }

    if (mode === 'backend') {
      return items
    }

    return items.filter((item) => matchesQuery(item, searchQuery))
  })

  const hasResults = computed(() => filteredData.value.length > 0)
  const resultCount = computed(() => filteredData.value.length)

  const triggerBackendSearch = (searchQuery: string) => {
    if (onBackendSearch && (mode === 'backend' || mode === 'hybrid')) {
      isSearching.value = true
      Promise.resolve(onBackendSearch(searchQuery)).finally(() => {
        isSearching.value = false
      })
    }
  }

  watch(query, (newQuery) => {
    if (debounceTimeout) {
      clearTimeout(debounceTimeout)
    }

    const trimmedQuery = newQuery.trim()

    if (mode === 'backend' || mode === 'hybrid') {
      if (trimmedQuery.length >= minQueryLength || trimmedQuery.length === 0) {
        debounceTimeout = setTimeout(() => {
          triggerBackendSearch(trimmedQuery)
        }, debounceMs)
      }
    }
  })

  const clearSearch = () => {
    query.value = ''
    if (mode === 'backend' || mode === 'hybrid') {
      triggerBackendSearch('')
    }
  }

  return {
    query,
    filteredData,
    isSearching,
    clearSearch,
    hasResults,
    resultCount,
  }
}

export function useLocalSearch<T extends Record<string, any>>(
  data: Ref<T[] | null | undefined>,
  searchableFields?: (keyof T)[],
) {
  return useSmartSearch<T>({
    data,
    searchableFields,
    mode: 'local',
    debounceMs: 150,
  })
}

export function useBackendSearch<T extends Record<string, any>>(
  data: Ref<T[] | null | undefined>,
  onSearch: (query: string) => void | Promise<void>,
  debounceMs = 500,
) {
  return useSmartSearch<T>({
    data,
    mode: 'backend',
    debounceMs,
    onBackendSearch: onSearch,
  })
}

export function useHybridSearch<T extends Record<string, any>>(
  data: Ref<T[] | null | undefined>,
  onBackendSearch: (query: string) => void | Promise<void>,
  searchableFields?: (keyof T)[],
  debounceMs = 400,
) {
  return useSmartSearch<T>({
    data,
    searchableFields,
    mode: 'hybrid',
    debounceMs,
    onBackendSearch,
  })
}
