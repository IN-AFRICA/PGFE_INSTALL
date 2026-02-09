import type { Country } from './country'

export interface Province {
  id: number
  name: string
  country_id: number
  created_at: string
  updated_at: string
  country: Country
}
