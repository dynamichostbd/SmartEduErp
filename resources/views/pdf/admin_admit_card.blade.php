<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admit Card</title>
    <style>
        @page {
            size: A4 landscape;
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
            font-size: 12px;
        }

        .page {
            width: 100%;
            height: 209mm;
            page-break-after: always;
            position: relative;
        }

        .student-block {
            position: absolute;
            width: 50%;
            height: 50%;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Fixed Positions for 2x2 Grid */
        .pos-0 {
            top: 0;
            left: 0;
        }

        .pos-1 {
            top: 0;
            left: 50%;
        }

        .pos-2 {
            top: 50%;
            left: 0;
        }

        .pos-3 {
            top: 50%;
            left: 50%;
        }

        .bg-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        /* TEXT POSITIONS (optimized for half-page width) */


        .student-name {
            left: 138px;
            top: 132px;
            position: absolute;
        }

        .fathers-name {
            left: 138px;
            top: 155px;
            position: absolute;
        }

        .mothers-name {
            left: 138px;
            top: 179px;
            position: absolute;
        }

        .academic-class {
            left: 400px;
            top: 225px;
            position: absolute;
        }

        .academic-level {
            left: 138px;
            top: 202px;
            position: absolute;
        }

        .session {
            left: 138px;
            top: 225px;
            position: absolute;
        }

        .department {
            left: 400px;
            top: 202px;
            position: absolute;
        }

        .college-roll {
            left: 400px;
            top: 248px;
            position: absolute;
        }

        .student-id {
            left: 138px;
            top: 271px;
            position: absolute;
        }

        .reg-no {
            left: 138px;
            top: 248px;
            position: absolute;
        }

        .student-type {
            left: 400px;
            top: 271px;
            position: absolute;
        }

        .profile {
            position: absolute;
            right: 27.5px;
            top: 88px;
            width: 82px;
            height: 84px;
        }

        .profile img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 1px solid #ccc;
        }

        .header-section {
            position: absolute;
            top: 25px;
            left: 30px;
            z-index: 1;
        }

        .header-section img {
            width: 40px;
            height: auto;
            object-fit: contain;
            margin-bottom: 5px;
            background-color: #e6e7e8 !important;
        }

        .header-section-p {
            font-size: 18px;
            font-weight: bold;
            position: absolute;
            top: 7px;
            text-align: center;
            width: 100%;
        }

        .exam_name {
            position: absolute;
            top: 50px;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            width: 100%;
            text-transform: capitalize;
        }

        .issue_date {
            position: absolute;
            bottom: 63px;
            left: 110px;
            font-size: 10px;
        }

        .principle_signature {
            position: absolute;
            bottom: 79px;
            right: 47.5px;
        }

        .principle_signature img {
            width: 53px;
            height: auto;
        }
    </style>

</head>

<body>
    @php
        $positions = ['pos-0', 'pos-1', 'pos-2', 'pos-3'];
    @endphp

    @foreach ($students->chunk(4) as $studentChunk)
        <div class="page">
            @foreach ($studentChunk->values() as $index => $student)
                <div class="student-block {{ $positions[$index] }}">

                    @if ($bgImage)
                        <img class="bg-img" src="{{ $bgImage }}" alt="Background">
                    @endif

                    <div class="header-section">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="Logo">
                        @endif
                    </div>
                    <div class="header-section-p">
                        <p>{{ $site->college_name }}</p>
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
    @endforeach
</body>

</html>
