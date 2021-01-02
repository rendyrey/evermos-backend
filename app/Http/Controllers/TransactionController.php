<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helper\UserHelper;
use App\Helper\TransactionHelper;
use App\Models\Product;
use App\Models\Basket;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use DB;
use Carbon\Carbon;

/**
 * TransactionController
 * @author Rendy Reynaldy Anggradwiguna <rendyreynaldy@gmail.com>
 * @since 2021.01.01
 */
class TransactionController extends Controller
{
    /**
     * Check the stock of the product
     * @param integer $productId The id of the product
     * @return boolean
     */
    private function checkStock($productId) {
        return Product::where('id',$productId)->where('quantity','>', 0)->exists();
    }

    private function response($code, $message){
        return response()->json([
            'code' => $code,
            'message' => $message
        ], $code);
    }

    /**
     * Add product to basket
     * @param integer $productId The Id of the product
     * @param integer $amount The amount of product
     * @return string
     */
    public function addToBasket(Request $request, $productId, $amount = 1) {
        // check the existance of the productId
        if($productId == null) {
            return $this->response(400, "Product ID could not be null");
        }

        // check the existance of the product
        if (DB::table('products')->where('id', $productId)->doesntExist()){
            return $this->response(400, "Product doesn't exists");
        }

        // check stock/quantity of the product
        if (!$this->checkStock($productId)) {
            return $this->response(404, "Product have zero stock");
        }

        try{
            $user = UserHelper::getUser($request->token);

            // check if added product exists on the previous basket. 
            $checkBasket = Basket::where('product_id', $productId)->where('user_id',$user->id)->where('is_checked_out', 0)->first();
            
            // if the added product exists on user's basket, the product amount is accumulated.
            if ($checkBasket) {
                $checkBasket->amount += $amount;
                $checkBasket->save();
            
            } else { // if no, create new row on basket table for that productId
                $basket = new Basket();
                $basket->user_id = $user->id;
                $basket->product_id = $productId;
                $basket->amount = $amount;
                $basket->is_checked_out = 0;
                $basket->save();
            }
    
            return $this->response(201, "Product(s) successfully added to basket");
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
         
    }

    /**
     * Checkout the product from the basket
     * @return string
     */
    public function checkout(Request $request) {

        $user = UserHelper::getUser($request->token);
        $basket = $basket = Basket::where('user_id', $user->id)->where('is_checked_out', 0)->get();

        // if there's product on the basket
        if($basket->count() > 0) {
            DB::beginTransaction(); // begin DB transaction to prevent failed on the middle of transaction
            try{
                // create new transaction 
                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->order_date = Carbon::now();
                
                $total_transaction = 0;

                // accumulate the total of transaction
                foreach($basket as $bkey => $bvalue){
                    $total_transaction += (float) $bvalue->product->price * $bvalue->amount;
                }

                $transaction->total_transaction = $total_transaction;
                $transaction->status = TransactionHelper::WAITING_PAYMENT; // set status to waiting payment
                $transaction->save();
                // end of create new transaction 

                // add all product on the basket to transaction detail
                foreach($basket as $bkey => $bvalue){
                    $transactionDetail = new TransactionDetail();
                    $transactionDetail->transaction_id = $transaction->id;
                    $transactionDetail->product_id = $bvalue->product_id;
                    $transactionDetail->amount = $bvalue->amount;
                    $transactionDetail->price = (float) $bvalue->product->price;
                    $transactionDetail->total_price = (float) ($bvalue->amount * $transactionDetail->price);
                    $transactionDetail->save();
                }

                Basket::where('user_id',$user->id)->where('is_checked_out', 0)->update(['is_checked_out' => 1]); // update to checked out
                DB::commit();

                return $this->response(201, "Successfully Checkout");
            } catch (\Exception $e) {
                DB::rollback(); // rollback db if error connection in the middle of process
                return $this->response(500, $e->getMessage());
            }
            
        }else{
            return $this->response(404, "The user doesn't have any product(s) on basket");
        }

    }

    /**
     * Update transaction to paid
     * @param integer $transactionId The transaction id
     * @param integer $paymentId the payment method id
     * @return string
     */
    public function pay(Request $request, $transactionId, $paymentId = 1) {
        $user = UserHelper::getUser($request->token);
        
        DB::beginTransaction();
        try{
            $transaction = Transaction::where('user_id', $user->id)->where('id', $transactionId)->first();
    
            if(!$transaction) { // if the transaction id doesn't exists
                return $this->response(400, "There is no transaction with that transaction ID");
            }
            
            if($transaction->status != TransactionHelper::WAITING_PAYMENT) { // if transaction status is not waiting payment it means it has been paid
                return $this->response(400, "You can't pay this transaction. This transaction has been paid!");
            }

            $transactionDetail = TransactionDetail::where('transaction_id', $transactionId)->get();

            // loop to check product quantity
            foreach($transactionDetail as $ktDetail => $vtDetail) {
                $product = Product::findOrFail($vtDetail->product_id);
                if($product->quantity < 1) { // if quantity is zero, cancel the payment process
                    return $this->response(400, "You can't pay this transaction. There are product(s) thas has zero quantity");
                }

                $product->quantity -= $vtDetail->amount; // decrease product quantity
                $product->save();
            }
            
            $transaction->payment_id = $paymentId;
            $transaction->payment_date = Carbon::now();
            $transaction->status = TransactionHelper::PAID;
            $transaction->save();

            DB::commit();

            return $this->response(200, "Transacation have been succesfully paid!");
        }catch (\Exception $e) {
            DB::rollback();
            
            return $this->response(500, $e->getMessage());
        }
    }

    /**
     * Update transaction to next status
     * here's the list of status
     * WAITING_PAYMENT = 1
     * PAID = 2
     * PRODUCT_SENT = 3
     * PRODUCT_RECEIVED = 4
     * COMPLETED = 5
     * @param integer $transactionId The transaction id
     * @param integer $transactionStatus The status of transaction like explained above
     * @return string
     */
    public function processTransaction(Request $request, $transactionId, $transactionStatus) {
        $user = UserHelper::getUser($request->token);
        try{
            $transaction = Transaction::where('user_id', $user->id)->where('id', $transactionId)->first();
            // process the transaction if the transaction is not completed yet
            if($transaction->status < TransactionHelper::COMPLETED && $transaction->status != TransactionHelper::PAID) {
                $transaction = Transaction::where('user_id', $user->id)->where('id', $transactionId)->first();
                if($transaction->status >= $transactionStatus) { // return error if transaction have been sent/paid/
                    return $this->response(400, $this->processMessage(400, $transactionStatus));
                }
                $transaction->status = $transactionStatus;
                $transaction->save();
    
                return $this->response(200, $this->processMessage(200, $transactionStatus));
            }

            return $this->response(400, $this->processMessage(400, $transactionStatus));
            
        } catch(\Exception $e){
            
            return $this->response(500, $e->getMessage());
        }
    }

    private function processMessage($code, $transactionStatus) {
        $message['success'] = '';
        $message['error'] = '';

        if($transactionStatus == TransactionHelper::PRODUCT_SENT) {
            $message['success'] = "Package successfully sent!";
            $message['error'] = "You can't send this package because package had been sent!";
        }else if($transactionStatus == TransactionHelper::PRODUCT_RECEIVED){
            $message['success'] = "Package successfully received by the buyer";
            $message['error'] = "Sorry, package have already received by the buyer";
        }else if($transactionStatus == TransactionHelper::COMPLETED){
            $message['success'] = "Transaction done/complete.";
            $message['error'] = "Sorry, transaction have already completed!";
        }else if($transactionStatus == TransactionHelper::PAID){
            $message['success'] = "Transaction successfully paid";
            $message['error'] = "Sorry, transaction have already paid!";
        }else{
            return "Sorry, Bad Request!";
        }

        if($code == 200) {
            return $message['success'];
        }

        return $message['error'];
    }

    public function listOfTransaction($transactionStatus) {
        return Transaction::where('status',$transactionStatus)->get();
    }
}