// Convert date to MySQL format 'YYYY-MM-DD HH:MM:SS'
export const toSqlDatetime = (d: string | Date): string => {
  // If it's already 'YYYY-MM-DD', just add midnight time
  if (typeof d === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(d)) {
    return `${d} 00:00:00`
  }

  // Otherwise convert to Date and format
  const date = d instanceof Date ? d : new Date(d)
  const pad = (n: number) => String(n).padStart(2, '0')

  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`
}

// Extract filename from Content-Disposition header
export const extractFilenameFromHeaders = (headers: Record<string, string>): string | null => {
  const disposition = headers['content-disposition'] || headers['Content-Disposition']
  if (!disposition) return null

  const match = /filename\*?=([^;]+)/i.exec(disposition)
  if (match && match[1]) {
    return decodeURIComponent(
      match[1]
        .replace(/UTF-8''/, '')
        .trim()
        .replace(/"/g, ''),
    )
  }
  return null
}

// Download a blob as a file
export const downloadBlob = (blob: Blob, filename: string): void => {
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  link.remove()
  window.URL.revokeObjectURL(url)
}
