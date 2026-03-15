<?php

namespace App\Traits\Lib;

trait CustomDataTrait
{
    public $notice_types = [
        'public' => 'Public Notice',
        'office' => 'Office Notice',
        'student' => 'Student Notice',
    ];

    private $status = [
        'draft' => 'Draft',
        'active' => 'Active',
        'deactive' => 'Deactive',
    ];

    private $gateway = [
        'SSL' => 'SSL',
        'BKASH' => 'BKASH',
        'NAGAD' => 'NAGAD',
    ];

    private $payment_durations = [
        'Yearly' => 'Yearly',
        'Monthly' => 'Monthly',
        'Anytime' => 'Any Time',
    ];

    private $months = [
        ['key' => 1, 'name' => 'January'],
        ['key' => 2, 'name' => 'February'],
        ['key' => 3, 'name' => 'March'],
        ['key' => 4, 'name' => 'April'],
        ['key' => 5, 'name' => 'May'],
        ['key' => 6, 'name' => 'June'],
        ['key' => 7, 'name' => 'July'],
        ['key' => 8, 'name' => 'August'],
        ['key' => 9, 'name' => 'September'],
        ['key' => 10, 'name' => 'October'],
        ['key' => 11, 'name' => 'November'],
        ['key' => 12, 'name' => 'December'],
    ];

    private $files = [
        [
            'checked' => false,
            'optional' => false,
            'key' => 'primary_applicatioin_copy',
            'name_en' => 'Primary Application Copy',
            'name_bn' => 'প্রাথমিক আবেদনপত্রের স্ক্যান কপি',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'nu_application_form',
            'name_en' => 'NU Application Form',
            'name_bn' => 'জাতীয় বিশ্ববিদ্যালয়ের আবেদনপত্রের স্ক্যান কপি',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'hsc_main_marksheet',
            'name_en' => 'HSC Main Marksheet',
            'name_bn' => 'এইচএসসি মূল সনদের স্ক্যান কপি',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'hsc_reg_card',
            'name_en' => 'HSC Reg Card',
            'name_bn' => 'এইচএসসি রেজিস্ট্রেশন কার্ডের স্ক্যান কার্ড',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'degree_main_marksheet',
            'name_en' => 'Degree Main Marksheet',
            'name_bn' => 'ডিগ্রি মূল সনদের স্ক্যান কপি',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'degree_reg_card',
            'name_en' => 'Degree Reg Card',
            'name_bn' => 'ডিগ্রি রেজিস্ট্রেশন কার্ডের স্ক্যান কপি',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'nid_birth_reeg_card',
            'name_en' => 'NID/Birth Reg Card',
            'name_bn' => 'জাতীয় পরিচয়পত্র/জন্মসনদের স্ক্যান কপি',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'education_interruption_certificate',
            'name_en' => 'Education Interruption Certificate',
            'name_bn' => 'শিক্ষাবিরতি সনদের স্ক্যান কপি',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'freedom_fighter_certificate',
            'name_en' => 'Freedom Fighter Certificate',
            'name_bn' => 'মুক্তযোদ্ধা সনদপত্রের স্ক্যান কপি',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'tribal_disability_certificate',
            'name_en' => 'Tribal/Disability Certificate',
            'name_bn' => 'আদিবাসী/উপজাতি/প্রতিবন্ধী সনদপত্রের স্ক্যান কপি',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'masters_admission_application_form',
            'name_en' => 'Masters Admission Application Form',
            'name_bn' => 'মাস্টার্স ভর্তির আবেদনের স্ক্যান কপি',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'graduation_honours_1st_phase_masters_mark_sheet',
            'name_en' => 'Graduation (Honours) /1st Phase Masters Mark Sheet',
            'name_bn' => 'স্নাতক(সম্মান)/১ম পর্ব মাষ্টার্স নম্বরপত্র',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'graduation_pass_preliminary_to_masters_registration_card',
            'name_en' => 'Graduation(Pass)/Preliminary to Masters Registration Card',
            'name_bn' => 'স্নাতক(পাস্)/প্রিলিমিনারী টু মাষ্টার্স রেজিস্ট্রেশন কার্ড',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'commitment_certificate',
            'name_en' => 'Commitment Certificate',
            'name_bn' => 'অঙ্গিকারনামা সনদের স্ক্যান কপি',
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'parents_nid',
            'name_en' => "Parent’s NID Card Scan Copy",
            'name_bn' => "Parent’s NID Card Scan Copy",
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'students_tc_card',
            'name_en' => "Student’s TC Card Scan Copy",
            'name_bn' => "Student’s TC Card Scan Copy",
        ],
        [
            'checked' => false,
            'optional' => false,
            'key' => 'previous_result_card',
            'name_en' => 'Previous Result Card',
            'name_bn' => 'Previous Result Card',
        ],
    ];

    private $sms_types = [
        'OTPInfo' => 'Update Information OTP',
        'OTP' => 'Forget OTP',
        'OTPAdmin' => 'Admin Login OTP',
        'OnlineAdmission' => 'Online Admission',
        'StudentCreate' => 'Student Create',
        'Absent' => 'Absent',
        'Migration' => 'Migration',
        'FailedPayment' => 'Failed Payment',
        'TeacherCreate' => 'Teacher Create',
        'Others' => 'Others',
    ];

    private $sms_keywords = [
        '[_Student_Name_]',
        '[_College_Roll_]',
        '[_Mobile_]',
        '[_Email_]',
        '[_Password_]',
        '[_Date_]',
        '[_Invoice_ID_]',
        '[_OTP_]',
    ];

    private $student_types = [
        'Regular',
        'Irregular',
    ];

    private $religions = [
        'Islam',
        'Hinduism',
        'Chirstian',
        'Buddhist',
        'Others',
    ];

    private $blood_groups = [
        'A-',
        'A+',
        'B-',
        'B+',
        'O-',
        'O+',
        'AB-',
        'AB+',
    ];

    protected function customData()
    {
        return [
            'notice_types' => $this->notice_types,
            'status' => $this->status,
            'gateway' => $this->gateway,
            'payment_durations' => $this->payment_durations,
            'months' => $this->months,
            'files' => $this->files,
            'sms_types' => $this->sms_types,
            'sms_keywords' => $this->sms_keywords,
            'student_types' => $this->student_types,
            'religions' => $this->religions,
            'blood_groups' => $this->blood_groups,
        ];
    }
}
