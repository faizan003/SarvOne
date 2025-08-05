<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GovernmentScheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheme_name',
        'description',
        'category',
        'max_income',
        'min_age',
        'max_age',
        'min_percentage',
        'required_credentials',
        'caste_criteria',
        'education_criteria',
        'employment_criteria',
        'benefit_amount',
        'benefit_type',
        'application_deadline',
        'status',
        'created_by',
        'organization_name',
        'organization_did',
        'contact_email',
        'contact_phone',
        'website_url',
        'application_url',
        'documents_required',
        'additional_info',
        'priority_level',
        'target_audience',
        'implementation_phase',
        'submitted_via',
        'api_key_used',
        'last_api_sync'
    ];

    protected $casts = [
        'required_credentials' => 'array',
        'caste_criteria' => 'array',
        'education_criteria' => 'array',
        'employment_criteria' => 'array',
        'documents_required' => 'array',
        'max_income' => 'decimal:2',
        'min_percentage' => 'decimal:2',
        'benefit_amount' => 'decimal:2',
        'application_deadline' => 'date',
        'last_api_sync' => 'datetime',
    ];

    /**
     * Check if a user is eligible for this scheme
     */
    public function checkEligibility($user)
    {
        $eligibilityChecks = [];

        // Check income criteria
        if ($this->max_income) {
            $userIncome = $this->getUserIncome($user);
            if ($userIncome === null) {
                // User has no income data - they are ineligible
                $eligibilityChecks['income'] = false;
            } else {
                $eligibilityChecks['income'] = $userIncome <= $this->max_income;
            }
        }

        // Check age criteria
        if ($this->min_age || $this->max_age) {
            $userAge = $user->getCurrentAge();
            if ($userAge === null) {
                $eligibilityChecks['age'] = false;
            } elseif ($this->min_age && $userAge < $this->min_age) {
                $eligibilityChecks['age'] = false;
            } elseif ($this->max_age && $userAge > $this->max_age) {
                $eligibilityChecks['age'] = false;
            } else {
                $eligibilityChecks['age'] = true;
            }
        }

        // Check caste criteria
        if ($this->caste_criteria && !empty($this->caste_criteria)) {
            $userCaste = $this->getUserCaste($user);
            $eligibilityChecks['caste'] = in_array(strtolower($userCaste), array_map('strtolower', $this->caste_criteria));
        }

        // Check required credentials
        if ($this->required_credentials && !empty($this->required_credentials)) {
            $userCredentials = $user->verifiableCredentials()->pluck('vc_type')->toArray();
            $eligibilityChecks['credentials'] = !empty(array_intersect($this->required_credentials, $userCredentials));
        }

        // Check education criteria
        if ($this->education_criteria && !empty($this->education_criteria)) {
            $userEducation = $user->education_level ?? '';
            $eligibilityChecks['education'] = in_array(strtolower($userEducation), array_map('strtolower', $this->education_criteria));
        }

        // Check percentage criteria
        if ($this->min_percentage) {
            $userPercentage = $this->getUserPercentage($user);
            if ($userPercentage === null) {
                $eligibilityChecks['percentage'] = false;
            } else {
                $eligibilityChecks['percentage'] = $userPercentage >= $this->min_percentage;
            }
        }

        // Check employment criteria
        if ($this->employment_criteria && !empty($this->employment_criteria)) {
            $userEmployment = $user->employment_status ?? '';
            $eligibilityChecks['employment'] = in_array(strtolower($userEmployment), array_map('strtolower', $this->employment_criteria));
        }

        // Check application deadline
        if ($this->application_deadline) {
            $eligibilityChecks['deadline'] = now()->lte($this->application_deadline);
        }

        // All checks must pass for eligibility
        return !in_array(false, $eligibilityChecks, true);
    }

    /**
     * Get eligibility details for a user
     */
    public function getEligibilityDetails($user)
    {
        $details = [
            'eligible' => true,
            'checks' => [],
            'missing_criteria' => []
        ];

        // Check income criteria
        if ($this->max_income) {
            $userIncome = $this->getUserIncome($user);
            
            if ($userIncome === null) {
                // User has no income data - they are ineligible
                $details['checks']['income'] = [
                    'required' => 'Family income ≤ ₹' . number_format($this->max_income),
                    'user_value' => 'Income data not available (Income certificate required)',
                    'eligible' => false
                ];
                $details['eligible'] = false;
                $details['missing_criteria'][] = 'Income data not available - Income certificate required';
            } else {
                $isEligible = $userIncome <= $this->max_income;
                $details['checks']['income'] = [
                    'required' => 'Family income ≤ ₹' . number_format($this->max_income),
                    'user_value' => '₹' . number_format($userIncome),
                    'eligible' => $isEligible
                ];
                if (!$isEligible) {
                    $details['eligible'] = false;
                    $details['missing_criteria'][] = 'Income exceeds limit';
                }
            }
        }

        // Check age criteria
        if ($this->min_age || $this->max_age) {
            $userAge = $user->getCurrentAge();
            $ageRange = '';
            if ($this->min_age && $this->max_age) {
                $ageRange = "Age between {$this->min_age} - {$this->max_age} years";
            } elseif ($this->min_age) {
                $ageRange = "Age ≥ {$this->min_age} years";
            } elseif ($this->max_age) {
                $ageRange = "Age ≤ {$this->max_age} years";
            }
            
            $isEligible = true;
            if ($userAge === null) {
                $isEligible = false;
                $details['checks']['age'] = [
                    'required' => $ageRange,
                    'user_value' => 'Age not available (Aadhaar VC required)',
                    'eligible' => false
                ];
                $details['eligible'] = false;
                $details['missing_criteria'][] = 'Age not available - Aadhaar verification required';
            } else {
                if ($this->min_age && $userAge < $this->min_age) {
                    $isEligible = false;
                } elseif ($this->max_age && $userAge > $this->max_age) {
                    $isEligible = false;
                }
                
                $details['checks']['age'] = [
                    'required' => $ageRange,
                    'user_value' => $userAge . ' years',
                    'eligible' => $isEligible
                ];
                if (!$isEligible) {
                    $details['eligible'] = false;
                    $details['missing_criteria'][] = 'Age criteria not met';
                }
            }
        }

        // Check percentage criteria
        if ($this->min_percentage) {
            $userPercentage = $this->getUserPercentage($user);
            
            if ($userPercentage === null) {
                $details['checks']['percentage'] = [
                    'required' => 'Minimum percentage: ' . $this->min_percentage . '%',
                    'user_value' => 'Percentage data not available (Marksheet VC required)',
                    'eligible' => false
                ];
                $details['eligible'] = false;
                $details['missing_criteria'][] = 'Percentage data not available - Marksheet VC required';
            } else {
                $isEligible = $userPercentage >= $this->min_percentage;
                $details['checks']['percentage'] = [
                    'required' => 'Minimum percentage: ' . $this->min_percentage . '%',
                    'user_value' => $userPercentage . '%',
                    'eligible' => $isEligible
                ];
                if (!$isEligible) {
                    $details['eligible'] = false;
                    $details['missing_criteria'][] = 'Percentage below required minimum';
                }
            }
        }

        // Check caste criteria
        if ($this->caste_criteria && !empty($this->caste_criteria)) {
            $userCaste = $this->getUserCaste($user);
            $isEligible = in_array(strtolower($userCaste), array_map('strtolower', $this->caste_criteria));
            $details['checks']['caste'] = [
                'required' => 'Caste: ' . implode(', ', $this->caste_criteria),
                'user_value' => $userCaste ?: 'Not specified',
                'eligible' => $isEligible
            ];
            if (!$isEligible) {
                $details['eligible'] = false;
                $details['missing_criteria'][] = 'Caste not eligible';
            }
        }

        // Check required credentials
        if ($this->required_credentials && !empty($this->required_credentials)) {
            $userCredentials = $user->verifiableCredentials()->pluck('vc_type')->toArray();
            $isEligible = !empty(array_intersect($this->required_credentials, $userCredentials));
            $details['checks']['credentials'] = [
                'required' => 'Required VCs: ' . implode(', ', $this->required_credentials),
                'user_value' => !empty($userCredentials) ? implode(', ', $userCredentials) : 'No VCs',
                'eligible' => $isEligible
            ];
            if (!$isEligible) {
                $details['eligible'] = false;
                $details['missing_criteria'][] = 'Required credentials missing';
            }
        }

        return $details;
    }

    /**
     * Scope for active schemes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get user income from verifiable credentials or profile
     */
    private function getUserIncome($user)
    {
        // First try to get from user profile
        if ($user->family_income) {
            return $user->family_income;
        }

        // Try to get from income certificate credential
        $incomeVC = $user->verifiableCredentials()
            ->where('vc_type', 'income_certificate')
            ->first();

        if ($incomeVC && $incomeVC->credential_data) {
            $data = is_string($incomeVC->credential_data) 
                ? json_decode($incomeVC->credential_data, true) 
                : $incomeVC->credential_data;

            // Check for annual_income in the credential data
            if (isset($data['income_certificate']['annual_income'])) {
                return $data['income_certificate']['annual_income'];
            }
            
            // Check for income in the credential data
            if (isset($data['income_certificate']['income'])) {
                return $data['income_certificate']['income'];
            }
        }

        // Return null if no income data found - this will make user ineligible
        return null;
    }

    /**
     * Scope for schemes by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get user percentage from marksheet verifiable credential
     */
    public function getUserPercentage($user)
    {
        // Try to get from marksheet credential
        $marksheetVC = $user->verifiableCredentials()
            ->where('vc_type', 'marksheet')
            ->first();

        if ($marksheetVC && $marksheetVC->credential_data) {
            $data = is_string($marksheetVC->credential_data) 
                ? json_decode($marksheetVC->credential_data, true) 
                : $marksheetVC->credential_data;

            // Check for percentage in the credential data
            if (isset($data['percentage'])) {
                return (float) $data['percentage'];
            }
            
            // Check for percentage in nested structure
            if (isset($data['marksheet']['percentage'])) {
                return (float) $data['marksheet']['percentage'];
            }
        }

        return null;
    }

    /**
     * Get user caste from verifiable credentials
     */
    public function getUserCaste($user)
    {
        // Try to get from caste certificate credential
        $casteVC = $user->verifiableCredentials()
            ->where('vc_type', 'caste_certificate')
            ->first();

        if ($casteVC && $casteVC->credential_data) {
            $data = is_string($casteVC->credential_data) 
                ? json_decode($casteVC->credential_data, true) 
                : $casteVC->credential_data;

            // Check for caste in the credential data
            if (isset($data['caste_certificate']['caste'])) {
                return $data['caste_certificate']['caste'];
            }
            
            // Check for caste_category in the credential data
            if (isset($data['caste_certificate']['caste_category'])) {
                return $data['caste_certificate']['caste_category'];
            }
            
            // Check for category in the credential data
            if (isset($data['caste_certificate']['category'])) {
                return $data['caste_certificate']['category'];
            }
        }

        return '';
    }
} 