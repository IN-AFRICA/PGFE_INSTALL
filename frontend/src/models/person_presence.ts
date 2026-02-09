export interface Personnel {
  id: number
  user_id: string
  mechanisation_id: string | null
  country_id: string
  province_id: string
  territory_id: string
  commune_id: string
  school_id: string
  type_id: string
  father_id: string
  mother_id: string
  academic_level_id: string
  fonction_id: string
  matricule: string
  name: string
  post_name: string
  pre_name: string
  username: string
  phone: string
  email: string
  image: string | null
  identity_card_number: string
  gender: string
  civil_status: string
  physical_address: string
  birth_date: string
  birth_place: string
  created_at: string
  updated_at: string
  image_url: string | null
}

export interface School {
  id: number
  country_id: string
  city: string
  province_id: number
  name: string
  address: string
  latitude: number
  type_id: string
  longitude: number
  phone_number: string
  email: string
  logo: string | null
  created_at: string
  updated_at: string
}

export interface PersonPresence {
  id: number
  personnel_id: number
  presence: boolean
  sick: boolean
  absences_justified: string
  school_id: number
  author_id: number
  created_at: string
  updated_at: string
  absent_justified: boolean
  personnel: Personnel
  school: School
}
