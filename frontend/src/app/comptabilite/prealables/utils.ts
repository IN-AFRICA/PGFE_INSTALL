// ==================== Utility Functions ====================
/**
 * Normalise la réponse API en tableau
 * Gère les différents formats de réponse possibles
 */
export function normalizeApiResponse<T>(data: unknown, alternateKey?: string): T[] {
  if (Array.isArray(data)) return data
  if (data && typeof data === 'object' && 'data' in data && Array.isArray(data.data))
    return data.data
  if (
    alternateKey &&
    data &&
    typeof data === 'object' &&
    alternateKey in data &&
    Array.isArray((data as Record<string, unknown>)[alternateKey])
  ) {
    return (data as Record<string, unknown>)[alternateKey] as T[]
  }
  if (data && typeof data === 'object' && !Array.isArray(data)) {
    return Object.values(data).filter(
      (item): item is T => !!item && typeof item === 'object' && 'id' in item,
    )
  }
  return []
}

/**
 * Extrait le message de succès d'une réponse API
 */
export function extractSuccessMessage(response: unknown, defaultMessage: string): string {
  if (typeof response === 'string') return response || defaultMessage
  if (response && typeof response === 'object' && 'message' in response) {
    return String(response.message) || defaultMessage
  }
  return defaultMessage
}

/**
 * Vérifie si une réponse API indique un succès
 */
export function isApiSuccess(response: unknown, error: unknown): boolean {
  if (error) return false
  if (
    response &&
    typeof response === 'object' &&
    'success' in response &&
    response.success === true
  )
    return true
  return !!response
}

/**
 * Formate une date en format français
 */
export function formatDate(dateString?: string): string {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR')
}
