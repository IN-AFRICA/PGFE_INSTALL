import axios from 'axios'
import { BASE_URL } from '@/utils/constants/api_route'

// Configuration Axios centralisée et réutilisable
export const axiosConfig = {
  baseURL: BASE_URL,
  timeout: 10000,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
}

// Instance Axios par défaut avec configuration
const axiosInstance = axios.create(axiosConfig)

export default axiosInstance
export { axios }
