export interface AcademicPersonal {
  id: number
  name: string
  pre_name: string
  post_name: string
  matricule: string | undefined
}

export interface SchoolYear {
  id: number
  name: string
}

export interface Semester {
  id: number
  name: string
}

export interface Evaluation {
  id: number
  critiques: string
  c1_quantite_travail: string
  c2_theorie_pratique: string
  c3_determ_ress_perso: string
  c4_ponctualite: string
  c5_dr_att_posit_collab: string
  academic_personal_id: string
  school_year_id: string
  semester_id: string
  school_id: string
  author_id: string
  created_at: string
  updated_at: string
  academic_personal: AcademicPersonal | undefined
  school_year: SchoolYear | undefined
  semester: Semester | undefined
}
