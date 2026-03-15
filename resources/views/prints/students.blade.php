<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Students</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #111827; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .btn { border: 1px solid #cbd5e1; background: #fff; padding: 6px 10px; border-radius: 6px; font-size: 12px; cursor: pointer; }
        .btn-primary { background: #0f172a; border-color: #0f172a; color: #fff; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; vertical-align: top; }
        th { background: #f8fafc; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; }
        .muted { color: #64748b; font-size: 11px; }
        .center { text-align: center; }
        @media print { .toolbar { display: none; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <div>
            <div style="font-weight: 700; font-size: 14px;">Students</div>
            <div class="muted">Total: {{ count($students ?? []) }}</div>
        </div>
        <div style="display:flex; gap:8px;">
            <button class="btn" onclick="window.close()">Close</button>
            <button class="btn btn-primary" onclick="window.print()">Print</button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="center" style="width:40px;">#</th>
                <th style="width:110px;">Software ID</th>
                <th>Student</th>
                <th class="center" style="width:95px;">Admission ID</th>
                <th class="center" style="width:70px;">Roll</th>
                <th class="center" style="width:95px;">Reg No.</th>
                <th style="width:120px;">Session</th>
                <th style="width:140px;">Dept. / Group</th>
                <th style="width:160px;">Academic Level / Class</th>
                <th class="center" style="width:70px;">Subjects</th>
                <th class="center" style="width:70px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $i => $s)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="center">{{ $s->student_id }}</td>
                    <td>
                        <div style="font-weight:700;">{{ $s->name }}</div>
                        <div class="muted">{{ $s->mobile }}</div>
                        <div class="muted">{{ $s->student_type }}</div>
                    </td>
                    <td class="center">{{ $s->admission_id }}</td>
                    <td class="center">{{ $s->college_roll }}</td>
                    <td class="center">{{ $s->reg_no }}</td>
                    <td>{{ $s->academic_session_name }}</td>
                    <td>{{ $s->department_name }}</td>
                    <td>
                        {{ $s->academic_qualification_name }}
                        <div class="muted">({{ $s->academic_class_name }})</div>
                    </td>
                    <td class="center">{{ $s->subjects_count }}</td>
                    <td class="center">{{ $s->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="center">No data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
