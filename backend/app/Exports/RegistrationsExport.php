<?php

namespace App\Exports;

use App\Models\Registration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RegistrationsExport implements FromCollection, WithHeadings, WithMapping
{
        protected $collection;

        public function __construct($collection = null)
        {
            $this->collection = $collection;
        }

        public function collection()
        {
            if ($this->collection) {
                return $this->collection;
            }
            return Registration::with([
                'student',
                'academicLevel',
                'classroom',
                'filiaire',
                'cycle',
                'schoolYear',
                'registrationParents.parent1',
                'registrationParents.parent2',
                'registrationParents.parent3',
            ])->get();
        }

    public function map($registration): array
    {
        return [
            // Identifiants bruts d'inscription
            $registration->id,
            $registration->school_id,
            $registration->classroom_id,
            $registration->student_id,
            $registration->school_year_id,
            $registration->academic_personal_id,
            $registration->academic_level_id,
            $registration->type_id,
            $registration->filiaire_id,
            $registration->cycle_id,

            // Infos inscription lisibles
            $registration->registration_date,
            $registration->registration_status ? 'Actif' : 'Inactif',
            optional($registration->schoolYear)->name,
            optional($registration->classroom)->name,
            optional($registration->academicLevel)->name,
            optional($registration->cycle)->name,
            optional($registration->filiaire)->name,
            $registration->note,

            // Infos élève
            optional($registration->student)->matricule,
            optional($registration->student)->lastname,
            optional($registration->student)->firstname,
            optional(optional($registration->student)->gender)->value ?? null,
            optional($registration->student)->birth_date,
            optional($registration->student)->address,
            optional($registration->student)->phone_number,
            optional($registration->student)->email,

            // Infos parents liés à l'inscription
            optional($registration->registrationParents?->parent1)->name,
            optional($registration->registrationParents?->parent1)->phone1,
            optional($registration->registrationParents?->parent1)->email1,
            optional($registration->registrationParents?->parent2)->name,
            optional($registration->registrationParents?->parent2)->phone1,
            optional($registration->registrationParents?->parent2)->email1,
            optional($registration->registrationParents?->parent3)->name,
            optional($registration->registrationParents?->parent3)->phone1,
            optional($registration->registrationParents?->parent3)->email1,
        ];
    }

    public function headings(): array
    {
        return [
            // IDs bruts
            'Registration ID',
            'School ID',
            'Classroom ID',
            'Student ID',
            'School Year ID',
            'Academic Personal ID',
            'Academic Level ID',
            'Type ID',
            'Filiaire ID',
            'Cycle ID',

            // Inscription lisible
            'Registration Date',
            'Registration Status',
            'School Year',
            'Classroom',
            'Academic Level',
            'Cycle',
            'Filiaire',
            'Note',

            // Élève
            'Student Matricule',
            'Student Lastname',
            'Student Firstname',
            'Student Gender',
            'Student Birth Date',
            'Student Address',
            'Student Phone',
            'Student Email',

            // Parents
            'Parent1 Name',
            'Parent1 Phone',
            'Parent1 Email',
            'Parent2 Name',
            'Parent2 Phone',
            'Parent2 Email',
            'Parent3 Name',
            'Parent3 Phone',
            'Parent3 Email',
        ];
    }
}
