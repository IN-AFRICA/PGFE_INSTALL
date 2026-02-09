import type { AcademicPersonal } from './academic_personal'
import type { Classroom } from './classroom'
import type { School } from './school'

export interface Visit {
  id: number
  personal_id: string
  classroom_id: string
  school_id: string
  subject: string
  visiteur: string
  cot_doc_prof: number
  cot_doc_eleve: number | null
  cot_meth_proc: number
  cot_matiere: number
  cot_march_lecon: number
  cot_enseignant: number
  cot_eleve: number
  visit_hour: string
  datefin: string
  summary: string
  created_at: string
  updated_at: string
  fonction_id: string
  academic_personal?: AcademicPersonal | null
  classroom?: Classroom | null
  school?: School | null
}
