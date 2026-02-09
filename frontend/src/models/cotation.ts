import { PeriodNote } from './periode_note'

export interface Cotation {
  id: number
  school_year_id: number
  student_id: number
  classroom_id: number
  course_id: number
  note: string | PeriodNote
  created_at: string
  updated_at: string
  deliberation_id: number
}
