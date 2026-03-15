<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Marksheet</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style type="text/css">
        body {
            position: relative;
            width: 100%;
            height: 100%;
            font-size: 18px;
        }

        .marksheet-body img {
            height: 100%;
            width: 100%;
        }

        .marksheet_name {
            position: absolute;
            top: 150px;
            left: 10px;
            font-size: 22px;
            font-weight: bold;
            width: 97.5%;
            text-align: center;
        }

        .marksheet-body .student_info {
            position: absolute;
            left: 193px;
            top: 277px;
            width: 71.4%;
        }

        .marksheet-body .student_info td {
            height: 35.3px;
        }

        .marksheet-body .student_info .right_side {
            width: 33%
        }

        .marksheet-body .marks_info {
            position: absolute;
            left: 98px;
            bottom: 519px;
            width: 83.7%;
        }

        .marksheet-body .marks_info td {
            height: 39px;
            font-size: 14px;
            text-align: center;
            line-height: 17px;
        }

        .marksheet-body .additional_subject_info {
            position: absolute;
            left: 98px;
            bottom: 230.5px;
            width: 68.5%;
        }

        .marksheet-body .additional_subject_info td {
            height: 38.5px;
            font-size: 14px;
            text-align: center;
        }

        .marksheet-body .gpa-above {
            width: 116px;
            position: absolute;
            top: 14px;
            font-size: 15px;
        }

        .marksheet-body .published_date {
            position: absolute;
            left: 132px;
            bottom: 12px;
            font-size: 16px;
        }

        .marksheet-body .merit_position_department {
            position: absolute;
            left: 230px;
            bottom: 132px;
            font-size: 16px;
            font-weight: bold;
        }

        .marksheet-body .merit_position_class {
            position: absolute;
            right: 174px;
            bottom: 132px;
            font-size: 16px;
            font-weight: bold;
        }

        @page {
            margin: 0;
        }
    </style>
</head>

<body>
    @php
        $student = $data['student'] ?? [];
        $result = $data['result'] ?? [];
        $details = $data['result_details'] ?? [];
        $marks = $data['marks'] ?? [];

        $bg = $config['marksheet_image'] ?? '';
        if (!empty($bg) && !preg_match('#^https?://#i', $bg)) {
            $bgStr = ltrim((string) $bg, '/');
            $bgStr = preg_replace('#^(storage/)?upload/#i', '', $bgStr);

            $candidates = [
                public_path((string) $bg),
                public_path($bgStr),
                public_path('storage/upload/' . $bgStr),
            ];

            foreach ($candidates as $p) {
                if (!empty($p) && file_exists($p)) {
                    $bg = $p;
                    break;
                }
            }
        }
        if (!empty($bg) && !preg_match('#^https?://#i', (string) $bg) && file_exists($bg)) {
            $bg = 'file:///' . str_replace(' ', '%20', str_replace('\\', '/', $bg));
        }

        $mainMarks = collect($marks)
            ->filter(function ($m) {
                return (int) ($m['additional_subject'] ?? 0) !== 1 && (int) data_get($m, 'subject.is_child', 0) !== 1;
            })
            ->values();

        $additional = collect($marks)->first(function ($m) {
            return (int) ($m['additional_subject'] ?? 0) === 1;
        });

        $totalExamSubjects = (int) data_get($result, 'total_exam_subjects', 0);
        $showGpa = $totalExamSubjects > 0 ? (count($marks) >= $totalExamSubjects) : true;
        $publishedDate = data_get($result, 'published_date');
        $gpaAbove = 0;
        if (is_array($additional)) {
            $gpaAbove = (float) ($additional['gpa'] ?? 0) - 2;
            if ($gpaAbove < 0) {
                $gpaAbove = 0;
            }
        }
    @endphp

    <div class="marksheet-body">
        <img src="{{ $bg }}" alt="">
        <div class="marksheet_name">
            {{ data_get($result, 'exam.name', '') }}
        </div>

        <table class="student_info">
            <tr>
                <td>{{ $student['name'] ?? '' }}</td>
            </tr>
            <tr>
                <td>{{ $student['fathers_name'] ?? '' }}</td>
            </tr>
            <tr>
                <td>{{ $student['mothers_name'] ?? '' }}</td>
            </tr>
            <tr>
                <td>{{ data_get($result, 'qualification.name', '') }}</td>
            </tr>
            <tr>
                <td>{{ data_get($result, 'department.name', '') }}</td>
                <td class="right_side">{{ $student['reg_no'] ?? '' }}</td>
            </tr>
            <tr>
                <td>{{ data_get($result, 'academic_class.name', '') }}</td>
                <td class="right_side">{{ data_get($result, 'academic_session.name', '') }}</td>
            </tr>
            <tr>
                <td>{{ $student['college_roll'] ?? '' }}</td>
                <td class="right_side">{{ $student['student_type'] ?? '' }}</td>
            </tr>
        </table>

        @if ($mainMarks->count())
        <table class="marks_info">
            <tbody style="text-align:center">
                @foreach ($mainMarks as $key => $mark)
                <tr>
                    <td style="width:193px; text-align:left; padding-left: 10px;">
                        {{ data_get($mark, 'subject.name_en', '') }}
                    </td>
                    <td style="width:64px">{{ !empty($mark['is_absent']) && (int) $mark['is_absent'] === 1 ? 'Absent' : (int) ($mark['total_mark'] ?? 0) }}</td>
                    <td style="width:60px">{{ !empty($mark['is_absent']) && (int) $mark['is_absent'] === 1 ? '--' : ($mark['letter_grade'] ?? '') }}</td>
                    <td style="width:59px">{{ !empty($mark['is_absent']) && (int) $mark['is_absent'] === 1 ? '--' : ($mark['gpa'] ?? '') }}</td>
                    @if ($key == 0)
                    <td rowspan="6" style="width:112px; font-size:20px">
                        <b>{{ $showGpa ? ($details['gpa_without_additional'] ?? '0.00') : '0.00' }}</b>
                    </td>
                    <td rowspan="6" style="width:112px; font-size:20px">
                        <b>{{ $showGpa ? ($details['gpa'] ?? '0.00') : '0.00' }}</b>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if (is_array($additional))
        <table class="additional_subject_info">
            <tbody style="text-align:center;">
                <tr>
                    <td style="width:137px; text-align:left; padding-left: 10px;">
                        {{ data_get($additional, 'subject.name_en', '') }}
                    </td>
                    <td style="width:45px">{{ !empty($additional['is_absent']) && (int) $additional['is_absent'] === 1 ? 'Absent' : (int) ($additional['total_mark'] ?? 0) }}</td>
                    <td style="width:43px">{{ !empty($additional['is_absent']) && (int) $additional['is_absent'] === 1 ? '--' : ($additional['letter_grade'] ?? '') }}</td>
                    <td style="width:43px">{{ !empty($additional['is_absent']) && (int) $additional['is_absent'] === 1 ? '--' : ($additional['gpa'] ?? '') }}</td>
                    <td style="width:80px">
                        <span class="gpa-above">
                            <b>{{ $gpaAbove }}</b>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        @endif

        <div class="merit_position_department">
            <p>{{ $details['merit_position_in_department'] ?? '' }}</p>
        </div>
        <div class="merit_position_class">
            <p>{{ $details['merit_position_in_class'] ?? '' }}</p>
        </div>

        @if (!empty($publishedDate))
        <p class="published_date"> {{ date('d F, Y', strtotime($publishedDate)) }}</p>
        @endif
    </div>
</body>

</html>
