export interface StudentRegistered {
  id: number
  school_id: string
  classroom_id: string
  student_id: string
  school_year_id: string
  academic_personal_id: string
  academic_level_id: string
  type_id: string
  registration_date: string
  registration_status: boolean
  note: string | null
  created_at: string
  updated_at: string
  filiaire_id: string
  cycle_id: string
  student_name: string
  classroom_name: string
  academic_level_name: string
  student: {
    id: number
    province_id: string
    territory_id: string
    commune_id: string
    parents_id: string
    parent2_id: string | null
    parent3_id: string | null
    matricule: string
    name: string
    firstname: string
    lastname: string
    gender: string
    civil_status: string
    address: string
    birth_date: string
    birth_place: string
    phone_number: string
    email: string
    image: string | null
    deleted_at: string | null
    created_at: string
    updated_at: string
    school_id: string
    country_id: string
  }
  academic_level: {
    id: number
    cycle_id: string
    name: string
    created_at: string
    updated_at: string
  }
  classroom: {
    id: number
    academic_level_id: string
    name: string
    indicator: string | null
    created_at: string
    updated_at: string
    titulaire_id: string | null
  }
  filiaire: {
    id: number
    school_id: string
    name: string
    code: string
    created_at: string
    updated_at: string
    academic_level_id: string | null
  }
  cycle: {
    id: number
    filiaire_id: string
    name: string
    created_at: string
    updated_at: string
  }
}
