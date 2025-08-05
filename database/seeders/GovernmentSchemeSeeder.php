<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GovernmentScheme;

class GovernmentSchemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Student Scholarship Scheme
        GovernmentScheme::create([
            'scheme_name' => 'National Merit Scholarship for Students',
            'description' => 'Financial assistance for meritorious students from economically weaker sections to pursue higher education. This scheme provides monthly stipend and covers tuition fees for eligible students.',
            'category' => 'education',
            'max_income' => 300000,
            'min_age' => 15,
            'max_age' => 25,
            'required_credentials' => ['education_certificate', 'income_certificate', 'aadhaar_card'],
            'caste_criteria' => ['OBC', 'SC', 'ST', 'EWS'],
            'education_criteria' => ['10th', '12th', 'Graduate'],
            'benefit_amount' => 50000,
            'benefit_type' => 'scholarship',
            'application_deadline' => '2025-12-31',
            'status' => 'active',
            'created_by' => 'Government Official'
        ]);

        // 2. Farmer Loan Scheme
        GovernmentScheme::create([
            'scheme_name' => 'Kisan Credit Card Loan Scheme',
            'description' => 'Low-interest agricultural loans for farmers to purchase seeds, fertilizers, and farming equipment. Special benefits for small and marginal farmers.',
            'category' => 'agriculture',
            'max_income' => 500000,
            'min_age' => 18,
            'max_age' => 65,
            'required_credentials' => ['aadhaar_card', 'pan_card', 'land_document'],
            'caste_criteria' => ['General', 'OBC', 'SC', 'ST'],
            'employment_criteria' => ['Farmer', 'Agricultural Worker'],
            'benefit_amount' => 200000,
            'benefit_type' => 'loan',
            'application_deadline' => '2025-11-30',
            'status' => 'active',
            'created_by' => 'Government Official'
        ]);

        // 3. Unemployment Assistance Scheme
        GovernmentScheme::create([
            'scheme_name' => 'Unemployment Allowance for Youth',
            'description' => 'Monthly financial support for unemployed youth while they search for employment. Includes skill development training and job placement assistance.',
            'category' => 'employment',
            'max_income' => 250000,
            'min_age' => 18,
            'max_age' => 35,
            'required_credentials' => ['aadhaar_card', 'education_certificate', 'unemployment_certificate'],
            'caste_criteria' => ['General', 'OBC', 'SC', 'ST'],
            'employment_criteria' => ['Unemployed'],
            'education_criteria' => ['12th', 'Graduate', 'Post Graduate'],
            'benefit_amount' => 15000,
            'benefit_type' => 'subsidy',
            'application_deadline' => '2025-10-31',
            'status' => 'active',
            'created_by' => 'Government Official'
        ]);

        // 4. Health Insurance Scheme
        GovernmentScheme::create([
            'scheme_name' => 'Ayushman Bharat Health Insurance',
            'description' => 'Comprehensive health insurance coverage for families below poverty line. Covers hospitalization expenses up to 5 lakh rupees per family per year.',
            'category' => 'health',
            'max_income' => 250000,
            'min_age' => 0,
            'max_age' => 100,
            'required_credentials' => ['aadhaar_card', 'income_certificate', 'family_card'],
            'caste_criteria' => ['General', 'OBC', 'SC', 'ST'],
            'benefit_amount' => 500000,
            'benefit_type' => 'insurance',
            'application_deadline' => '2025-09-30',
            'status' => 'active',
            'created_by' => 'Government Official'
        ]);

        // 5. 10th Passout Student Scholarship Scheme
        GovernmentScheme::create([
            'scheme_name' => '10th Passout Student Scholarship Scheme',
            'description' => 'Special scholarship for students who have completed 10th standard with excellent academic performance. Designed to support students from economically weaker sections to continue their education.',
            'category' => 'education',
            'max_income' => 500000,
            'min_age' => 15,
            'max_age' => 20,
            'required_credentials' => ['aadhaar_card', 'marksheet', 'income_certificate'],
            'caste_criteria' => ['General', 'OBC', 'SC', 'ST', 'EWS'],
            'education_criteria' => ['10th'],
            'min_percentage' => 90.0, // 90% minimum requirement
            'benefit_amount' => 75000,
            'benefit_type' => 'scholarship',
            'application_deadline' => '2025-12-31',
            'status' => 'active',
            'created_by' => 'Government Official'
        ]);

        $this->command->info('Government schemes seeded successfully!');
    }
} 