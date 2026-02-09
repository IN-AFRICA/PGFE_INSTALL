import { Classroom } from './classroom'
import { Cycle } from './cycle'

export interface AcademicLevel {
  id: number
  cycle_id: string
  name: string
  created_at: string
  updated_at: string
  cycle?: Cycle
  classrooms?: Classroom[]
}
