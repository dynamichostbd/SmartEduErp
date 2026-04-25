<?php

namespace App\Http\Controllers\api;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\AccountHead;
use App\Models\FeeSetup;
use App\Models\FeeSetupDetails;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\RegistrationNoVerify;
use App\Models\Student;
use App\Models\SubjectAssign;
use App\Models\SubjectAssignDetails;
use App\Models\System\SiteSetting;
use App\Models\WalletBalance;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use PDF;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $query = Invoice::with('head')->latest('id')
            ->select(
                'id',
                'invoice_date',
                'invoice_number',
                'account_head_id',
                'amount',
                'status'
            )
            ->where('student_id', auth()->id());
        $query->whereAny('account_head_id', $request->account_head_id);

        $datas = $query->paginate(10);
        return new Resource($datas);
    }

    // public function checkDependHead(Request $request)
    // {
    //     $dependHead = "";
    //     $studentID  = auth()->id();
    //     $conditions = Student::commonArr();
    //     $fees       = FeeSetupDetails::find($request->fees_details_id);

    //     if (!empty($fees)) {
    //         $dependHead = $fees->depend_head->name ?? "";
    //         $exists     = Invoice::where($conditions)
    //             ->where('student_id', $studentID)
    //             ->where('account_head_id', $fees->depend_head_id)
    //             ->where('status', 'success')
    //             ->first();

    //         $status = empty($exists) && !empty($dependHead) ? true : false;
    //         if ($status) {
    //             return $this->sendError("You have to pay {$dependHead} first", 422);
    //         }
    //         return $this->sendResponse(true, 200, "Payment Clear");
    //     }
    //     return $this->sendError("Fees Not Found", 422);
    // }

    public function checkDependHead(Request $request)
    {
        $studentID = auth()->id();
        $conditions = Student::commonArr();
        $fees = FeeSetupDetails::find($request->fees_details_id);

        if (!empty($fees)) {
            // Parse depend_head_id (JSON) into an array
            $dependHeadIds = json_decode($fees->depend_head_id, true) ?? [];

            if (empty($dependHeadIds)) {
                return response()->json([
                    "status" => true,
                    "message" => "No dependency heads found, you can proceed"
                ]);
            }

            // Check if any dependency has been paid
            $paid = Invoice::where($conditions)
                ->where('student_id', $studentID)
                ->whereIn('account_head_id', $dependHeadIds)
                ->where('status', 'success')
                ->exists();

            if ($paid) {
                return response()->json([
                    "status" => true,
                    "message" => "At least one dependency fee has been paid, you can proceed"
                ]);
            } else {
                // None paid, list dependency heads
                $unpaidHeads = AccountHead::whereIn('id', $dependHeadIds)->pluck('name')->toArray();
                $message = "You have to pay at least one of the following fees first: " . implode(', ', $unpaidHeads);

                return response()->json([
                    "status" => false,
                    "message" => $message
                ]);
            }
        }

        return response()->json([
            "status" => false,
            "message" => "Fee details not found"
        ]);
    }

    /**
     * Check Depend Head
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkRegistrationNo(Request $request)
    {
        $regNoVerify = RegistrationNoVerify::where([
            'academic_session_id'       => auth()->user()->academic_session_id,
            'academic_qualification_id' => auth()->user()->academic_qualification_id,
            'department_id'             => auth()->user()->department_id,
            'academic_class_id'         => auth()->user()->academic_class_id,
        ])->first();

        if (empty($regNoVerify)) {
            return response()->json(false, 200);
        }

        foreach ($regNoVerify->registration_no_lists as $key => $roll) {
            if ((string) $roll === (string) $request->registration_no) {
                return response()->json(false, 200);
            }
        }

        return response()->json(true, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $siteSetting = App::make('siteSettingObj');
            $conditions  = Student::commonArr();

            // Fees Amount
            $feeSetup = FeeSetup::where($conditions)->first();
            $fees     = $feeSetup->details()
                ->where('account_head_id', $request->account_head_id)
                ->first();

            // If fees not found
            if (empty($fees)) {
                return $this->sendError("Sorry!! You cannot select any payment type", 422);
            }

            // Check Generate Invoice
            $existInvoice = Invoice::where($conditions)
                ->where('student_id', auth()->id())
                ->where('account_head_id', $fees->account_head_id)
                ->where('status', '!=', 'success')
                ->first();

            // If invoice found return false
            if (!empty($existInvoice)) {
                return $this->sendError("Already generate invoice, please pay from payment history", 422);
            }

            // Payable Amount & Chrage Balance
            $amount = !empty($request->form_fee) ? $request->amount : $fees->amount;
            $charge = Invoice::percentAmount($amount);

            // Wallet Balacne Check
            if (!empty($siteSetting['service_charge_percent'])) {
                $wallet = WalletBalance::where('student_id', auth()->user()->id)->first();
                if (!empty($wallet) && $wallet->amount < $charge) {
                    return $this->sendError("Sorry!! Your balance is less than charge amount, please recharge your balance", 422);
                }
            }

            // Create Invoice
            $request['created_from'] = 'app';
            $request['charge']       = $charge;
            $request['total_amount'] = $amount + $fees->fine_amount;

            $request['academic_session_id'] = auth()->user()->academic_session_id ?? null;

            $invoice = Invoice::store($request, $fees->account_head_id, $fees);

            // SET SSL Store ID & PASS
            $this->setStoreID($fees->gateway->store_id, $fees->gateway->store_password);

            // SSL Payment
            $pay      = app("App\Http\Controllers\BankApi\SslCommerzPaymentController");
            $response = $pay->index($invoice->id, "invoice");

            if (empty($response)) {
                return $this->sendError("Sorry!! Payment cannot proceed at this time, please try again", 422);
            }

            DB::commit();
            return $this->sendResponse($response, 200, "Payment gateway url");
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
        }
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
            $id = $request->invoice_id;
            if (empty($id)) {
                return $this->sendError("Invoice ID is required", 422);
            }

            $inv = Invoice::where('student_id', auth()->id())->where('id', $id)->first();

            if (empty($inv)) {
                return $this->sendError("Sorry!! Wrong Invoice, please try again", 422);
            }

            $gateway = PaymentGateway::find($inv->payment_gateway_id);

            // SET SSL Store ID & PASS
            $this->setStoreID($gateway->store_id, $gateway->store_password);

            // SSL Payment
            $pay      = app("App\Http\Controllers\BankApi\SslCommerzPaymentController");
            $response = $pay->index($id, "invoice");

            if (empty($response)) {
                return $this->sendError("Sorry!! Payment cannot proceed at this time, please try again", 422);
            }

            return $this->sendResponse($response, 200, "Payment gateway url");
        } catch (\Throwable $th) {
            return $this->sendError("Exceptions errors", 500, $th->getMessage());
        }
    }

    /**
     * Download Invoice
     *
     * @return \Illuminate\Http\Response
     */
    public function invoice(Request $request)
    {
        $invoice = Invoice::with(
            'head',
            'student',
            'department',
            'qualification',
            'academic_class',
            'academic_session'
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

        $pdf      = PDF::loadView('pdf.invoice', compact('invoice', 'config'))->setPaper('a4', 'portrait');
        $fileName = "invoice({$invoiceNumber}__{$softwareID}__{$stdName}).pdf";
        return $pdf->download($fileName);
        // return $pdf->stream();
    }

    /**
     * Generate Invoice
     *
     * @return \Illuminate\Http\Response
     */
    public function generateInvoiceNo()
    {
        $data = GlobalHelper::generate_id(Invoice::class, 'invoice_number', [
            'pad_length' => 5,
            'prefix'     => "INV-",
        ]);
        return $this->sendResponse($data);
    }

    public function getSubjectsByPaymentType(Request $request)
    {
        try {
            $paymentTypeId = $request->input('fee_setup_id');
            $accoutHeadId = (int) $request->input('account_head_id');


            // Get the fee setup
            $fee = FeeSetup::find($paymentTypeId);

            $academicClass = FeeSetupDetails::where('fee_setup_id', $paymentTypeId)->where('account_head_id', $accoutHeadId)->first();


            if (!$fee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Fee setup not found',
                    'data' => ['subjects' => []]
                ], 404);
            }

            if (!$academicClass) {
                return response()->json([
                    'status' => false,
                    'message' => 'Academic class details not found for the selected fee',
                    'data' => ['subjects' => []]
                ], 404);
            }

            // Check if it's an improvement type
            if ($request->improvement == 1) {

                $subjectAssignment = SubjectAssign::where([
                    'department_id' => $fee->department_id,
                    'academic_qualification_id' => $fee->academic_qualification_id,
                    'academic_class_id' => $academicClass->academic_class_id_improvment
                ])->first();

                if (!$subjectAssignment) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Subject assignment not found',
                        'data' => ['subjects' => []]
                    ], 200);
                }

                $subjects = [];

                // Get subject details
                $subjectDetails = SubjectAssignDetails::where('subject_assign_id', $subjectAssignment->id)
                    ->with('subject')
                    ->get();

                foreach ($subjectDetails as $detail) {
                    if ($detail->subject) {
                        $subjects[] = [
                            'id' => $detail->subject->id,
                            'name' => $detail->subject->name_en,
                            'amount' => $detail->amount ?? $detail->subject->amount ?? 0,
                            'subject_assign_detail_id' => $detail->id
                        ];
                    }
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Subjects fetched successfully',
                    'data' => ['subjects' => $subjects]
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'No improvement subjects required',
                'data' => ['subjects' => []]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch subjects. ' . $e->getMessage()
            ], 500);
        }
    }
}


