<?php

namespace App\Http\Controllers\api;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\WalletBalance;
use App\Models\WalletTransaction;
use DB;
use Illuminate\Http\Request;

class WalletController extends Controller {

    /**
     * Get wallet balance
     *
     * @return \Illuminate\Http\Response
     */
    public function walletBalance() {
        $data = WalletBalance::where( 'student_id', auth()->id() )->first()->amount ?? "0.00";
        return $this->sendResponse( $data );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $authID = auth()->user()->id;
        $query  = WalletTransaction::select( 'id', 'invoice_date', 'invoice_number', 'amount', 'payment_method', 'status' )
            ->where( 'student_id', $authID )->latest( 'id' );

        $datas = $query->paginate( 10 );
        return new Resource( $datas );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( Request $request ) {
        if ( $this->validateCheck( $request ) ) {
            try {
                if ( $request->amount < 100 ) {
                    return $this->sendError( "Sorry!! You can't recharge less than 100 taka", 422 );
                }

                // Check Recharge Payment
                $trans       = WalletTransaction::where( 'status', 'success' )->where( 'student_id', auth()->id() )->whereDate( 'invoice_date', date( 'Y-m-d' ) )->sum( 'paid_amount' );
                $transAmount = $trans + $request->amount ?? 0;
                if ( $transAmount > 100 ) {
                    return $this->sendError( "Sorry!! You can't recharge more than 100 taka per day", 422 );
                }

                // Check Generate Invoice
                $countTrans = WalletTransaction::where( 'status', 'pending' )->where( 'student_id', auth()->id() )->count();
                if ( $countTrans > 2 ) {
                    return $this->sendError( "Please payment from ''Service Balance'' List", 422 );
                }

                $request['created_from'] = 'app';

                $invoice = WalletTransaction::store( $request );

                // SET SSL Store ID & PASS
                $this->setStoreID( config( 'sslcommerz.wallet_store_id' ), config( 'sslcommerz.wallet_store_pass' ) );

                // SSL Payment
                $pay      = app( "App\Http\Controllers\BankApi\SslCommerzPaymentController" );
                $response = $pay->index( $invoice->id, 'wallet' );

                if ( empty( $response ) ) {
                    return $this->sendError( "Sorry!! Payment cannot proceed at this time, please try again", 422 );
                }

                DB::commit();
                return $this->sendResponse( $response, 200, "Payment gateway url" );

            } catch ( \Throwable $th ) {
                DB::rollBack();
                return $this->sendError( "Exceptions errors", 500, $th->getMessage() );
            }
        }
    }

    /**
     * Pay Invoice
     *
     * @param $invoice_id
     * @return \Illuminate\Http\Response
     */
    public function payInvoice( Request $request ) {
        try {
            $inv = WalletTransaction::find( $request->transaction_id );

            if ( empty( $inv ) ) {
                return $this->sendError( "Sorry!! Wrong Transactions, please try again", 422 );
            }

            if ( $inv->status == 'success' ) {
                return $this->sendError( "Sorry!! Already payment this invoice", 422 );
            }

            // Check Recharge Payment
            $trans       = WalletTransaction::where( 'status', 'success' )->where( 'student_id', auth()->id() )->whereDate( 'invoice_date', date( 'Y-m-d' ) )->sum( 'paid_amount' );
            $transAmount = $trans + $inv->amount ?? 0;
            if ( $transAmount > 100 ) {
                return $this->sendError( "Sorry!! You can't recharge more than 100 taka per day", 422 );
            }

            // SET SSL Store ID & PASS
            $this->setStoreID( config( 'sslcommerz.wallet_store_id' ), config( 'sslcommerz.wallet_store_pass' ) );

            // SSL Payment
            $pay      = app( "App\Http\Controllers\BankApi\SslCommerzPaymentController" );
            $response = $pay->index( $inv->id, "wallet" );

            if ( empty( $response ) ) {
                return $this->sendError( "Sorry!! Payment cannot proceed at this time, please try again", 422 );
            }

            return $this->sendResponse( $response, 200, "Payment gateway url" );
        } catch ( \Throwable $th ) {
            return $this->sendError( "Exceptions errors", 500, $th->getMessage() );
        }
    }

    /**
     * Generate Invoice
     *
     * @return \Illuminate\Http\Response
     */
    public function generateTransNo() {
        $transID = GlobalHelper::generate_id( WalletTransaction::class, 'invoice_number', [
            'pad_length' => 5,
            'prefix'     => "TR-",
        ] );
        return $this->sendResponse( $transID );
    }

    /**
     * Validate form field.
     *
     * @return \Illuminate\Http\Response
     */
    public function validateCheck( $request ) {
        return $request->validate( [
            "amount"         => "required",
            "invoice_number" => "required|unique:wallet_transactions",
            "invoice_date"   => "required",
        ] );
    }
}


