<?php

declare(strict_types=1);

namespace App\Services\Imports;

use App\Models\AcademicPersonal;
use App\Models\Commune;
use App\Models\Country;
use App\Models\Province;
use App\Models\School;
use App\Models\Territory;
use App\Models\Type;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

final class AcademicPersonalImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $collection): void
    {
        $filteredCollection = $collection->filter(
            fn ($row): bool => ! empty($row['name']) && ! empty($row['firstname']) &&
                ! empty($row['phone_number']) && ! empty($row['email']) &&
                ! empty($row['matricule']) && ! empty($row['identity_card'])
        );

        $filteredCollection->each(function ($row): void {
            $country = Country::find($row['country_id'] ?? null);
            $province = Province::find($row['province_id'] ?? null);
            $territory = Territory::find($row['territory_id'] ?? null);
            $commune = Commune::find($row['commune_id'] ?? null);
            $school = School::find($row['school_id'] ?? null);
            $type = Type::find($row['type_id'] ?? null);

            $existingRecord = AcademicPersonal::where('matricule', $row['matricule'])
                ->orWhere('email', $row['email'])
                ->orWhere('phone_number', $row['phone_number'])
                ->orWhere('identity_card', $row['identity_card'])
                ->first();

            if ($existingRecord) {
                return;
            }

            AcademicPersonal::create([
                'country_id' => $country?->id,
                'province_id' => $province?->id,
                'territory_id' => $territory?->id,
                'commune_id' => $commune?->id,
                'school_id' => $school?->id,
                'type_id' => $type?->id,
                'father_id' => $row['father_id'] ?? null,
                'mother_id' => $row['mother_id'] ?? null,
                'academic_level_id' => $row['academic_level_id'] ?? null,
                'fonction_id' => $row['fonction_id'] ?? null,
                'matricule' => $row['matricule'],
                'name' => $row['name'],
                'firstname' => $row['firstname'],
                'username' => $row['username'] ?? null,
                'phone_number' => $row['phone_number'],
                'email' => $row['email'],
                'identity_card' => $row['identity_card'],
                'gender' => $row['gender'],
                'civil_status' => $row['civil_status'],
                'birth_date' => $row['birth_date'],
                'birth_place' => $row['birth_place'] ?? null,
                'address' => $row['address'] ?? null,
            ]);
        });
    }
}
