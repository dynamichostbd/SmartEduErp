<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\AttendanceDetails;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Search Attendance 
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 30;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        $query = AttendanceDetails::select('attendances.date', 'attendance_details.student_id', 'attendance_details.in_time', 'attendance_details.out_time', 'attendance_details.status')
            ->join('attendances', 'attendances.id', 'attendance_details.attendance_id');

        $query->whereDates('attendances.date', $from_date, $to_date);
        $query->where('attendance_details.student_id', auth()->user()->student_id);
        $query->latest('attendances.date');
        $data = $query->paginate($pagination);
        return new Resource($data);
    }
}


