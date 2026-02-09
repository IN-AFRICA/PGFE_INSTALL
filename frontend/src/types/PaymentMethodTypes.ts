import { z } from 'zod'

export interface PaymentMethod {
  id: number
  name: string
  code: string
  created_at?: string
  updated_at?: string
}

export interface PaymentMethodsApiResponse {
  success: boolean
  message: string
  data?: PaymentMethod[]
  method?: string
}

export type PaymentMethodsGetResponse =
  | PaymentMethod[]
  | { data: PaymentMethod[] }
  | { methods: PaymentMethod[] }

export const paymentMethodSchema = z.object({
  name: z
    .string()
    .min(1, 'Le nom est requis')
    .max(255, 'Le nom ne peut pas dépasser 255 caractères'),
  code: z
    .string()
    .min(1, 'Le code est requis')
    .max(50, 'Le code ne peut pas dépasser 50 caractères'),
})

export type PaymentMethodFormData = z.infer<typeof paymentMethodSchema>
