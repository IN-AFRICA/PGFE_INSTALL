import { ref, watch } from 'vue'

export function useSearch<T>(fetchFunction: (params: { search: string }) => void, delay = 500) {
  const query = ref<string>('')
  let timeout: ReturnType<typeof setTimeout>

  watch(query, (newVal) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
      fetchFunction({ search: newVal })
    }, delay)
  })

  return { query }
}
