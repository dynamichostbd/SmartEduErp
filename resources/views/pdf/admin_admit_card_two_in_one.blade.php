<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admit Card</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        @php
            $myriadRegular = storage_path('fonts/MyriadPro-Regular.ttf');
            $myriadBold = storage_path('fonts/MyriadPro-Bold.ttf');
        @endphp

        @if (file_exists($myriadRegular))
        @font-face {
            font-family: 'Myriad Pro';
            src: url("{{ $myriadRegular }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        @endif

        @if (file_exists($myriadBold))
        @font-face {
            font-family: 'Myriad Pro';
            src: url("{{ $myriadBold }}") format("truetype");
            font-weight: bold;
            font-style: normal;
        }
        @endif

        body {
            margin: 0;
            padding: 0;
            font-family: 'Myriad Pro', sans-serif;
            font-size: 15px;
        }

        .page {
            width: 100%;
            height: 297mm;
            page-break-after: always;
            position: relative;
        }

        .student-block {
            position: absolute;
            width: 100%;
            height: 147mm;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Fixed Positions for 2x2 Grid */
        .pos-0 {
            top: 0;
            left: 0;
        }

        .pos-1 {
            top: 150mm;
            left: 0;
        }
        .pos-2 {
            top: 0;
            left: 0;
        }

        .pos-3 {
            top: 50%;
            left: 0%;
        }

        .bg-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        @if (!empty($bgFront))
        .front-bg {
            background-image: url("{{ $bgFront }}");
            background-repeat: no-repeat;
            background-position: top left;
            background-size: 100% 100%;
        }
        @endif

        @if (!empty($bgBack))
        .back-bg {
            background-image: url("{{ $bgBack }}");
            background-repeat: no-repeat;
            background-position: top left;
            background-size: 100% 100%;
        }
        @endif

        /* TEXT POSITIONS (optimized for half-page width) */


        .student-name {
            left: 160px;
            top: 274px;
            position: absolute;
        }

        .fathers-name {
            left: 160px;
            top: 302px;
            position: absolute;
        }

        .mothers-name {
            left: 160px;
            top: 330px;
            position: absolute;
        }

        .academic-class {
            left: 535px;
            top: 386px;
            position: absolute;
        }

        .academic-level {
            left: 160px;
            top: 358px;
            position: absolute;
        }

        .session {
            left: 160px;
            top: 386px;
            position: absolute;
        }

        .department {
            left: 535px;
            top: 358px;
            position: absolute;
        }

        .college-roll {
            left: 535px;
            top: 414px;
            position: absolute;
        }

        .student-id {
            left: 160px;
            top: 442px;
            position: absolute;
        }

        .reg-no {
            left: 160px;
            top: 414px;
            position: absolute;
        }

        .student-type {
            left: 535px;
            top: 442px;
            position: absolute;
        }

        .profile {
            position: absolute;
            right: 30.5px;
            top: 196px;
            width: 140.4px;
            height: 140.5px;
        }

        .profile img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 1px solid #ccc;
        }

        .header-section {
            position: absolute;
            top: 30px;
            left: 30px;
            z-index: 1;
        }

        .header-section img {
            width: 92px;
            height: auto;
            object-fit: contain;
            margin-bottom: 5px;
            background-color: #e6e7e8 !important;
        }

        .header-section-p {
            font-size: 27px;
            top: -8px;
            font-weight: bold;
            position: absolute;
            text-align: center;
            width: 100%;
        }
        .sub-header-section-p {
            font-size: 17.5px;
            position: absolute;
            top: 50px;
            text-align: center;
            width: 100%;
            line-height: 4px;
            text-align: center;
        }

        .exam_name {
            position: absolute;
            top: 158px;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            width: 100%;
            text-transform: capitalize;
        }

        .issue_date {
            position: absolute;
            bottom: 27.5px;
            left: 133px;
            font-size: 14px;
        }

        .principle_signature {
            position: absolute;
            bottom: 50px;
            right: 33px;
        }

        .principle_signature img {
            width: auto;
            height: 40px;
        }

        .subject-list {
            position: absolute;
            top: 95px;
            left: 10%;
            width: 80%;
            font-size: 16px;
            line-height: 1.3;
            border: 1px solid black;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .subjects-table {
            position: absolute;
            top: 90px;
            left: 8%;
            width: 84%;
            border-collapse: collapse;
        }

        .subjects-table td {
            vertical-align: top;
        }

        .subjects-table h6 {
            margin: 0 0 4px 0;
            font-size: 14px;
        }

        .subjects-table ul {
            margin: 0;
            padding-left: 18px;
        }

        .subjects-table li {
            margin: 0;
            padding: 0;
        }
        .top_info{
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 8px;
            font-family: 'Myriad Pro', sans-serif;
            font-style: italic;
            line-height: 3px;
        }
    </style>

</head>

<body>
    @php
        $positions = ['pos-0', 'pos-1'];
        $includeBack = $includeBack ?? true;
    @endphp

    @foreach ($students->chunk(2) as $studentChunk)
        <div class="page">
            @foreach ($studentChunk->values() as $index => $student)
                <div class="student-block {{ $positions[$index] }}">

                    @if (!empty($bgFront))
                        <img class="bg-img" src="{{ $bgFront }}" alt="Background">
                    @endif

                    <div class="header-section">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="Logo">
                        @endif
                    </div>
                    <div class="header-section-p">
                        <p>{{ $site->college_name }}</p>
                    </div>
                    <div class="sub-header-section-p">
                        <p>Phone: {{ $site->college_phone }}</p>
                        <p>Email: {{ $site->college_email }}</p>
                        <p>Web: {{ $site->college_web }}</p>
                        <p>Epay: {{ $site->web }}</p>
                    </div>

                    <div class="exam_name">{{ $searchParams['exam_name'] ?? '' }}</div>
                    <div class="issue_date">{{ $searchParams['issue_date'] ?? '' }}</div>
                    <div class="student-name">{{ $student->name }}</div>
                    <div class="fathers-name">{{ $student->fathers_name }}</div>
                    <div class="mothers-name">{{ $student->mothers_name }}</div>
                    <div class="academic-class">{{ $student->academic_class->name ?? '' }}</div>
                    <div class="academic-level">{{ $student->qualification->name ?? '' }}</div>
                    <div class="session">{{ $student->academic_session->name ?? '' }}</div>
                    <div class="department">
                        {{ $student->department->name ?? ($student->qualification->name ?? '') }}
                    </div>
                    <div class="college-roll">{{ $student->college_roll }}</div>
                    <div class="student-id">{{ $student->student_id }}</div>
                    <div class="reg-no">{{ $student->reg_no }}</div>
                    <div class="student-type">{{ $student->student_type ?? 'Regular' }}</div>

                    <div class="profile">
                        @if ($student->profile_base64)
                            <img src="{{ $student->profile_base64 }}">
                        @elseif($student->profile)
                            <img src="{{ $student->profile }}">
                        @endif
                    </div>

                    <div class="principle_signature">
                        <img src="{{ $principle_signature }}" alt="Signature">
                    </div>

                </div>
            @endforeach
        </div>

        @if ($includeBack)
            <div class="page">
                @foreach ($studentChunk->values() as $index => $student)
                    <div class="student-block {{ $positions[$index] }}">

                        @if (!empty($bgBack))
                            <img class="bg-img" src="{{ $bgBack }}" alt="Background">
                        @endif

                        <div class="top_info">
                            <p>{{ $student->name }}</p>
                            <p>{{ $student->college_roll }}</p>
                        </div>

                        <table class="subjects-table">
                            <tr>
                                <td class="common-subjects" style="width: 50%;">
                                    <h6>Compulsory Subject:</h6>
                                    <ul>
                                        @foreach (($student->compulsory_subjects ?? []) as $sub)
                                            <li>
                                                {{ $sub->subject->name_en ?? '' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="main-subjects" style="width: 50%;">
                                    <ul>
                                        <h6>4th Subject:</h6>
                                        @foreach (($student->fourth_subjects ?? []) as $key => $sub)
                                            <li>
                                                {{ $sub->subject->name_en ?? '' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                    <ul>
                                        <h6 style="margin-top: 10px;">Main Subject:</h6>
                                        @foreach (($student->main_subjects ?? []) as $key => $sub)
                                            <li>
                                                {{ $sub->subject->name_en ?? '' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        </table>

                    </div>
                @endforeach
            </div>
        @endif
    @endforeach
</body>

</html>
