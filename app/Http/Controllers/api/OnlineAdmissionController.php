<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\FeeSetup;
use App\Models\Invoice;
use App\Models\OnlineAdmission;
use App\Models\OnlineAdmissionRollVerify;
use App\Models\Student;
use App\Models\System\SiteSetting;
use App\Traits\ResizeTrait;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;
use App\Models\FeeSetupDetails;
use App\Traits\ImageUpload;

class OnlineAdmissionController extends Controller
{

    use ResizeTrait , ImageUpload;

    protected $resizeArr = [
        ["width" => 300, "height" => 300],
    ];
    protected $resize_type = "force";

    /**
     * Check application fees
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkApplicationFees(Request $request)
    {

        $admission = Admission::with('department', 'qualification', 'academic_class', 'academic_session')
            ->where([
                'invoice_number' => $request->application_invoice_no,
                'admission_roll' => $request->admission_roll,
                'status'         => 'success',
            ])->first();
        if (empty($admission)) {
            return $this->sendError("Sorry!! Applicatioin Fees not found", 422);
        }

        // Check Same Admission Roll / Mobile
        $exist = OnlineAdmission::where([
            'admission_roll'      => $request->admission_roll,
            'academic_session_id' => $admission->academic_session_id,
            'department_id'       => $admission->department_id,
        ])->latest('id')->exists();
        if ($exist) {
            return $this->sendError("Already Registered this Admission Roll", 422);
        }

        return $this->sendResponse($admission);
    }

    /**
     * Check admission roll
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkAdmissionRoll(Request $request)
    {
        $rollVerify = OnlineAdmissionRollVerify::where([
            'academic_session_id'       => $request->academic_session_id,
            'academic_qualification_id' => $request->academic_qualification_id,
            'department_id'             => $request->department_id,
            'academic_class_id'         => $request->academic_class_id,
        ])->first();
        if (empty($rollVerify)) {
            return $this->sendError("Sorry!! Roll Not Found", 422);
        }

        // Check Same Admission Roll
        $exist = OnlineAdmission::where([
            'admission_roll'      => $request->admission_roll,
            'academic_session_id' => $request->academic_session_id,
            'department_id'       => $request->department_id,
        ])->latest('id')->exists();
        if ($exist) {
            return $this->sendError("Already Registered this Admission Roll", 422);
        }

        foreach ($rollVerify->roll_lists as $key => $roll) {
            if ((string) $roll === $request->admission_roll) {
                return $this->sendResponse(
                    [
                        'roll_number' => $roll,
                        'name' => $rollVerify->name_lists[$roll] ?? ''
                    ],
                    200,
                    "Roll found"
                );
            }
        }
        return $this->sendError("Sorry!! Roll Not Found", 422);
    }

    /**
     * Get online admission account heads
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getPaymentHeads(Request $request)
    {

        $onlineAdmission = OnlineAdmission::where([
            'admission_roll' => $request->admission_roll,
            'mobile'         => $request->mobile,
        ])->latest('id')->first();

        // if online admission not found
        if (empty($onlineAdmission)) {
            return $this->sendError("Sorry!! Applicatioin not found", 422);
        }

        $conditions = OnlineAdmission::commonArr($onlineAdmission);

        // paid invoices
        $paidInvoices = Invoice::where($conditions)
            ->where('academic_session_id', $onlineAdmission->academic_session_id)
            ->where('online_admission_id', $onlineAdmission->id)
            ->where('status', 'success')
            ->pluck('account_head_id');

        // fee setup
        $fees = FeeSetup::where($conditions)->first();

        if (empty($fees)) {
            return $this->sendError("Fees are not available", 422);
        }

        if (!empty($fees)) {
            $feesDetails = $fees->details()->where('online_addmission_fees', 1)->count();
            if (empty($feesDetails)) {
                return $this->sendError("Fees are not available", 422);
            }

            $details = $fees->details()
                ->select('account_head_id', 'amount', 'service_charge')
                ->with('account_head')
                ->where('online_addmission_fees', 1)
                ->whereDate('start_date', '<=', date('Y-m-d'))
                ->whereDate('additional_date', '>=', date('Y-m-d'))
                ->whereNotIn('account_head_id', $paidInvoices)
                ->get();

            if (empty($details)) {
                return $this->sendError("You have already paid all the fees", 422);
            }
        }

        return $this->sendResponse($details ?? []);
    }

    /**
     * Check Depend Head
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkDependHead(Request $request)
    {
        $dependHead = "";
        $onlineAd   = OnlineAdmission::where('mobile', $request->mobile)->latest('id')->first();
        $fees       = FeeSetupDetails::find($request->id);

        if (!empty($fees) && !empty($onlineAd)) {
            $dependHead = $fees->depend_head->name ?? "";
            $exists     = Invoice::where('online_admission_id', $onlineAd->id)
                ->where('account_head_id', $fees->depend_head_id)
                ->where('status', 'success')
                ->first();

            $status = empty($exists) ? true : false;
        }
        return response()->json([
            "status"  => $status ?? false,
            "message" => "You have to pay {$dependHead} first",
        ]);
    }

    /**
     * Single document upload
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function documentUpload(Request $request)
    {
        if ($this->fileValidate($request)) {
            try {
                if (!empty($request->type) && $request->type == 'profile') {
                    $imagePath = $this->resizer($request->file, ['student_profile']);
                    $file_path = $imagePath['resize0'] ?? null;
                } else {
                    $this->makeDir('online_admission_dummy_files');
                    $upload_path = storage_path("app/public/upload/online_admission_dummy_files/");
                    $fileName    = $this->resize_image($request->file, $upload_path, '595', '842');
                    $file_path   = "upload/online_admission_dummy_files/{$fileName}";
                }
                return $this->sendResponse($file_path);
            } catch (\Throwable $th) {
                return $this->sendError("Exceptions errors", 500, $th->getMessage());
            }
        }
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
                $config = App::make('siteSettingObj');
                DB::beginTransaction();

                $data = $request->all();

                // Check Same Admission Roll / Mobile
                $existStd = Student::where([
                    'admission_id'        => $request->admission_roll,
                    'academic_session_id' => $request->academic_session_id,
                    'department_id'       => $request->department_id,
                ])->exists();

                $existOA = OnlineAdmission::where([
                    'admission_roll'      => $request->admission_roll,
                    'academic_session_id' => $request->academic_session_id,
                    'department_id'       => $request->department_id,
                ])->latest('id')->exists();

                if ($existOA || $existStd) {
                    return $this->sendError("Already Registered this Admission Roll", 422);
                }

                $existMobile = OnlineAdmission::where([
                    'mobile' => $request->mobile,
                    'academic_session_id' => $request->academic_session_id,
                ])->latest('id')->exists();
                if ($existMobile) {
                    return $this->sendError("Already Registered this Mobile Number", 422);
                }

                // Check Admission Fees
                if ((bool) $request->check_application_fees) {
                    $admission = Admission::where([
                        'invoice_number' => $request->application_invoice_no,
                        'admission_roll' => $request->admission_roll,
                    ])->exists();

                    if (empty($admission)) {
                        return $this->sendError("Sorry!! Applicatioin Fees Not Found", 422);
                    }
                }

                // Docements
                $documents = $request->documents ?? [];
                if (!empty($documents)) {
                    foreach ($documents as $key => $document) {
                        $destination = str_replace("online_admission_dummy_files", "admission_files", $document['file']);
                        if (Storage::disk('public')->exists($document['file'])) :
                            Storage::move($document['file'], $destination);
                            $documents[$key]['file'] = $destination;
                        endif;
                    }
                }

                $data['name']         = strtoupper($data['name']);
                $data['fathers_name'] = strtoupper($data['fathers_name']);
                $data['mothers_name'] = strtoupper($data['mothers_name']);
                $data['address']      = strtoupper($data['address']);

                $data['documents']       = json_encode($documents);

                $std = OnlineAdmission::create($data);

                DB::commit();
                return $this->sendResponse([], 200, 'Admission Form Submit Sccessfully, Please Payment!');
            } catch (\Throwable $th) {
                DB::rollBack();
                return $this->sendError("Exceptions errors", 500, $th->getMessage());
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function payInvoice(Request $request)
    {
        try {
            DB::beginTransaction();

            $onlineAdmission = OnlineAdmission::where([
                'admission_roll' => $request->admission_roll,
                'mobile'         => $request->mobile,
            ])->latest('id')->first();

            // if online admission not found
            if (empty($onlineAdmission)) {
                return $this->sendError("Sorry!! Applicatioin Fees Not Found", 422);
            }

            $conditions = OnlineAdmission::commonArr($onlineAdmission);

            // Fees Amount
            $feeSetup = FeeSetup::where($conditions)->first();
            $fees     = $feeSetup->details()
                ->where('account_head_id', $request->account_head_id)
                ->first();

            // If fees not found
            if (empty($fees)) {
                return $this->sendError("Sorry!! You cannot select any payment type", 422);
            }

            // Payable Amount & Chrage Balance
            $amount         = $fees->amount;
            $charge         = !empty($fees->service_charge) ? Invoice::serviceCharge($amount, 'admission_fees_charge_percent') : 0;
            $gateway_charge = !empty($fees->service_charge) ? Invoice::serviceCharge($amount, 'admission_fees_gateway_percent') : null;

            // Create Invoice Data
            $request['charge']              = $charge;
            $request['gateway_charge']      = $gateway_charge;
            $request['total_amount']        = $amount + $fees->fine_amount;
            $request['academic_session_id'] = $onlineAdmission->academic_session_id;
            $request['online_admission_id'] = $onlineAdmission->id;
            $request['created_from']        = 'app';
            $request->merge($conditions);

            // Check Generate Invoice
            $invoice = Invoice::where($conditions)
                ->where('academic_session_id', $onlineAdmission->academic_session_id)
                ->where('online_admission_id', $onlineAdmission->id)
                ->where('account_head_id', $fees->account_head_id)
                ->where('status', '!=', 'success')
                ->first();

            // If invoice not found, generate a new invoice for payment
            if (empty($invoice)) {
                $invoice = Invoice::store($request, $fees->account_head_id, $fees);
            }

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
     * get invoices
     *
     * @return \Illuminate\Http\Response
     */
    public function invoice(Request $request)
    {
        $onlineAdmission = OnlineAdmission::where([
            'admission_roll' => $request->admission_roll,
            'mobile'         => $request->mobile,
        ])->latest('id')->first();

        // if online admission not found
        if (empty($onlineAdmission)) {
            return $this->sendError("Sorry!! Online Admission Not Found", 422);
        }

        $invoice = Invoice::with(
            'head',
            'online_admission',
            'department',
            'qualification',
            'academic_class',
            'academic_session'
        )
            ->where('online_admission_id', $onlineAdmission->id)
            ->get();

        return $this->sendResponse($invoice);
    }

    /**
     * Download Invoice
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadInvoice($id)
    {
        $invoice = Invoice::with(
            'head',
            'online_admission',
            'department',
            'qualification',
            'academic_class',
            'academic_session'
        )->where('id', $id)->first();

        if (empty($invoice)) {
            return $this->sendError("Invoice not found", 422);
        }

        $config = SiteSetting::first();

        $student_name  = $invoice->online_admission->name ?? null;
        $stdName       = Str::slug($student_name);
        $invoiceNumber = $invoice->invoice_date;

        $pdf      = PDF::loadView('pdf.online_admission_invoice', compact('invoice', 'config'))->setPaper('a4', 'portrait');
        $fileName = "invoice({$invoiceNumber}__{$stdName}).pdf";
        return $pdf->download($fileName);

        // return $pdf->stream();
    }


    // public function downloadForm(Request $request)
    // {
    //     $data = OnlineAdmission::where([
    //         'admission_roll' => $request->admission_roll,
    //         'mobile'         => $request->mobile,
    //     ])->latest('id')->first();

    //     if (!empty($request->json_data)) {
    //         return $this->sendResponse($data);
    //     }

    //     $config = SiteSetting::first();

    //     $stdName = Str::slug($data->name) ?? null;
    //     $mobile  = $data->mobile;

    //     $pdf      = PDF::loadView('pdf.online_admission_form', compact('data', 'config'))->setPaper('a4', 'portrait');
    //     $fileName = "online-admission-form({$stdName}-{$mobile}).pdf";
    //     return $pdf->download($fileName);
    // }

    public function downloadForm(Request $request)
    {
        $data = OnlineAdmission::where([
            'admission_roll' => $request->admission_roll,
            'mobile'         => $request->mobile,
        ])->latest('id')->first();

        if (!$data) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        if (!empty($request->json_data)) {
            return $this->sendResponse($data);
        }

        $config = SiteSetting::first();

        $stdName = \Str::slug($data->name);
        $mobile  = $data->mobile;

        $pdf = \PDF::loadView('pdf.online_admission_form', compact('data', 'config'))
            ->setPaper('a4', 'portrait');

        $fileName = "online-admission-form({$stdName}-{$mobile}).pdf";

        // ✅ Save directly in public/pdfs
        $folderPath = public_path('pdfs');

        // create folder if not exists
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        $fullPath = $folderPath . '/' . $fileName;

        file_put_contents($fullPath, $pdf->output());

        // ✅ Generate URL
        $url = asset('pdfs/' . $fileName);

        return response()->json([
            'success' => true,
            'url' => $url
        ]);
    }

    // public function downloadForm(Request $request)
    // {
    //     $data = OnlineAdmission::where([
    //         'admission_roll' => $request->admission_roll,
    //         'mobile'         => $request->mobile,
    //     ])->latest('id')->first();

    //     if (!$data) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Data not found'
    //         ], 404);
    //     }

    //     $config = SiteSetting::first();

    //     // Generate PDF and save instead of download
    //     $stdName = Str::slug($data->name) ?? null;
    //     $mobile  = $data->mobile;
    //     $fileName = "online-admission-form({$stdName}-{$mobile}).pdf";

    //     $pdf = PDF::loadView('pdf.online_admission_form', compact('data', 'config'))->setPaper('a4', 'portrait');
    //     $path = public_path("uploads/forms/{$fileName}");
    //     $pdf->save($path);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Form generated successfully',
    //         'data'    => $data,
    //         'pdf_url' => url("uploads/forms/{$fileName}")
    //     ]);
    // }


    /**
     * Validation check====
     */
    private function validateCheck($request)
    {
        return $request->validate([
            "profile"                   => "required",
            "academic_class_id"         => "required",
            "academic_session_id"       => "required",
            "academic_qualification_id" => "required",
            "name"                      => "required",
            "fathers_name"              => "required",
            "mothers_name"              => "required",
            "mobile"                    => "required|unique:online_admissions",
            "email"                     => "nullable",
            "guardian_type"             => "required",
            "religion"                  => "required",
            "gender"                    => "required",
            "address"                   => "required",
            "guardian_type"             => "required",
            "admission_roll"            => "required|unique:online_admissions",
            "documents.*.file"          => "required",
        ]);
    }

    /**
     * Validation check====
     */
    private function fileValidate($request)
    {
        return $request->validate([
            "file" => "required|mimes:jpg,jpeg,png|file",
        ]);
    }
}


