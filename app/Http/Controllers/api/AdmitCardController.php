<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\AdmitCard;
use App\Models\Student;
use App\Models\SubjectAssign;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdmitCardController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->getAdmitCard();
        $data = $query->get();

        return $this->sendResponse($data);
    }

    /**
     * get admit card 
     */
    private function getAdmitCard()
    {
        $student = auth()->user();

        return DB::table('admit_cards as ac')
            ->leftJoin('exams', 'exams.id', '=', 'ac.exam_id')
            ->leftJoin('attendance_summaries as asu', 'asu.admit_card_id', '=', 'ac.id')
            ->leftJoin('attendance_summary_details as asud', 'asud.attendance_summarie_id', '=', 'asu.id')
            ->select(
                'ac.id',
                'ac.name',
                'ac.expired_date',
                'exams.name as exam_name',
                'asu.present_percent',
                'asud.student_id',
                'asud.present_percentage as student_percent',
                'asud.status'
            )
            ->where('asud.student_id', $student->student_id)
            ->orWhereNull('asud.student_id')
            ->whereNull('ac.deleted_at')
            ->whereDate('ac.issue_date', '<=', date('Y-m-d'))
            ->whereDate('ac.expired_date', '>=', date('Y-m-d'))
            ->where([
                'ac.academic_session_id'       => $student->academic_session_id,
                'ac.academic_qualification_id' => $student->academic_qualification_id,
                'ac.department_id'             => $student->department_id,
                'ac.academic_class_id'         => $student->academic_class_id,
            ]);
    }

    /**
     * Download admit card
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadAdmitCard($id)
    {
        $admit   = AdmitCard::find($id);
        if (empty($admit)) {
            return $this->sendError("Admit card not found", 422);
        }

        $admitCardsQuery = $this->getAdmitCard();
        $admitInfo = $admitCardsQuery->where('ac.id', $id)->first();

        if ($admitInfo->status === "A") {
            return $this->sendError("You are unable to participate in the examination due to insufficient class attendance. Please contact the college authority.", 404);
        } else if ($admitInfo->status === "A" && (int) $admitInfo->student_percent < (int) $admitInfo->present_percent) {
            return $this->sendError("You are unable to participate in the examination due to insufficient class attendance. Please contact the college authority.", 404);
        }

        $student = Student::find(auth()->id());

        $subjectAssign = SubjectAssign::where([
            'academic_qualification_id' => $admit->academic_qualification_id,
            'department_id'             => $admit->department_id,
            'academic_class_id'         => $admit->academic_class_id,
        ])->first();

        $subjects = [];
        if (!empty($subjectAssign) && $subjectAssign->details()->count() > 0) {
            $subjects = $subjectAssign->details()->with('subject')->select('subject_assign_id', 'subject_id')->where(['fourth_subject' => 0, 'main_subject' => 0,])->get();
        }

        $main_subjects = $student->subjects()->where('main_subject', 1)->get();
        $fourth_subjects = $student->subjects()->where('main_subject', 0)->get();

        $config   = app()->make('siteSettingObj');
        $examName = Str::slug($admit->exam->name ?? '');

        $pdf      = Pdf::loadView('pdf.admit_card', compact('admit', 'student', 'subjects', 'main_subjects', 'fourth_subjects', 'config'))->setPaper('a4', 'portrait');
        $fileName = "admit-card({$examName}).pdf";
        return $pdf->download($fileName);
    }
}


