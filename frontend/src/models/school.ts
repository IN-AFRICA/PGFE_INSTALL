export interface School {
  id: number
  country_id: string
  city: string
  province_id: string | null
  name: string
  address: string
  latitude: string | null
  type_id: string
  longitude: string | null
  phone_number: string | null
  email: string
  logo: string | null
  created_at: string
  updated_at: string
}
