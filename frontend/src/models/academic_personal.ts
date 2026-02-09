export interface AcademicPersonal {
  id: number
  user_id: string
  mechanisation_id: number | null
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
  identity_card_number: string
  gender: string
  civil_status: string
  physical_address: string
  birth_date: string
  birth_place: string
  created_at: string
  updated_at: string
}

export interface AcademicPersonalRelationName {
  id: number
  name?: string
  title?: string
  description?: string
  created_at?: string
  updated_at?: string
}

export interface AcademicPersonalSchool {
  id: number
  name: string
  address?: string | null
  city?: string | null
  country_id?: number | string | null
  province_id?: number | string | null
  type_id?: number | string | null
  email?: string | null
  phone_number?: string | null
  logo?: string | null
  latitude?: string | null
  longitude?: string | null
  created_at?: string
  updated_at?: string
}

export interface AcademicPersonalUser {
  id: number
  name: string
  email: string
  email_verified_at?: string | null
  last_login_at?: string | null
  school_id?: number | null
  created_at?: string
  updated_at?: string
}

export interface AcademicPersonalApi {
  id: number
  user_id: number | string
  mechanisation_id: number | null
  country_id: number | string
  province_id: number | string
  territory_id: number | string
  commune_id: number | string
  school_id: number | string
  type_id: number | string
  father_id: number | string
  mother_id: number | string
  academic_level_id: number | string
  fonction_id: number | string
  matricule: string
  name: string
  post_name?: string | null
  pre_name?: string | null
  firstname?: string | null
  username: string
  phone?: string | null
  phone_number?: string | null
  email: string
  identity_card_number?: string | null
  identity_card?: string | null
  gender: string
  civil_status: string
  physical_address?: string | null
  address?: string | null
  birth_date: string
  birth_place: string
  image?: string | null
  created_at?: string
  updated_at?: string

  country?: AcademicPersonalRelationName
  province?: AcademicPersonalRelationName
  territory?: AcademicPersonalRelationName
  commune?: AcademicPersonalRelationName
  school?: AcademicPersonalSchool
  type?: AcademicPersonalRelationName
  academic_level?: AcademicPersonalRelationName
  fonction?: AcademicPersonalRelationName
  user?: AcademicPersonalUser
}

export interface GetAcademicPersonalResponse {
  data: AcademicPersonalApi
  message?: string
}
