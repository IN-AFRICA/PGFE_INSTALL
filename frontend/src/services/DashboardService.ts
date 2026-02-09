import { API_ROUTES } from '@/utils/constants/api_route'
import api from './api'

export interface DashboardStats {
  total: string | number
  girls: string | number
  boys: string | number
  filtered: {
    gender: string
    count: string | number
  } | null
  by_school_year: any // Can be string or Record depending on backend version
  school_year_id: number
  school_year_total: string | number
  school_year_name?: string
}

export interface DashboardResponse {
  total_students: string | number
  data: DashboardStats
  success: boolean
  message: string
}

export interface DashboardFilters {
  school_year_id?: number
  classroom_id?: number
  filiere_id?: number // Corrected spelling based on document(3).json
  filiaire_id?: number // Keep for compatibility
  gender?: string
  id?: string | number // student id filter
}

export class DashboardService {
  static async getStats(filters?: DashboardFilters): Promise<DashboardResponse> {
    const params: Record<string, any> = {}

    if (filters?.school_year_id) params.school_year_id = filters.school_year_id
    if (filters?.classroom_id) params.classroom_id = filters.classroom_id
    if (filters?.filiere_id || filters?.filiaire_id) {
      params.filiere_id = filters?.filiere_id || filters?.filiaire_id
    }
    if (filters?.gender) params.gender = filters.gender
    if (filters?.id) params.id = filters.id

    // Log the EXACT call for debugging
    console.log('DashboardService - Calling GET:', API_ROUTES.GET_DASHBOARD, 'with params:', params)

    const response = await api.get<DashboardResponse>(API_ROUTES.GET_DASHBOARD, { params })
    return response.data
  }
}

export default DashboardService
