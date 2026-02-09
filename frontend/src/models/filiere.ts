import { Cycle } from './cycle'
import { School } from './school'

export interface Filiere {
  id: number
  school_id: string
  name: string
  code: string
  created_at: string
  updated_at: string
  academic_level_id: string | null
  school?: School
  cycles?: Cycle[]
}
