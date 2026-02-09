import { ref, watch } from 'vue'

type FetchFunction = (params: Record<string, any>) => void

export function usePagination(
  fetchFunction: FetchFunction,
  initialPage: number = 1,
  perPage: number = 15,
  options?: {
    pageParam?: string
    perPageParam?: string
  },
) {
  const page = ref<number>(initialPage)
  const perPageCount = ref<number>(perPage)
  const total = ref<number>(0)
  const additionalParams = ref<Record<string, any>>({})

  const pageParam = options?.pageParam ?? 'page'
  const perPageParam = options?.perPageParam ?? 'limit'

  watch([page, perPageCount], () => {
    fetchFunction({
      [pageParam]: page.value,
      [perPageParam]: perPageCount.value,
      ...additionalParams.value,
    })
  })

  const setAdditionalParams = (params: Record<string, any>) => {
    additionalParams.value = params
  }

  return { page, perPageCount, total, setAdditionalParams }
}
