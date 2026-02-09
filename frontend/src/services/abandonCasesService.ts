// services/abandonCasesService.js
import { BASE_URL, API_ROUTES } from '@/utils/constants/api_route'
import { loadAuthState } from '@/stores/auth'
import type { AbandonCasePayload } from '@/types'
import axios from 'axios'

const API_URL = `${BASE_URL}${API_ROUTES.GET_ABANDON_CASES}`

const token = loadAuthState()?.token

const headers = {
  Accept: 'application/json',
  'Content-Type': 'application/json',
  Authorization: `Bearer ${token}`,
}
export default {
  /**
   * Create a new abandon case
   */
  createAbandonCase(payload: AbandonCasePayload) {
    return axios.post(API_URL, payload, { headers })
  },

  /**
   * Get all abandon cases
   */
  getAllAbandonCases(params: Record<string, any> = {}) {
    return axios.get(API_URL, { headers, params })
  },

  /**
   * Get a single abandon case by ID
   * @param {number|string} id
   */
  getAbandonCaseById(id: number) {
    return axios.get(`${API_URL}/${id}`, { headers })
  },

  /**
   * Update an abandon case
   * @param {number|string} id
   * @param {Object} payload
   */
  updateAbandonCase(id: number, payload: AbandonCasePayload) {
    return axios.put(`${API_URL}/${id}`, payload, { headers })
    // or axios.patch if backend supports partial update
  },

  /**
   * Delete an abandon case
   * @param {number|string} id
   */
  deleteAbandonCase(id: number) {
    return axios.delete(`${API_URL}/${id}`, { headers })
  },
}
