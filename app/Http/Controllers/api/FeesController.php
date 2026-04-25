<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\FeeSetup;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\ApproveStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $conditions = Student::commonArr();
        $fees       = FeeSetup::with('details')->where($conditions)->first();

        $details = [];
        if (!empty($fees)) {
            $query = $fees->details()
                ->with('account_head');
            $query->whereAny('account_head_id', $request->account_head_id);

            $details = $query->get();
        }
        return $this->sendResponse($details);
    }

    /**
     * Get Dues Account Heads
     *
     * @return \Illuminate\Http\Response
     */
    public function duesPaymentHeads()
    {
        $conditions = Student::commonArr();
        $feeSetup   = FeeSetup::where($conditions)->first();

        if (!empty($feeSetup->details)) {
            $monthlyID = $feeSetup->details->where('payment_duration', 'Monthly')->pluck('account_head_id')->toArray();
            $yearlyID  = $feeSetup->details->where('payment_duration', 'Yearly')->pluck('account_head_id')->toArray();

            $monthlyFees = Invoice::where($conditions)
                ->whereMonth('invoice_date', date('m'))
                ->whereIn('account_head_id', $monthlyID)
                ->where('student_id', auth()->user()->id)
                ->pluck('account_head_id')
                ->toArray();
            $yearlyFees = Invoice::where($conditions)
                // ->whereYear('invoice_date', date('Y'))
                ->whereYear('invoice_date', '<=', date('Y'))
                ->whereIn('account_head_id', $yearlyID)
                ->where('student_id', auth()->user()->id)
                ->pluck('account_head_id')
                ->toArray();

            $headID = array_merge($monthlyFees, $yearlyFees);

            $now = now();
            $fees = $feeSetup->details()->with('account_head')
                ->where([
                    'online_addmission_fees' => 0,
                    'migration_fee'          => 0,
                    'status'                 => 1,
                    'is_approver'            => 1,
                ])
                ->where('start_date', '<=', $now)
                ->where('additional_date', '>=', $now)
                ->whereNotIn('account_head_id', $headID)
                ->get();

            // ---------------------------------------------------------------
            // APPROVED STUDENT FILTER
            // Fee heads with approved = 1 are only shown to students whose
            // result_status = 'PASSED' in the matching ApproveStudent batch.
            // FAILED students will NOT see this fee head in the dropdown.
            // ---------------------------------------------------------------
            $student     = Auth::user();
            $collegeRoll = $student->college_roll;

            $fees = $fees->filter(function ($fee) use ($conditions, $collegeRoll) {

                // approved = 0 or null → always show, no restriction
                if (empty($fee->approved) || $fee->approved != 1) {
                    return true;
                }

                // approved = 1 but no exam_id → can't check, show it
                $examId = $fee->exam_id;
                if (!$examId) {
                    return true;
                }

                // Find an active batch matching exam + student's class/dept/session
                $batch = ApproveStudent::where('exam_id', $examId)
                    ->where('academic_class_id',         $conditions['academic_class_id'])
                    ->where('academic_qualification_id', $conditions['academic_qualification_id'])
                    ->where('department_id',             $conditions['department_id'])
                    ->where('status', 'active')
                    ->first();

                // No batch configured → don't restrict
                if (!$batch) {
                    return true;
                }

                // Find student in the batch's students_data by college_roll
                $studentsData = $batch->students_data ?? [];
                foreach ($studentsData as $std) {
                    if (
                        isset($std['college_roll']) &&
                        (string) $std['college_roll'] === (string) $collegeRoll
                    ) {
                        // Check result_status: only PASSED can see this fee head
                        $status = strtoupper($std['result_status'] ?? 'FAILED');
                        return $status === 'PASSED';
                    }
                }

                // Student not in batch → do not show restricted fee head
                return false;
            });

            // Re-index collection values so JSON is an array, not an object
            $fees = $fees->values();

            // ---------------------------------------------------------------
            // SUBJECT-BASED FEE HEAD FILTER
            //
            // Checks whether the student has subjects 40 and/or 41 assigned,
            // then hides the appropriate fee head:
            //
            //   Has neither 40 nor 41  → hide head_id 75 and 76  (show 74)
            //   Has one of 40 or 41    → hide head_id 76 and 74  (show 75)
            //   Has both 40 and 41     → hide head_id 75 and 74  (show 76)
            //
            // Fee heads with other account_head_id values are unaffected.
            // ---------------------------------------------------------------
            $subjectFeeHeads = [75, 76, 74]; // the three mutually-exclusive fee heads

            // Only run the filter if the fee list contains any of these heads
            $hasSubjectHeads = $fees->whereIn('account_head_id', $subjectFeeHeads)->isNotEmpty();

            if ($hasSubjectHeads) {
                // Fetch the student's assigned subject IDs from student_subject_assigns
                $assignedSubjectIds = $student->subjects()
                    ->pluck('student_subject_assigns.subject_id')
                    ->toArray();

                $has40 = in_array(40, $assignedSubjectIds);
                $has41 = in_array(41, $assignedSubjectIds);

                if (!$has40 && !$has41) {
                    // Neither subject → hide 75 and 76, keep 74
                    $hideHeads = [75, 76];
                } elseif ($has40 && $has41) {
                    // Both subjects → hide 75 and 74, keep 76
                    $hideHeads = [75, 74];
                } else {
                    // Exactly one of 40 or 41 → hide 76 and 74, keep 75
                    $hideHeads = [76, 74];
                }

                $fees = $fees->filter(function ($fee) use ($hideHeads, $subjectFeeHeads) {
                    // Only apply restriction to the three subject-based heads
                    if (!in_array($fee->account_head_id, $subjectFeeHeads)) {
                        return true; // unrelated heads are always shown
                    }
                    return !in_array($fee->account_head_id, $hideHeads);
                })->values();
            }

            // ---------------------------------------------------------------
            // SESSION AND RELIGION FILTER
            // Checks if a fee is restricted to specific sessions or religions
            // ---------------------------------------------------------------
            $fees = $fees->filter(function ($fee) use ($student) {
                // Check Session Condition
                if ($fee->session == 1) {
                    $sessionIds = is_string($fee->session_ids) ? json_decode($fee->session_ids, true) : $fee->session_ids;
                    if (is_array($sessionIds) && count($sessionIds) > 0) {
                        if (!in_array($student->academic_session_id, $sessionIds)) {
                            return false;
                        }
                    }
                }

                // Check Religion Condition
                if ($fee->religion == 1) {
                    $religionIds = is_string($fee->religion_ids) ? json_decode($fee->religion_ids, true) : $fee->religion_ids;
                    if (is_array($religionIds) && count($religionIds) > 0) {
                        if (!in_array($student->religion, $religionIds)) {
                            return false;
                        }
                    }
                }

                // Check Gender Condition
                if ($fee->gender == 1) {
                    $genderIds = is_string($fee->gender_ids) ? json_decode($fee->gender_ids, true) : $fee->gender_ids;
                    if (is_array($genderIds) && count($genderIds) > 0) {
                        if (!in_array($student->gender, $genderIds)) {
                            return false;
                        }
                    }
                }

                // Check Student Type Condition
                if ($fee->student_type == 1) {
                    if ($student->student_type != $fee->student_type_id) {
                        return false;
                    }
                }

                return true;
            })->values();
        }

        return $this->sendResponse($fees ?? []);
    }
}


