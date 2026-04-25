<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\HostelFeeGenerate;
use App\Models\HostelFeeSetup;
use App\Models\HostelPayment;
use App\Models\System\SiteSetting;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PDF;

class HostelFeesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['fees']    = HostelFeeGenerate::hostelFeeGenerates();
        $data['history'] = HostelPayment::paymentHistory();

        return $this->sendResponse($data);
    }

    /**
     * Store && Pay Invoice
     *
     * @param $invoice_id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $fees = HostelPayment::where('student_id', auth()->id())
                ->whereHas('details', function ($q) use ($request) {
                    $q->whereIn('hostel_fee_generate_details_id', $request->months);
                })->get();

            // Already created this invoice
            if ($fees->count() > 0) {
                return $this->sendError("Sorry!! You have already created this month's invoice", 422);
            }

            $student = auth()->user();
            $inv     = HostelPayment::store($student, $request->months, 'app');

            // If hostel payment not created
            if (empty($inv)) {
                return $this->sendError("Sorry!! Something went wrong, please try again", 422);
            }

            // Fees Amount
            $fees = HostelFeeSetup::where('hostel_id', auth()->user()->hostel_id)->first();

            // If fees not found
            if (empty($fees)) {
                return $this->sendError("Sorry!! Fees amount not found", 422);
            }

            // SET SSL Store ID & PASS
            $this->setStoreID($fees->store_id, $fees->store_password);

            // SSL Payment
            $pay      = app("App\Http\Controllers\BankApi\SslCommerzPaymentController");
            $response = $pay->index($inv->id, "hostel");

            if (empty($response)) {
                return $this->sendError("Sorry!! Payment cannot proceed at this time, please try again", 422);
            }

            DB::commit();
            return $this->sendResponse($response, 200, "Payment gateway url");
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError("Exceptions errors", 500, $th->getMessage());
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function invoice(Request $request)
    {
        $invoice = HostelPayment::with(
            'student',
            'department',
            'qualification',
            'academic_class',
            'academic_session',
            'hostel:id,name',
            'details:id,hostel_payment_id,invoice_date,amount,account_head_id,year',
            'details.head'
        )
            ->where('student_id', auth()->id())
            ->where('id', $request->invoice_id)->first();

        // if invoice is not found
        if (empty($invoice)) {
            return $this->sendError("Sorry!! Invoice not found", 422);
        }

        if (empty($request->pdf)) {
            return $this->sendResponse($invoice);
        }

        $config = SiteSetting::first();

        $softwareID    = $invoice->student->student_id ?? null;
        $stdName       = Str::slug($invoice->student->name) ?? null;
        $invoiceNumber = $invoice->invoice_date;

        $pdf      = PDF::loadView('pdf.hostel_fees_invoice', compact('invoice', 'config'))->setPaper('a4', 'portrait');
        $fileName = "invoice({$invoiceNumber}__{$softwareID}__{$stdName}).pdf";
        return $pdf->download($fileName);
    }

    /**
     * Pay Invoice
     *
     * @param $invoice_id
     * @return \Illuminate\Http\Response
     */
    public function payInvoice(Request $request)
    {
        try {
            $inv = HostelPayment::find($request->invoice_id);

            // If invoice not found
            if (empty($inv)) {
                return $this->sendError("Sorry!! Invoice not found, please try again", 422);
            }

            // Fees Amount
            $fees = HostelFeeSetup::where('hostel_id', auth()->user()->hostel_id)->first();

            // If fees not found
            if (empty($fees)) {
                return $this->sendError("Sorry!! Fees amount not found", 422);
            }

            // SET SSL Store ID & PASS
            $this->setStoreID($fees->store_id, $fees->store_password);

            // SSL Payment
            $pay      = app("App\Http\Controllers\BankApi\SslCommerzPaymentController");
            $response = $pay->index($inv->id, "hostel");

            if (empty($response)) {
                return $this->sendError("Sorry!! Payment cannot proceed at this time, please try again", 422);
            }

            return $this->sendResponse($response, 200, "Payment gateway url");
        } catch (\Throwable $th) {
            return $this->sendError("Exceptions errors", 500, $th->getMessage());
        }
    }
}


