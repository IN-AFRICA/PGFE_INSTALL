import { AcademicLevel } from './academic_level'
import { Filiere } from './filiere'

export interface Cycle {
  id: number
  filiaire_id: string
  name: string
  created_at: string
  updated_at: string
  filiaire?: Filiere
  academic_levels?: AcademicLevel[]
}
