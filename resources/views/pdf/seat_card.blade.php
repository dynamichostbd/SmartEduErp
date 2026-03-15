<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Seat Card</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            font-size: 11px;
        }

        .page {
            width: 100%;
            height: 297mm;
            page-break-after: always;
            position: relative;
        }

        .student-block {
            position: absolute;
            width: 50%;
            height: 25%;
            box-sizing: border-box;
            overflow: hidden;
        }

        .card-wrapper {
            position: relative;
            width: 98%;
            height: 98%;
            margin: 5px;
            overflow: hidden;
        }

        /* Fixed Positions for 2x4 Grid (8 students) */
        .pos-0 {
            top: 0;
            left: 0;
        }

        .pos-1 {
            top: 0;
            left: 50%;
        }

        .pos-2 {
            top: 25%;
            left: 0;
        }

        .pos-3 {
            top: 25%;
            left: 50%;
        }

        .pos-4 {
            top: 50%;
            left: 0;
        }

        .pos-5 {
            top: 50%;
            left: 50%;
        }

        .pos-6 {
            top: 75%;
            left: 0;
        }

        .pos-7 {
            top: 75%;
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


        .header-section {
            position: absolute;
            top: 15px;
            left: 18px;
            z-index: 1;
        }

        .header-section img {
            width: 35px;
            height: auto;
            object-fit: contain;
            margin-bottom: 5px;
        }

        /* TEXT POSITIONS (optimized for seat card layout) */
        .header-section-p {
            font-size: 14px;
            font-weight: bold;
            position: absolute;
            top: 5px;
            text-align: center;
            width: 100%;
        }

        .exam_name {
            position: absolute;
            left: 0;
            width: 100%;
            text-align: center;
            top: 40px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .student-name {
            left: 117px;
            top: 102px;
            position: absolute;
        }

        .fathers-name {
            left: 117px;
            top: 121px;
            position: absolute;
        }

        .mothers-name {
            left: 117px;
            top: 139px;
            position: absolute;
        }

        .academic-class {
            left: 266px;
            top: 159px;
            position: absolute;
        }


        .academic-level {
            left: 117px;
            top: 159px;
            position: absolute;
        }

        .session {
            left: 117px;
            top: 197px;
            position: absolute;
        }

        .department {
            left: 117px;
            top: 178px;
            position: absolute;
        }

        .college-roll {
            left: 266px;
            top: 177.5px;
            position: absolute;
        }

        .reg_no {
            position: absolute;
            left: 266px;
            top: 196.5px;
        }

        /* .student-id {
            left: 120px;
            top: 194px;
            position: absolute;
        } */

        .profile {
            position: absolute;
            right: 16.5px;
            top: 68.2px;
            width: 72.5px;
            height: 74px;
        }

        .profile img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .exam_controller {
            position: absolute;
            bottom: 27px;
            left: 98px;
            font-size: 8px;
            text-align: center;
        }

        .principle_signature {
            position: absolute;
            bottom: 28px;
            right: 105px;
        }

        .principle_signature img {
            width: 55px;
            height: auto;
        }
    </style>

</head>

<body>
    @php
        $positions = ['pos-0', 'pos-1', 'pos-2', 'pos-3', 'pos-4', 'pos-5', 'pos-6', 'pos-7'];
    @endphp

    @foreach ($students->chunk(8) as $studentChunk)
        <div class="page">
            @foreach ($studentChunk->values() as $index => $student)
                <div class="student-block {{ $positions[$index] }}">
                    <div class="card-wrapper">
                        @if ($bgImage)
                            <img class="bg-img" src="{{ $bgImage }}" alt="Background">
                        @endif

                        <div class="header-section">
                            @if ($logo)
                                <img src="{{ $logo }}" alt="Logo">
                            @endif
                        </div>

                        <!-- Added College Name -->
                        <div class="header-section-p">
                            <p>{{ $site->college_name ?? '' }}</p>
                        </div>

                        <div class="exam_name">{{ $searchParams['exam_name'] ?? '' }}</div>

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
                        <div class="reg_no">{{ $student->reg_no }}</div>
                        {{-- <div class="student-id">{{ $student->student_id }}</div> --}}

                        <div class="profile">
                            @if ($student->profile_base64)
                                <img src="{{ $student->profile_base64 }}">
                            @elseif($student->profile)
                                <img src="{{ $student->profile }}">
                            @endif
                        </div>

                        <!-- Added Issue Date -->
                        <div class="exam_controller">{{ $searchParams['exam_controller_name'] ?? '' }}</div>

                        <!-- Added Principal Signature -->
                        <div class="principle_signature">
                            @if (isset($principle_signature) && $principle_signature)
                                <img src="{{ $principle_signature }}" alt="Signature">
                            @endif
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    @endforeach
</body>

</html>
