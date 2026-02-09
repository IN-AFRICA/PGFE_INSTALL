export interface BreadcrumbItem {
  label: string
  href?: string
  icon?: string
  isActive?: boolean
}

export type BreadcrumbProps = {
  items: BreadcrumbItem[]
  homeIcon?: string
  separatorIcon?: string
}

export interface AbandonCasePayload {
  school_year_id: number // required
  classroom_id: number // required
  filiere_id: number | null // optional (can be null)
  semester_id: number // required
  student_id: number // required
  comment: string | null // optional (can be null)
}
