<?php

namespace App\Jobs;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProcessStudentZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $requestData;
    public string $zipName;

    public function __construct(array $requestData, string $zipName)
    {
        $this->requestData = $requestData;
        $this->zipName = $zipName;
    }

    protected function baseFilteredQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $departmentId = $this->requestData['department_id'] ?? null;
        $hostelId = $this->requestData['hostel_id'] ?? null;

        $subjectIds = $this->requestData['subject_ids'] ?? [];
        $fourthSubjectId = $this->requestData['fourth_subject_id'] ?? null;
        $classId = $this->requestData['academic_class_id'] ?? null;

        $query = Student::query();

        $ids = $this->requestData['ids'] ?? null;
        if (is_array($ids) && count($ids) > 0) {
            $query->whereIn('students.id', $ids);
        }

        $field = $this->requestData['field_name'] ?? null;
        $value = $this->requestData['value'] ?? null;

        if (!empty($field) && $value !== null && $value !== '') {
            if ($field === 'admission_id') {
                $query->where('students.' . $field, $value);
            } else {
                $query->where('students.' . $field, 'like', '%' . $value . '%');
            }
        }

        if (!empty($this->requestData['academic_qualification_id'])) {
            $query->where('students.academic_qualification_id', $this->requestData['academic_qualification_id']);
        }

        if (!empty($this->requestData['student_type'])) {
            $query->where('students.student_type', $this->requestData['student_type']);
        }

        if (!empty($this->requestData['status'])) {
            $query->where('students.status', $this->requestData['status']);
        }

        if (!empty($this->requestData['gender'])) {
            $query->where('students.gender', $this->requestData['gender']);
        }

        if (!empty($classId)) {
            $query->where('students.academic_class_id', $classId);
        }

        if (!empty($this->requestData['academic_session_id'])) {
            $query->where('students.academic_session_id', $this->requestData['academic_session_id']);
        }

        if ($departmentId !== null && $departmentId !== '') {
            $query->where('students.department_id', $departmentId);
        }

        if ($hostelId !== null && $hostelId !== '') {
            $query->where('students.hostel_id', $hostelId);
        }

        if (!empty($subjectIds) && is_array($subjectIds)) {
            $subjectIds = array_values(array_filter($subjectIds, fn($v) => $v !== null && $v !== ''));
            if (count($subjectIds) > 0) {
                $query->whereIn('students.id', function ($q) use ($subjectIds, $departmentId, $classId) {
                    $q->from('student_subject_assigns')
                        ->select('student_id')
                        ->when($classId, fn($sq) => $sq->where('academic_class_id', $classId))
                        ->when($departmentId, fn($sq) => $sq->where('department_id', $departmentId))
                        ->where('main_subject', 1)
                        ->whereIn('subject_id', $subjectIds)
                        ->groupBy('student_id')
                        ->havingRaw('COUNT(DISTINCT subject_id) = ?', [count($subjectIds)]);
                });
            }
        }

        if (!empty($fourthSubjectId)) {
            $query->whereExists(function ($q) use ($fourthSubjectId, $departmentId, $classId) {
                $q->from('student_subject_assigns')
                    ->whereColumn('student_subject_assigns.student_id', 'students.id')
                    ->when($classId, fn($sq) => $sq->where('academic_class_id', $classId))
                    ->when($departmentId, fn($sq) => $sq->where('department_id', $departmentId))
                    ->where('main_subject', 0)
                    ->where('subject_id', $fourthSubjectId);
            });
        }

        return $query;
    }

    public function handle(): void
    {
        $limit = (int) ($this->requestData['pagination'] ?? $this->requestData['limit'] ?? 2000);
        $limit = $limit > 0 ? min($limit, 10000) : 2000;

        $students = $this->baseFilteredQuery()
            ->select('students.id', 'students.student_id', 'students.profile')
            ->orderBy('students.college_roll', 'asc')
            ->limit($limit)
            ->get();

        Storage::disk('public')->makeDirectory('student-zips');
        $zipFullPath = storage_path('app/public/student-zips/' . $this->zipName);

        $zip = new \ZipArchive();
        $opened = $zip->open($zipFullPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($opened !== true) {
            return;
        }

        foreach ($students as $s) {
            $profile = $s->profile;
            if (empty($profile)) {
                continue;
            }

            $contents = null;
            $ext = 'jpg';

            if (preg_match('/^https?:\/\//i', $profile)) {
                $extFromUrl = pathinfo(parse_url($profile, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
                if (!empty($extFromUrl)) {
                    $ext = $extFromUrl;
                }
                $resp = Http::timeout(25)->get($profile);
                if ($resp->ok()) {
                    $contents = $resp->body();
                }
            } else {
                $extFromPath = pathinfo($profile, PATHINFO_EXTENSION);
                if (!empty($extFromPath)) {
                    $ext = $extFromPath;
                }
                $full1 = public_path($profile);
                $full2 = public_path('storage/upload/' . $profile);
                if (file_exists($full1)) {
                    $contents = file_get_contents($full1);
                } elseif (file_exists($full2)) {
                    $contents = file_get_contents($full2);
                }
            }

            if ($contents === null) {
                continue;
            }

            $file = ($s->student_id ?: $s->id) . '.' . $ext;
            $zip->addFromString($file, $contents);
        }

        $zip->close();
    }
}
