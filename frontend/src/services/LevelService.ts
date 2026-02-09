import { BASE_URL } from '@/utils/constants/api_route'
import { loadAuthState } from '@/stores/auth'
import axios from 'axios'

const API_URL = `${BASE_URL}academic/levels`

const token = loadAuthState()?.token

const headers = {
  Accept: 'application/json',
  'Content-Type': 'application/json',
  Authorization: `Bearer ${token}`,
}

export default {
  getAllLevels() {
    return axios.get(API_URL, { headers })
  },

  getLevelById(id: number | string) {
    return axios.get(`${API_URL}/${id}`, { headers })
  },

  createLevel(data: any) {
    return axios.post(API_URL, data, { headers })
  },

  updateLevel(id: number | string, data: any) {
    return axios.put(`${API_URL}/${id}`, data, { headers })
  },

  deleteLevel(id: number | string) {
    return axios.delete(`${API_URL}/${id}`, { headers })
  },
}
