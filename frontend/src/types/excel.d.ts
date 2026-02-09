// Types pour les modules Excel
declare module 'xlsx' {
  export interface WorkBook {
    SheetNames: string[]
    Sheets: { [key: string]: WorkSheet }
  }

  export interface WorkSheet {
    [key: string]: any
  }

  export const utils: {
    book_new(): WorkBook
    json_to_sheet(data: any[]): WorkSheet
    book_append_sheet(workbook: WorkBook, worksheet: WorkSheet, name: string): void
    sheet_to_json(worksheet: WorkSheet): any[]
  }

  export function read(data: any, options?: any): WorkBook
  export function write(workbook: WorkBook, options?: any): any
}

declare module 'file-saver' {
  export function saveAs(blob: Blob, filename: string): void
}
