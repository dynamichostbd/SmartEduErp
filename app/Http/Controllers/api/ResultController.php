<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Backend\Result\Traits\SearchResultTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ResultDetails;
use App\Models\Exam;
use Barryvdh\DomPDF\Facade\Pdf;

class ResultController extends Controller
{
    use SearchResultTrait;

    /**
     * Search Result
     *
     * @return \Illuminate\Http\Response
     */
    public function result(Request $request)
    {
        $data = ['result' => []];
        $cond = [
            'academic_session_id'       => $request->academic_session_id,
            'department_id'             => $request->department_id,
            'academic_qualification_id' => $request->academic_qualification_id,
            'academic_class_id'         => $request->academic_class_id,
            'exam_id'                   => $request->exam_id,
        ];

        $exam = Exam::find($request->exam_id);

        if (empty($exam)) {
            return $this->sendError("Exam not found", 422);
        }

        if ($exam->exam_type == 'ct') {
            $data = $this->classTestResult($cond);
            if (!empty($data['message'])) {
                return $this->sendError($data['message'], 422);
            }
        } else {
            $data = $this->termResult($cond);
            if (!empty($data['message'])) {
                return $this->sendError($data['message'], 422);
            }
        }

        $data['exam_type'] = $exam->exam_type;
        return $this->sendResponse($data);
    }

    /**
     * Marksheet Download
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadMarksheet($id)
    {
        $detail = ResultDetails::with(
            'student',
            'marks.subject',
            'result.academic_class',
            'result.academic_session',
            'result.qualification',
            'result.department',
            'result.exam',
        )->where('student_id', auth()->id())->find($id);

        if (!is_object($detail)) {
            return $this->sendError("Result not found", 422);
        }

        $additional = [];
        if (!empty($detail->marks)) {
            $additional = $detail->marks()->with('subject')->where('additional_subject', 1)->first();
        }

        $config   = app()->make('siteSettingObj');

        $pdf      = Pdf::loadView('pdf.marksheet', compact('detail', 'additional', 'config'))->setPaper('a4', 'portrait');
        $fileName = "marksheet.pdf";
        return $pdf->download($fileName);
    }
}


