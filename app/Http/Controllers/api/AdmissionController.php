<?php

namespace App\Http\Controllers\api;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\AdmissionFeeSetup;
use App\Models\System\SiteSetting;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PDF;

class AdmissionController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurposes()
    {
        $query = AdmissionFeeSetup::with('head:id,name')
            ->select('id', 'account_head_id', 'amount');
        $query->whereSub('head', 'status', 'active');
        $data = $query->get();

        return $this->sendResponse($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($this->validateCheck($request)) {
            try {
                DB::beginTransaction();
                $data = $request->all();

                // Find Admission
                $admFees = AdmissionFeeSetup::find($request->admission_fee_setup_id);
                if (empty($admFees)) {
                    return $this->sendError("Sorry!! Wrong Transactions, please try again", 422);
                }

                // Check Today Payment Exists
                // $existsPayment = Admission::where( [
                //     'admission_roll'      => $request->admission_roll,
                //     'academic_session_id' => $request->academic_session_id,
                //     'mobile'              => $request->mobile,
                //     'payment_date'        => date( 'Y-m-d' ),
                // ] )->where( 'status', 'success' )->exists();

                // if ( !empty( $existsPayment ) ) {
                //     return $this->sendError( "Sorry!! Today you cannot no more payment", 422 );
                // }

                // Check Today Invoice Create but Not Payment Exists
                $existsInvoice = Admission::where([
                    'academic_session_id'       => $request->academic_session_id,
                    'admission_roll'            => $request->admission_roll,
                    'academic_qualification_id' => $request->academic_qualification_id,
                    'academic_class_id'         => $request->academic_class_id,
                    'mobile'                    => $request->mobile,
                ])->where('status', '!=', 'success')->exists();

                if (!empty($existsInvoice)) {
                    return $this->sendError("Sorry!! Already invoice created, please check invoice", 422);
                }

                $data['invoice_number'] = GlobalHelper::generate_id(Admission::class, 'invoice_number', [
                    'pad_length' => 5,
                    'prefix'     => "ADM-",
                ]);

                $data['name']            = strtoupper($request->name);
                $data['invoice_date']    = date('Y-m-d');
                $data['account_head_id'] = $admFees->account_head_id;
                $data['amount']          = $admFees->amount == 0 ? (int) $request->amount : $admFees->amount;
                $data['created_from']    = 'app';

                $adm = Admission::create($data);

                // SET SSL Store ID & PASS
                $this->setStoreID($admFees->store_id, $admFees->store_password);

                // SSL Payment
                $pay      = app("App\Http\Controllers\BankApi\SslCommerzPaymentController");
                $response = $pay->index($adm->id, "admission");

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
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function invoice(Request $request)
    {
        $invoice = Admission::with('head', 'academic_session', 'department', 'qualification', 'academic_class')
            ->where([
                'admission_roll' => $request->admission_roll,
                'mobile'         => $request->mobile,
            ])->latest('id')->first();

        if (empty($invoice)) {
            return $this->sendError("Sorry!! Invoice not found", 422);
        }

        if (empty($request->pdf)) {
            return $this->sendResponse($invoice);
        }

        $config = SiteSetting::first();

        $stdName       = Str::slug($invoice->name) ?? null;
        $invoiceNumber = $invoice->invoice_date;

        $pdf      = PDF::loadView('pdf.application_fees_invoice', compact('invoice', 'config'))->setPaper('a4', 'portrait');
        $fileName = "invoice({$invoiceNumber}__{$stdName}).pdf";
        return $pdf->download($fileName);
        // return $pdf->stream();
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
            $adm = Admission::where([
                'admission_roll' => $request->admission_roll,
                'mobile'         => $request->mobile,
            ])->latest('id')->first();

            if (empty($adm)) {
                return $this->sendError("Sorry!! Wrong Transactions, please try again", 422);
            }

            if ($adm->status == 'success') {
                return $this->sendError("Sorry!! Already payment this invoice", 422);
            }

            // Check Today Payment Exists
            // $existsPayment = Admission::where([
            //     'admission_roll'      => $adm->admission_roll,
            //     'academic_session_id' => $adm->academic_session_id,
            //     'mobile'              => $adm->mobile,
            //     'payment_date'        => date('Y-m-d'),
            // ])->where('status', 'success')->exists();

            // if (!empty($existsPayment)) {
            //     return $this->sendError("Sorry!! Today you cannot no more payment", 422);
            // }

            // Find Admission
            $admFees = AdmissionFeeSetup::find($adm->admission_fee_setup_id);

            // SET SSL Store ID & PASS
            $this->setStoreID($admFees->store_id, $admFees->store_password);

            // SSL Payment
            $pay      = app("App\Http\Controllers\BankApi\SslCommerzPaymentController");
            $response = $pay->index($adm->id, "admission");

            if (empty($response)) {
                return $this->sendError("Sorry!! Payment cannot proceed at this time, please try again", 422);
            }

            return $this->sendResponse($response, 200, "Payment gateway url");
        } catch (\Throwable $th) {
            return $this->sendError("Exceptions errors", 500, $th->getMessage());
        }
    }

    /**
     * Validate form field.
     *
     * @return \Illuminate\Http\Response
     */
    public function validateCheck($request)
    {
        return $request->validate([
            "academic_class_id"         => "required",
            "academic_session_id"       => "required",
            "academic_qualification_id" => "required",
            "name"                      => "required",
            "admission_roll"            => "required",
            "mobile"                    => "required",
        ]);
    }
}


