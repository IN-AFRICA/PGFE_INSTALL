export interface IndisciplineCase {
  id: number
  date: string
  student_id: string
  fault_count: string
  action: string
  roi: string | null
  classroom_id: string
  filiere_id: string | null
  school_year_id: string
  created_at: string
  updated_at: string
}
