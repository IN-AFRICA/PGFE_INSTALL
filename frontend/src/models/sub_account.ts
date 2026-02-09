import type { Account } from './account'

export interface SubAccount {
  id: number
  name: string
  code: string
  account_plan_id: string
  created_at: string
  updated_at: string
  account_plan: Account
}
