export type Day = 'Lundi' | 'Mardi' | 'Mercredi' | 'Jeudi' | 'Vendredi';

export const DAYS: Day[] = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];

export const TIME_SLOTS = [
    '08:00', '09:00', '10:00', '11:00', '12:00',
    '13:00', '14:00', '15:00', '16:00', '17:00',
];

export interface ScheduleEntry {
    id: string;
    day: Day;
    startTime: string;
    endTime: string;
    subject: string;
    teacher: string;
    room: string;
    // Real data IDs for editing
    academic_personal_id?: number | string;
    course_id?: number | string;
    classroom_id?: number | string;
    school_year_id?: number | string;
    week_number?: number | string;
}
