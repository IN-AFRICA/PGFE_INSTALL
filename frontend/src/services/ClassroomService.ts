// services/abandonCasesService.js
import { BASE_URL, API_ROUTES } from '@/utils/constants/api_route'
import { loadAuthState } from '@/stores/auth'
import axios from 'axios'

const API_URL = `${BASE_URL}${API_ROUTES.GET_CLASSROOMS}?per_page=1000`

const token = loadAuthState()?.token

const headers = {
  Accept: 'application/json',
  'Content-Type': 'application/json',
  Authorization: `Bearer ${token}`,
}

export default {
  getAllClassroom(params: { search?: string } = {}) {
    return axios.get(API_URL, { headers, params })
  },
}
