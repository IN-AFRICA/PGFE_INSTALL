<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Database\Seeder;

final class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Charger explicitement les relations nécessaires pour éviter le lazy loading
        $classrooms = Classroom::with('academicLevel.cycle.filiaire')->get();
        $paymentMethodId = \App\Models\PaymentMethod::query()->first()?->id;
        $accountId = \App\Models\Account::query()->first()?->id;
        foreach ($classrooms as $classroom) {
            // Déduire l'école de manière robuste selon les colonnes/relations disponibles
            $schoolId = $classroom->school_id
                ?? $classroom->academicLevel?->cycle?->filiaire?->school_id
                ?? $classroom->academicLevel?->cycle?->school_id;
            $studentIds = \App\Models\Registration::where('classroom_id', $classroom->id)
                ->pluck('student_id')
                ->take(3);
            $students = Student::whereIn('id', $studentIds)->get();
            $fee = Fee::where('school_id', $schoolId)->first();
            foreach ($students as $student) {
                Payment::firstOrCreate([
                    'student_id' => $student->id,
                    'fee_id' => $fee?->id,
                    'amount' => $fee?->amount ?? 100,
                    'currency_id' => $fee?->currency_id,
                    'reference' => 'PAY-'.$classroom->id.'-'.$student->id,
                    'payment_method_id' => $paymentMethodId,
                    'account_id' => $accountId,
                ]);
            }
        }
    }
}
