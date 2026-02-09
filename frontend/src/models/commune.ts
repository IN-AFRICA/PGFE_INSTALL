interface Province {
  id: number
  country_id: string
  name: string
  created_at: string
  updated_at: string
}

export interface Commune {
  id: number
  province_id: string
  name: string
  created_at: string
  updated_at: string
  province: Province
}
