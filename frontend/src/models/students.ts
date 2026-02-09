export interface Student {
  id: number
  country_id: number
  province_id: number
  territory_id: number
  commune_id: number
  school_id: number
  classroom_id: number
  type_id: number
  father_id: number
  mother_id: number
  academic_level_id: number
  matricule: string
  name: string
  lastname: string
  firstname: string
  gender: 'Masculin' | 'Féminin' | string
  civil_status: 'Célibataire' | 'Marié' | string
  address: string
  birth_date: string // ISO date string
  birth_place: string
  identity_card: string
  phone_number: string
  email: string
  deleted_at: string | null
  created_at: string // ISO date string
  updated_at: string // ISO date string
  academic_personal_id: number
}
