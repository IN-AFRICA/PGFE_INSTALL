// services/abandonCasesService.js
import { BASE_URL } from '@/utils/constants/api_route'
import { loadAuthState } from '@/stores/auth'
import axios from 'axios'

const API_URL = `${BASE_URL}school/filiaires/lists`

const token = loadAuthState()?.token

const headers = {
  Accept: 'application/json',
  'Content-Type': 'application/json',
  Authorization: `Bearer ${token}`,
}

export default {
  getAllFiliaire() {
    return axios.get(API_URL, { headers })
  },
}
