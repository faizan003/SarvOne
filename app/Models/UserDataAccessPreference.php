<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDataAccessPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_type',
        'allowed_data_types',
        'mandatory_data_types',
        'is_active'
    ];

    protected $casts = [
        'allowed_data_types' => 'array',
        'mandatory_data_types' => 'array',
        'is_active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Get available data types for each organization type
    public static function getAvailableDataTypes()
    {
        return [
            'aadhaar_card' => 'Aadhaar Card',
            'pan_card' => 'PAN Card',
            'income_certificate' => 'Income Certificate',
            'caste_certificate' => 'Caste Certificate',
            'student_status' => 'Student Status',
            'marksheet' => 'Marksheet',
            'study_certificate' => 'Study Certificate',
            'degree_certificate' => 'Degree Certificate',
            'transfer_certificate' => 'Transfer Certificate',
            'admission_certificate' => 'Admission Certificate',
            'attendance_certificate' => 'Attendance Certificate',
            'character_certificate' => 'Character Certificate',
            'migration_certificate' => 'Migration Certificate',
            'bonafide_certificate' => 'Bonafide Certificate',
            'scholarship_certificate' => 'Scholarship Certificate',
            'sports_certificate' => 'Sports Certificate',
            'extracurricular_certificate' => 'Extracurricular Certificate',
            'alumni_status' => 'Alumni Status',
            'course_completion' => 'Course Completion',
            'land_document' => 'Land Document',
            'property_tax' => 'Property Tax',
            'bank_account' => 'Bank Account',
            'loan_history' => 'Loan History',
            'credit_score' => 'Credit Score'
        ];
    }

    // Get organization types with their default mandatory data types and verifiable credentials
    public static function getOrganizationTypes()
    {
        return [
            'uidai' => [
                'name' => 'UIDAI (Aadhaar)',
                'description' => 'Unique Identification Authority of India',
                'icon' => 'fas fa-id-card',
                'color' => 'blue',
                'mandatory' => ['aadhaar_card'],
                'optional' => [],
                'verifiable_credentials' => ['aadhaar_card']
            ],
            'government' => [
                'name' => 'Government Agencies',
                'description' => 'Central and State Government Departments',
                'icon' => 'fas fa-landmark',
                'color' => 'yellow',
                'mandatory' => ['aadhaar_card', 'pan_card'],
                'optional' => ['income_certificate', 'caste_certificate'],
                'verifiable_credentials' => ['aadhaar_card', 'pan_card', 'income_certificate', 'caste_certificate']
            ],
            'land_property' => [
                'name' => 'Land & Property',
                'description' => 'Property registration and land management',
                'icon' => 'fas fa-home',
                'color' => 'green',
                'mandatory' => ['aadhaar_card', 'land_document'],
                'optional' => ['property_tax'],
                'verifiable_credentials' => ['aadhaar_card', 'land_document', 'property_tax']
            ],
            'bank' => [
                'name' => 'Banks & Financial',
                'description' => 'Banks, NBFCs, and financial institutions',
                'icon' => 'fas fa-university',
                'color' => 'purple',
                'mandatory' => ['aadhaar_card', 'pan_card'],
                'optional' => ['income_certificate', 'bank_account', 'loan_history', 'credit_score'],
                'verifiable_credentials' => ['aadhaar_card', 'pan_card', 'income_certificate', 'bank_account', 'loan_history', 'credit_score']
            ],
            'school_university' => [
                'name' => 'Schools & Universities',
                'description' => 'Educational institutions and universities',
                'icon' => 'fas fa-graduation-cap',
                'color' => 'indigo',
                'mandatory' => ['aadhaar_card'],
                'optional' => ['student_status', 'marksheet', 'study_certificate', 'degree_certificate', 'transfer_certificate', 'admission_certificate', 'attendance_certificate', 'character_certificate', 'migration_certificate', 'bonafide_certificate', 'scholarship_certificate', 'sports_certificate', 'extracurricular_certificate', 'alumni_status', 'course_completion'],
                'verifiable_credentials' => ['aadhaar_card', 'student_status', 'marksheet', 'study_certificate', 'degree_certificate', 'transfer_certificate', 'admission_certificate', 'attendance_certificate', 'character_certificate', 'migration_certificate', 'bonafide_certificate', 'scholarship_certificate', 'sports_certificate', 'extracurricular_certificate', 'alumni_status', 'course_completion']
            ]
        ];
    }
}
