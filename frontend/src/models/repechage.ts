import { PeriodNote } from './periode_note'

export interface Repechage {
  id: number
  student_id: string
  full_name: string | null
  classroom_id: string
  course_id: string
  school_year_id: string
  score_percent: number | null
  note: PeriodNote
  pourcentage: number
  created_at: string
  updated_at: string
}
