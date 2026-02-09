import { BASE_URL } from '@/utils/constants/api_route.ts'
import { loadAuthState } from '@/stores/auth'
import axios from 'axios'

const API_URL = `${BASE_URL}presence/student-exits`

const token = loadAuthState()?.token

const headers = {
  Accept: 'application/json',
  'Content-Type': 'application/json',
  Authorization: `Bearer ${token}`,
}

export default {
  getAllStudentExits() {
    return axios.get(API_URL, { headers })
  },
  deleteStudentExit(id: number) {
    return axios.delete(`${API_URL}/${id}`, { headers })
  },
  getStudentExitById(id: number) {
    return axios.get(`${API_URL}/${id}`, { headers })
  },
  createStudentExit(data: any) {
    return axios.post(API_URL, data, { headers })
  },
  updateStudentExit(id: number, data: any) {
    return axios.put(`${API_URL}/${id}`, data, { headers })
  },
}
