<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SarvOne Credential Scopes Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines what credentials each organization type can
    | ISSUE (write) and VERIFY (read) based on their business model and 
    | regulatory permissions.
    |
    */

    'uidai' => [
        'write' => [
            'aadhaar_card' => 'Aadhaar Card'
        ],
        'read' => [
            'aadhaar_card' => 'Aadhaar Card'
        ]
    ],

    'government' => [
        'write' => [
            'aadhaar_card' => 'Aadhaar Card',
            'pan_card' => 'PAN Card',
            'voter_id' => 'Voter ID',
            'caste_certificate' => 'Caste Certificate',
            'ration_card' => 'Ration Card',
            'income_certificate' => 'Income Certificate',
            'domicile_certificate' => 'Domicile Certificate',
            'birth_certificate' => 'Birth Certificate',
            'death_certificate' => 'Death Certificate',
            'marriage_certificate' => 'Marriage Certificate'
        ],
        'read' => [
            'aadhaar_card' => 'Aadhaar Card',
            'pan_card' => 'PAN Card',
            'voter_id' => 'Voter ID',
            'caste_certificate' => 'Caste Certificate',
            'ration_card' => 'Ration Card',
            'income_certificate' => 'Income Certificate',
            'domicile_certificate' => 'Domicile Certificate',
            'birth_certificate' => 'Birth Certificate',
            'death_certificate' => 'Death Certificate',
            'marriage_certificate' => 'Marriage Certificate'
        ]
    ],

    'land_property' => [
        'write' => [
            'land_property' => 'Land Property',
            'property_tax_receipt' => 'Property Tax Receipt',
            'encumbrance_certificate' => 'Encumbrance Certificate'
        ],
        'read' => [
            'aadhaar_card' => 'Aadhaar Card',
            'pan_card' => 'PAN Card',
            'land_property' => 'Land Property',
            'income_certificate' => 'Income Certificate'
        ]
    ],

    'bank' => [
        'write' => [
            'bank_account' => 'Bank Account',
            'loan' => 'Loan',
            'land_loan' => 'Land Loan',
            'credit_score' => 'Credit Score',
            'income_certificate' => 'Income Certificate',
            'employment_certificate' => 'Employment Certificate'
        ],
        'read' => [
            'aadhaar_card' => 'Aadhaar Card',
            'pan_card' => 'PAN Card',
            'income_certificate' => 'Income Certificate',
            'land_property' => 'Land Property',
            'employment_certificate' => 'Employment Certificate'
        ]
    ],

    'school_university' => [
        'write' => [
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
            'academic_transcript' => 'Academic Transcript',
            'diploma_certificate' => 'Diploma Certificate',
            'post_graduation_certificate' => 'Post Graduation Certificate'
        ],
        'read' => [
            'aadhaar_card' => 'Aadhaar Card',
            'pan_card' => 'PAN Card',
            'income_certificate' => 'Income Certificate',
            'caste_certificate' => 'Caste Certificate',
            'domicile_certificate' => 'Domicile Certificate',
            'birth_certificate' => 'Birth Certificate',
            'marksheet' => 'Marksheet',
            'study_certificate' => 'Study Certificate',
            'degree_certificate' => 'Degree Certificate',
            'transfer_certificate' => 'Transfer Certificate'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Contract Mapping Configuration
    |--------------------------------------------------------------------------
    | 
    | This section maps frontend scope names to smart contract scope names.
    | The smart contract uses these exact scope names for validation.
    |
    */

    'contract_mapping' => [
        // Identity Documents
        'aadhaar_card' => 'aadhaar_card',
        'pan_card' => 'pan_card',
        'voter_id' => 'voter_id',
        'passport' => 'passport',
        'driving_license' => 'driving_license',
        
        // Government Certificates
        'caste_certificate' => 'caste_certificate',
        'ration_card' => 'ration_card',
        'income_certificate' => 'income_certificate',
        'domicile_certificate' => 'domicile_certificate',
        'birth_certificate' => 'birth_certificate',
        'death_certificate' => 'death_certificate',
        'marriage_certificate' => 'marriage_certificate',
        
        // Land & Property
        'land_property' => 'land_property',
        'property_tax_receipt' => 'property_tax_receipt',
        'encumbrance_certificate' => 'encumbrance_certificate',
        
        // Banking & Financial
        'bank_account' => 'bank_account',
        'loan' => 'loan',
        'land_loan' => 'land_loan',
        'credit_score' => 'credit_score',
        'employment_certificate' => 'employment_certificate',
        
        // Education
        'student_status' => 'student_status',
        'marksheet' => 'marksheet',
        'study_certificate' => 'study_certificate',
        'degree_certificate' => 'degree_certificate',
        'transfer_certificate' => 'transfer_certificate',
        'admission_certificate' => 'admission_certificate',
        'attendance_certificate' => 'attendance_certificate',
        'character_certificate' => 'character_certificate',
        'migration_certificate' => 'migration_certificate',
        'bonafide_certificate' => 'bonafide_certificate',
        'scholarship_certificate' => 'scholarship_certificate',
        'sports_certificate' => 'sports_certificate',
        'academic_transcript' => 'academic_transcript',
        'diploma_certificate' => 'diploma_certificate',
        'post_graduation_certificate' => 'post_graduation_certificate'
    ],

    /*
    |--------------------------------------------------------------------------
    | Scope Limits Configuration
    |--------------------------------------------------------------------------
    |
    | Maximum number of scopes each organization type can have.
    |
    */

    'max_write_scopes' => 20,
    'max_read_scopes' => 25,
]; 