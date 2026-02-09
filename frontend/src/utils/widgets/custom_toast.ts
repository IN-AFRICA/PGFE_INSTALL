import { toast } from 'vue-sonner'
type ToastType = 'success' | 'error'

interface CustomToastOptions {
  message: string
  type?: ToastType
  autoClose?: number
}

export function showCustomToast({
  message,
  type = 'success',
  autoClose = 3000,
}: CustomToastOptions) {
  let options: any = { autoClose }

  if (type === 'error') {
    options = {
      ...options,
      style: {
        background: '#fee2e2',
        color: '#991b1b',
        border: '1px solid #fca5a5',
      },
      class: 'border-red-300',
      descriptionClass: 'text-red-800',
    }
    toast.error(message, options)
  } else if (type === 'success') {
    options = {
      ...options,
      style: {
        background: '#dcfce7',
        color: '#166534',
        border: '1px solid #86efac',
      },
      class: 'border-green-300',
      descriptionClass: 'text-green-800',
    }
    toast.success(message, options)
  } else {
    toast(message, options)
  }
}
