// ==================== Shared Types ====================
export interface ClassComptable {
  id: number
  name: string
  code: string
  user_id?: number | null
  created_at?: string
}

export interface CategorieComptable {
  id: number
  name: string
  created_at?: string
}

export interface ApiResponse {
  success: boolean
  message: string
  data?: string
}
