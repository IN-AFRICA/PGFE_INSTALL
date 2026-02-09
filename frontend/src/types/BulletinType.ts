// Types pour le système de bulletin scolaire (mis à jour selon l'API réelle)

export interface Ecole {
  id: string | number
  name: string
  code?: string
  provinceEducationnelle?: string
  ville?: string // Conserver pour compatibilité interne si nécessaire, ou mapper vers city
  city?: string
  commune?: string
  address?: string
  email?: string
  phone_number?: string
  province?: {
    id: number
    name: string
  }
}

export interface StudentInfo {
  id: number
  full_name: string
  lastname: string
  firstname: string
  gender: string
  birth_date: string
  birth_place: string
  matricule: string
  address: string
  phone_number: string
  email: string
  image_url: string | null
}

export interface Registration {
  school: Ecole
  classroom: { id: number; name: string }
  filiere: { id: number; name: string }
  academic_level: { id: number; name: string }
  cycle: { id: number; name: string }
  school_year: { id: number; name: string }
  type: { id: number; name: string }
}

export interface GradeNote {
  P1: number | null
  P2: number | null
  P3: number | null
  P4: number | null
  E1: number | null
  S1?: number | null // Total obtenu Semestre 1
  E2: number | null
  S2?: number | null // Total obtenu Semestre 2
}

export interface GradeMaxima {
  P1: string | number
  P2: string | number
  P3: string | number
  P4: string | number
  E1: string | number
  E2: string | number
  TOT1?: string | number // Maxima Semestre 1
  TOT2?: string | number // Maxima Semestre 2
  TG?: string | number // Maxima Général
}

export interface Grade {
  id: number
  course_id: string | number
  course: string
  note: GradeNote
  maxima: GradeMaxima
  total_obtained: number
  total_maxima: number
  sem1_total: number
  sem1_maxima: number
  sem2_total: number
  sem2_maxima: number
  average_percent: number
  average_on_20: number
}

export interface ConduiteEntry {
  id: number
  fault_count: number
  conduite_semester_1: string
  conduite_semester_2: string
}

export interface DeliberationEntry {
  id: number
  average: number | null
  is_validated: '1' | '0'
  school_year_id: string | number
}

export interface BulletinSummary {
  class_size: number
  rank: number
  position?: {
    rank: number
    out_of: number
  }
  place?: string
  overall_percent: number
  overall_on_20: number
  period_exam_totals?: {
    obtained: {
      P1: number
      P2: number
      P3: number
      P4: number
      E1: number
      E2: number
    }
    maxima: {
      P1: number
      P2: number
      P3: number
      P4: number
      E1: number
      E2: number
    }
  }
  period_exam_ranks?: {
    P1: number
    P2: number
    P3: number
    P4: number
    E1: number
    E2: number
  }
  period_exam_place?: {
    P1: { rank: number; out_of: number; label: string; percent: number; on_20: number }
    P2: { rank: number; out_of: number; label: string; percent: number; on_20: number }
    P3: { rank: number; out_of: number; label: string; percent: number; on_20: number }
    P4: { rank: number; out_of: number; label: string; percent: number; on_20: number }
    E1: { rank: number; out_of: number; label: string; percent: number; on_20: number }
    E2: { rank: number; out_of: number; label: string; percent: number; on_20: number }
  }
  semester_totals?: {
    S1: { obtained: number; maxima: number }
    S2: { obtained: number; maxima: number }
  }
  semester_ranks?: {
    S1: number
    S2: number
  }
  semester_place?: {
    S1: { rank: number; out_of: number; label: string; percent: number; on_20: number }
    S2: { rank: number; out_of: number; label: string; percent: number; on_20: number }
  }
  application: string | null
  conduite: Array<{
    fault_count: number
    sem1: string
    sem2: string
  }>
  deliberation: string
  repechage: boolean
  school_code: string | null
}

export interface BulletinResponse {
  generated_at: string
  student: StudentInfo
  registration: Registration
  grades: Grade[]
  repechages: any[]
  conduite: ConduiteEntry[]
  deliberations: DeliberationEntry[]
  summary: BulletinSummary
}

// Pour compatibilité avec les composants existants
export interface BulletinData {
  eleve: StudentInfo
  registration: Registration
  anneeScolaire: string
}

export interface BulletinNotes extends BulletinResponse {}

// Pour les composants UI
export interface SelectOption {
  value: string
  label: string
}
