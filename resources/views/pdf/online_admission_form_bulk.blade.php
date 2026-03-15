<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Online Admission Form Bulk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style type="text/css">
        body {
            font-family: sans-serif;
            position: relative;
            width: 100%;
            height: 100%;
        }

        .table tr td {
            padding: 1px;
            font-size: 14.5px;
            border: 0px solid;
        }

        .main-body {
            page-break-after: always;
        }

        .main-body img {
            height: 100%;
            width: 100%;
        }

        .profile img {
            top: 194px;
            right: 30.5px;
            position: absolute;
            height: 148px;
            width: 147.5px;
        }

        .student_info {
            width: 76%;
            position: absolute;
            top: 228px;
            left: 181px;
        }

        .academic_info {
            width: 76%;
            position: absolute;
            top: 412px;
            left: 181px;
        }

        .contact_info {
            width: 76%;
            position: absolute;
            top: 545px;
            left: 181px;
        }

        .guardian_info {
            width: 76%;
            position: absolute;
            top: 633px;
            left: 181px;
        }

        .others_info {
            width: 76%;
            position: absolute;
            top: 721px;
            left: 181px;
        }

        .subjects_info {
            width: 93%;
            position: absolute;
            top: 857px;
            left: 33px;
        }

        .subject-list {
            width: 33.33%;
            position: relative;
            float: left;
            font-size: 13px;
        }

        .subject-list ul {
            padding: 0px;
            padding-left: 12px;
        }

        .commitments_info {
            width: 73%;
            position: absolute;
            top: 969px;
            left: 181px;
            height: 80px;
            color: #000;
        }

        .commitments_info tr td {
            font-size: 12px;
            line-height: 15px;
        }

        @page {
            margin: 0;
        }
    </style>

</head>

<body>
    @foreach($applications as $data)
    <div class="main-body">
        <img src="{{$config['online_admission_form_image']??''}}" alt="">

        <div class="profile">
            <img src="{{ $data->profile??'' }}">
        </div>

        <table class="table student_info">
            <tbody>
                <tr>
                    <td style="width: 61%;">{{ $data->name }}</td>
                </tr>
                <tr>
                    <td colspan="2">{{ $data->fathers_name }}</td>
                </tr>
                <tr>
                    <td colspan="2">{{ $data->mothers_name }}</td>
                </tr>
                <tr>
                    <td colspan="2">{{ $data->gender }}</td>
                </tr>
                <tr>
                    <td>{{ $data->dob ??'--' }}</td>
                    <td>{{ $data->blood_group ??'--' }}</td>
                </tr>
                <tr>
                    <td>{{ $data->nid ??'--' }}</td>
                    <td>{{ $data->religion ??'--' }}</td>
                </tr>
            </tbody>
        </table>

        <table class="table academic_info">
            <tbody>
                <tr>
                    <td style="width: 61%;">{{ $data->admission_roll ??'--' }}</td>
                    <td>{{ $data->ssc_gpa ??'--' }}</td>
                </tr>
                <tr>
                    <td>{{ $data->registration_no ??'--' }}</td>
                    <td>{{ $data->student->college_roll ??'--' }}</td>
                </tr>
                <tr>
                    <td> {{ $data->academic_session->name ??'--' }}</td>
                    <td> {{ $data->department->name ??'--' }}</td>
                </tr>
                <tr>
                    <td> {{ $data->qualification->name ??'--'}}</td>
                    <td>{{ $data->academic_class->name ??'--'}}</td>
                </tr>
            </tbody>
        </table>

        <table class="table contact_info">
            <tbody>
                <tr>
                    <td style="width: 61%;">{{ $data->mobile }}</td>
                    <td>{{ $data->email ??'--' }}</td>
                </tr>
                <tr>
                    <td colspan="2">{{ $data->address }}</td>
                </tr>
            </tbody>
        </table>

        <table class="table guardian_info">
            <tbody>
                <tr>
                    <td colspan="2">{{ $data->guardian_name ??'--' }}</td>
                </tr>
                <tr>
                    <td style="width: 61%;">{{ $data->guardian_mobile ??'--' }}</td>
                    <td>{{ $data->guardian_relations ??'--' }}</td>
                </tr>
            </tbody>
        </table>

        <table class="table others_info">
            <tbody>
                <tr>
                    <td style="width: 61%;">{{ $data->passing_year ??'--' }}</td>
                    <td>{{ $data->nationality ??'--' }}</td>
                </tr>
                <tr>
                    <td colspan="2">{{ $data->extra_curricular_activity ??'--' }}</td>
                </tr>
                <tr>
                    <td>{{ $data->quota ??'--' }}</td>
                    <td>{{ $data->marital_status ??'--' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="subjects_info">
            <div class="subject-list">
                <ul>
                    @foreach($data['subjectAssign']['details']??[] as $sub)
                    <li>
                        {{ $sub['subject']['name_en'] ?? "" }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @if(is_array($data->subject_choose))
            <div class="subject-list">
                <ul>
                    @foreach($data->subject_choose??[] as $key => $sub)
                    @if($sub['main_subject'] == 1 )
                    <li>
                        {{ $sub['subject']['name_en'] ?? "" }}
                    </li>
                    @endif
                    @endforeach
                </ul>
            </div>
            <div class="subject-list">
                <ul>
                    @foreach($data->subject_choose??[] as $key => $sub)
                    @if($sub['main_subject'] == 0 )
                    <li>
                        {{ $sub['subject']['name_en'] ?? "" }}
                    </li>
                    @endif
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <table class="table commitments_info">
            <tbody>
                <tr>
                    <td style="width: 100%;"> {!! strip_tags($data->qualification->commitment ?? '--') !!}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach
</body>

</html>
