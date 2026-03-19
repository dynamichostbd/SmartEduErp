<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Payment Invoice</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
    <style type="text/css">
        .box {
            display: table;
            width: 100%;
        }

        .col-3 {
            display: table-cell;
            width: 25%;
        }

        .col-5 {
            display: table-cell;
            width: 41.6666666667%;
        }

        .col-7 {
            display: table-cell;
            width: 58.3333333333%;
        }

        .col-9 {
            display: table-cell;
            width: 75%;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right !important;
        }

        .table tr th,
        .table tr td {
            padding: 5px;
        }

        /* INVOICE */
        .invoice {
            background: #fff;
            padding: 30px 35px;
            border-radius: 5px;
            width: 100%;
            height: 500px;
        }

        .invoice:first-child {
            border-bottom: 2px dashed red;
        }

        .logo {
            margin-top: 5px;
        }

        .invoice-text {
            border: 2px solid;
            padding: 8px 20px;
            border-radius: 4px;
            width: 200px;
            margin: 0 auto;
            font-size: 16px;
        }

        .site-name {
            text-align: right;
            color: #444;
            font-size: 27px;
            margin: 0px;
            padding: 0px;
        }

        .table {
            width: 100%;
            font-size: 14px;
        }

        .student-info {
            font-size: 14px;
        }

        .inword {
            width: 100%;
            text-transform: uppercase;
            font-size: 13px;
            margin-top: -10px;
        }

        .powered-by {
            margin-top: 50px;
            margin-bottom: 0px;
            font-size: 12px;
        }

        @page {
            margin: 0;
        }
    </style>

</head>

<body>
    <div class="invoice-main" style="width: 720px;">
        <?php for ($i = 0; $i < 2; $i++) { ?>
            <div class="invoice">
                <div class="box" style="border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-bottom: 10px;">
                    <div class="col-3">
                        <img src="{{$config['logo']??''}}" class="logo" style="height: 75px" />
                    </div>
                    <div class="col-9">
                        <h3 class="site-name">
                            {{ $config['title_en'] ?? '' }}
                        </h3>
                        <p class="text-end invoice-date">
                            <strong>
                                {{ !empty($invoice->invoice_date) ? date('M d, Y', strtotime($invoice->invoice_date)) : '' }}
                                &nbsp;&nbsp;|&nbsp;&nbsp; {{$invoice->invoice_number ?? '' }}
                            </strong>
                        </p>
                    </div>
                </div>
                <div class="box mt-3">
                    <div class="col-9 student-info">
                        <b>Name :</b> {{ $invoice->online_admission->name ?? '' }} <br />
                        <b>Mobile :</b> {{ $invoice->online_admission->mobile ?? '' }} <br />
                        <b>Level :</b>
                        {{ $invoice->qualification->name ?? "" }}
                        ({{ $invoice->academic_session->name ?? "" }})
                        <br />
                        <b>Dept. :</b>
                        {{ $invoice->department->name ?? "" }}
                        ({{ $invoice->academic_class->name ?? "" }})
                        <br />
                        <b>Admission Roll :</b>
                        {{ $invoice->online_admission->admission_roll ?? '' }}
                        <br />
                        <b>Reg. No :</b>
                        {{ $invoice->online_admission->registration_no ?? '' }}
                    </div>
                    <div class="col-3 text-center">
                        @if(($invoice->status ?? '')=='success')
                        <div style="border: 2px solid #1f9d55; color:#1f9d55; width: 120px; height: 120px; line-height: 115px; border-radius: 9999px; font-weight: bold; font-size: 20px; margin: 0 auto;">
                            PAID
                        </div>
                        @endif
                    </div>
                </div>
                <div style="width: 100%; margin:15px;">
                    <h5 class="text-center invoice-text">
                        EPAYMENT INVOICE
                    </h5>
                </div>

                <table class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th style="width: 90px">Sl No.</th>
                            <th>Description</th>
                            <th style="width: 130px">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ "01" }}</td>
                            <td class="">{{$invoice->head ?$invoice->head->name : "" }}</td>
                            <td>{{ number_format($invoice->amount ?? 0,2)}}</td>
                        </tr>
                    </tbody>
                </table>
                <p class="inword">
                    Inword :
                    <?php echo App\\Models\\NumberToWord::convert_number_to_words((int) ($invoice->amount ?? 0)); ?> taka only
                </p>
                <p class="powered-by text-center">
                    Powered By Dynamic Host BD
                </p>
            </div>
        <?php } ?>
    </div>
</body>

</html>
