<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Razorpay\Api\Api;
use App\Models\Parents;
use App\Models\FeesPaid;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\FeesChoiceable;
use App\Models\UserNotification;
use App\Models\PaidInstallmentFee;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    //razorpay webhooks
    public function razorpay(Request $request)
    {
        try {
            // get the json data of payment
            $webhookBody = $request->getContent();
            $webhookBody = file_get_contents('php://input');
            $data = json_decode($webhookBody);

            // gets the signature from header
            $webhookSignature = $request->header('X-Razorpay-Signature');
            $webhookSecret = env('RAZORPAY_WEBHOOK_SECRET');

            $api = new Api(env('RAZORPAY_API_KEY'), env('RAZORPAY_SECRET_KEY'));

            // get the metadata
            $parent_id = $data->payload->payment->entity->notes->parent_id;
            $student_id = $data->payload->payment->entity->notes->student_id;
            $class_id = $data->payload->payment->entity->notes->class_id;
            $session_year_id = $data->payload->payment->entity->notes->session_year_id;
            $payment_transaction_id = $data->payload->payment->entity->notes->payment_transaction_id;
            $is_fully_paid = $data->payload->payment->entity->notes->is_fully_paid;
            $type_of_fee = $data->payload->payment->entity->notes->type_of_fee;
            $is_due_charges = $data->payload->payment->entity->notes->is_due_charges;
            $due_charges = $data->payload->payment->entity->notes->due_charges ?? null;
            $optional_paid_data = json_decode($data->payload->payment->entity->notes->optional_fees_paid) ?? null;
            $installment_paid_data = json_decode($data->payload->payment->entity->notes->installment_fees_paid) ?? null;

            // get the current today's date
            $current_date = Carbon::now()->format('Y-m-d');

            //get the payment_id
            $payment_id  = $data->payload->payment->entity->id;

            Log::error(json_encode($data->event));

            //if the transaction is success
            if (isset($data->event) && $data->event == 'payment.captured') {

                //checks the signature
                $expectedSignature = hash_hmac("SHA256", $webhookBody, $webhookSecret);
                Log::error("expectedSignature --->" . $expectedSignature);
                Log::error("Header Signature --->" . $webhookSignature);

                if ($expectedSignature == $webhookSignature) {
                    Log::error("Signature Matched --->");
                }
                $api->utility->verifyWebhookSignature($webhookBody, $webhookSignature, $webhookSecret);

                // udpate data in payment transaction table local
                $transaction_db = PaymentTransaction::find($payment_transaction_id);
                if (!empty($transaction_db)) {
                    Log::error("INSIDE TRANSACTION DB");
                    if ($transaction_db->status != 1) {
                        Log::error("INSIDE TRANSACTION DB STATUS");
                        //get the total amount from table
                        $total_amount = $transaction_db->total_amount;

                        //udpate the values in payment transaction
                        $transaction_db->payment_id = $payment_id;
                        $transaction_db->payment_status = 1;
                        $transaction_db->save();

                        // Add due charges of fully Paid Complusory Amount
                        if ($type_of_fee == 0 && $is_due_charges == 1) {
                            $add_due_charges = new FeesChoiceable();
                            $add_due_charges->student_id = $student_id;
                            $add_due_charges->class_id = $class_id;
                            $add_due_charges->is_due_charges = 1;
                            $add_due_charges->total_amount = $due_charges;
                            $add_due_charges->session_year_id = $session_year_id;
                            $add_due_charges->save();
                        }

                        if(isset($installment_paid_data) && !empty($installment_paid_data)){
                            Log::info("Paid Installment Fee Status Updated");
                            foreach($installment_paid_data as $row)
                            {
                                $db =  PaidInstallmentFee::find($row);
                                if(!empty($db))
                                {
                                    if($db->status != 1)
                                    {
                                        $db->status = 1;
                                        $db->save();
                                    }
                                    // Log::error("Installment status updated", ['id' => $db->id, 'status' => $db->status]);
                                }
                            }

                        }else{
                            Log::info('NO INSTALLMENT DATA');
                        }

                        if(isset($optional_paid_data) && !empty($optional_paid_data)){
                            Log::info("Optional Fees Status Updated");
                            foreach($optional_paid_data as $row)
                            {
                                $db =  FeesChoiceable::find($row);
                                if(!empty($db))
                                {
                                    if($db->status != 1)
                                    {
                                        $db->status = 1;
                                        $db->save();
                                    }
                                    // Log::error("FeesChoiceable status updated", ['id' => $db->id, 'status' => $db->status]);
                                }
                            }
                        }else{
                            Log::info('NO OPTIONAL DATA');
                        }

                        // add data in fees paid table local
                        $update_fees_paid_query = FeesPaid::where(['student_id'=> $student_id, 'class_id' => $class_id , 'session_year_id' => $session_year_id]);
                        if($update_fees_paid_query->count()){
                            $update_fee_paid_data = FeesPaid::findOrFail($update_fees_paid_query->first()->id);
                            $update_fee_paid_data->total_amount = ($update_fees_paid_query->first()->total_amount + $total_amount);
                            $update_fee_paid_data->is_fully_paid = $is_fully_paid;
                            $update_fee_paid_data->save();
                        }else{
                            $fees_paid_db = new FeesPaid();
                            $fees_paid_db->parent_id = $parent_id;
                            $fees_paid_db->student_id = $student_id;
                            $fees_paid_db->class_id = $class_id;
                            $fees_paid_db->total_amount = $total_amount;
                            $fees_paid_db->date = $current_date;
                            $fees_paid_db->session_year_id = $session_year_id;
                            $fees_paid_db->is_fully_paid = $is_fully_paid;
                            $fees_paid_db->save();
                        }

                        http_response_code(200);

                        $user = Parents::where('id', $parent_id)->pluck('user_id');
                        $body = 'Amount :- ' . $total_amount;
                        $type = 'online';
                        $image = null;
                        $userinfo = null;

                        $notification = new Notification();
                        $notification->send_to = 2;
                        $notification->title = 'Payment Success';
                        $notification->message = $body;
                        $notification->type = $type;
                        $notification->date = Carbon::now();
                        $notification->is_custom = 0;
                        $notification->save();
                        foreach($user as $data)
                        {
                            $user_notification = new UserNotification();
                            $user_notification->notification_id = $notification->id;
                            $user_notification->user_id = $data;
                            $user_notification->save();
                        }

                        send_notification($user, 'Payment Success', $body, $type, $image, $userinfo);
                    }else{
                        Log::error("Transaction Already Successed --->");
                        return false;
                    }
                } else {
                    Log::error("Payment Transaction id not found --->");
                    return false;
                }
            }

            //if the transaction is failed
            if (isset($data->event) && $data->event == 'payment.failed') {
                $transaction_db = PaymentTransaction::find($payment_transaction_id);
                if (!empty($transaction_db)) {
                    $total_amount = $transaction_db->total_amount;
                    $transaction_db->payment_id = $payment_id;
                    $transaction_db->payment_status = 0;
                    $transaction_db->save();
                    http_response_code(400);

                    FeesChoiceable::where('payment_transaction_id',$payment_transaction_id)->where('status',0)->delete();
                    PaidInstallmentFee::where('payment_transaction_id',$payment_transaction_id)->where('status',0)->delete();

                    $user = Parents::where('id', $parent_id)->pluck('user_id');
                    $body = 'Amount :- ' . $total_amount;
                    $type = 'online';
                    $image = null;
                    $userinfo = null;

                    $notification = new Notification();
                    $notification->send_to = 2;
                    $notification->title = 'Payment Failed';
                    $notification->message = $body;
                    $notification->type = $type;
                    $notification->date = Carbon::now();
                    $notification->is_custom = 0;
                    $notification->save();

                    foreach($user as $data)
                    {
                        $user_notification = new UserNotification();
                        $user_notification->notification_id = $notification->id;
                        $user_notification->user_id = $data;
                        $user_notification->save();
                    }
                    send_notification($user, 'Payment Failed', $body, $type, $image, $userinfo);
                }else{
                    Log::error("Payment Transaction id not found --->");
                    return false;
                }
            }else{
                Log::error('Failed Else');
            }
        } catch (\Throwable $th) {
            Log::error($th);
            Log::error('Razorpay --> Webhook Error Accured');

        }
    }
    public function stripe(Request $request)
    {
        // This is your test secret API key.
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

        // You can find your endpoint's secret in your webhook settings
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        // Verify webhook signature and extract the event.
        // See https://stripe.com/docs/webhooks/signatures for more information.
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            Log::error("Payload Mismatch");
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::error("Signature Verification Failed");
            http_response_code(400);
            exit();
        }


        // get the metadata
        $student_id = $event->data->object->metadata->student_id;
        $class_id = $event->data->object->metadata->class_id;
        $parent_id = $event->data->object->metadata->parent_id;
        $session_year_id = $event->data->object->metadata->session_year_id;
        $payment_transaction_id = $event->data->object->metadata->payment_transaction_id;
        $is_fully_paid = $event->data->object->metadata->is_fully_paid;
        $type_of_fee = $event->data->object->metadata->type_of_fee;
        $is_due_charges = $event->data->object->metadata->is_due_charges;
        $due_charges = $event->data->object->metadata->due_charges ?? null;
        $optional_paid_data = json_decode($event->data->object->metadata->optional_fees_paid) ?? null;
        $installment_paid_data = json_decode($event->data->object->metadata->installment_fees_paid) ?? null;

        //get the current today's date
        $current_date = Carbon::now()->format('Y-m-d');
        Log::error("event_type", [$event->type]);
        // handle the events
        switch ($event->type) {
            case 'payment_intent.succeeded':

                // update the values in transaction table local
                $transaction_db = PaymentTransaction::find($payment_transaction_id);
                if (!empty($transaction_db)) {
                    if ($transaction_db->status != 1) {

                        //get the total from transaction table local
                        $total_amount = $transaction_db->total_amount;

                        //udpate the values in transaction table local
                        $transaction_db->payment_status = 1;
                        $transaction_db->save();

                        // Add due charges of fully Paid Complusory Amount
                        if ($type_of_fee == 0 && $is_due_charges == 1) {
                            $add_due_charges = new FeesChoiceable();
                            $add_due_charges->student_id = $student_id;
                            $add_due_charges->class_id = $class_id;
                            $add_due_charges->is_due_charges = 1;
                            $add_due_charges->total_amount = $due_charges;
                            $add_due_charges->session_year_id = $session_year_id;
                            $add_due_charges->status= 1;
                            $add_due_charges->save();
                        }

                        if(isset($installment_paid_data) && !empty($installment_paid_data)){
                            Log::info("Paid Installment Fee Status Updated");
                            foreach($installment_paid_data as $row)
                            {
                                $db =  PaidInstallmentFee::find($row);
                                if(!empty($db))
                                {
                                    if($db->status != 1)
                                    {
                                        $db->status = 1;
                                        $db->save();
                                    }
                                    // Log::error("Installment status updated", ['id' => $db->id, 'status' => $db->status]);
                                }
                            }
                        }else{
                            Log::info('NO INSTALLMENT DATA');
                        }

                        if(isset($optional_paid_data) && !empty($optional_paid_data)){
                            Log::info("Optional Fees Status Updated");
                            foreach($optional_paid_data as $row)
                            {
                                $db =  FeesChoiceable::find($row);
                                if(!empty($db))
                                {
                                    if($db->status != 1)
                                    {
                                        $db->status = 1;
                                        $db->save();
                                    }
                                    // Log::error("FeesChoiceable status updated", ['id' => $db->id, 'status' => $db->status]);
                                }
                            }

                        }else{
                            Log::info('NO OPTIONAL DATA');
                        }

                        // add the data in fees paid table local
                        $update_fees_paid_query = FeesPaid::where(['student_id'=> $student_id, 'class_id' => $class_id , 'session_year_id' => $session_year_id]);
                        if($update_fees_paid_query->count()){
                            $update_fee_paid_data = FeesPaid::findOrFail($update_fees_paid_query->first()->id);
                            $update_fee_paid_data->total_amount = ($update_fees_paid_query->first()->total_amount + $total_amount);
                            $update_fee_paid_data->is_fully_paid = $is_fully_paid;
                            $update_fee_paid_data->save();
                        }else{
                            $fees_paid_db = new FeesPaid();
                            $fees_paid_db->parent_id = $parent_id;
                            $fees_paid_db->student_id = $student_id;
                            $fees_paid_db->class_id = $class_id;
                            $fees_paid_db->payment_transaction_id = $payment_transaction_id;
                            $fees_paid_db->total_amount = $total_amount;
                            $fees_paid_db->date = $current_date;
                            $fees_paid_db->session_year_id = $session_year_id;
                            $fees_paid_db->is_fully_paid = $is_fully_paid;
                            $fees_paid_db->save();
                        }

                        $user = Parents::where('id', $parent_id)->pluck('user_id');
                        $body = 'Amount :- ' . $total_amount;
                        $type = 'online';
                        $image = null;
                        $userinfo = null;

                        $notification = new Notification();
                        $notification->send_to = 2;
                        $notification->title = 'Payment Success';
                        $notification->message = $body;
                        $notification->type = $type;
                        $notification->date = Carbon::now();
                        $notification->is_custom = 0;
                        $notification->save();

                        foreach($user as $data)
                        {
                            $user_notification = new UserNotification();
                            $user_notification->notification_id = $notification->id;
                            $user_notification->user_id = $data;
                            $user_notification->save();
                        }


                        send_notification($user, 'Payment Success', $body, $type, $image, $userinfo);
                        http_response_code(200);
                        break;
                    } else {
                        Log::error("Transaction Already Successed --->");
                        break;
                    }
                } else {
                    Log::error("Payment Transaction id not found --->");
                    break;
                }

            case 'payment_intent.payment_failed':
                // update the data in transaction table local
                $transaction_db = PaymentTransaction::find($payment_transaction_id);
                if (!empty($transaction_db)) {
                    $total_amount = $transaction_db->total_amount;
                    $transaction_db->payment_status = 0;
                    $transaction_db->save();
                    http_response_code(400);

                    FeesChoiceable::where('payment_transaction_id',$payment_transaction_id)->where('status',0)->delete();
                    PaidInstallmentFee::where('payment_transaction_id',$payment_transaction_id)->where('status',0)->delete();

                    $user = Parents::where('id', $parent_id)->pluck('user_id');
                    $body = 'Amount :- ' . $total_amount;
                    $type = 'online';
                    $image = null;
                    $userinfo = null;

                    $notification = new Notification();
                    $notification->send_to = 2;
                    $notification->title = 'Payment Failed';
                    $notification->message = $body;
                    $notification->type = $type;
                    $notification->date = Carbon::now();
                    $notification->is_custom = 0;
                    $notification->save();
                    foreach($user as $data)
                    {
                        $user_notification = new UserNotification();
                        $user_notification->notification_id = $notification->id;
                        $user_notification->user_id = $data;
                        $user_notification->save();
                    }

                    send_notification($user, 'Payment Failed', $body, $type, $image, $userinfo);
                    break;
                } else {
                    Log::error("Payment Transaction id not found --->");
                    break;
                }

            default:
                Log::error($event->type);
                // Unexpected event type
                Log::error('Received unknown event type');
        }
    }

    public function paystack(Request $request)
    {
        try{
            $webhookBody = $request->getContent();
            $webhookBody = file_get_contents('php://input');

            $webhookSignature = $request->header('x-paystack-signature');
            $paystackSecretKey = env('PAYSTACK_SECRET_KEY');

            $expectedSignature = hash_hmac('sha512', $webhookBody, $paystackSecretKey);

            Log::error("Expected Signature --->" . $expectedSignature);
            Log::error("Header Signature --->" . $webhookSignature);

            // validate event do all at once to avoid timing attack
            if($webhookSignature == $expectedSignature)
            {
                Log::error("Signature Matched --->");
            }
            $current_date = Carbon::now()->format('Y-m-d');

            $event = json_decode($webhookBody);

            // Check if decoding was successful
            if ($event !== null && isset($event->data->metadata->payment_gateway_details)) {
                // Access properties on the decoded object
                $payload = $event->data->metadata->payment_gateway_details;
                $payment_transaction_id = $payload->payment_transaction_id;
                $optional_paid_data = $payload->optional_fees_id ?? [];
                $installment_paid_data = $payload->paid_installment_id ?? [];
            }

            $transaction_db = PaymentTransaction::find($payment_transaction_id);
            $student_id = $transaction_db->student_id;
            $parent_id = $transaction_db->parent_id;
            $class_id = $transaction_db->class_id;
            $session_year_id = $transaction_db->session_year_id;
            $is_fully_paid = $payload->is_fully_paid;
            $type_of_fee = $transaction_db->type_of_fee;
            $email = $payload->email;

            if($event && isset($event->event))
            {
                if($event->event === 'charge.success')
                {
                    if (!empty($transaction_db)) {
                        if ($transaction_db->status != 1) {

                            //get the total from transaction table local
                            $total_amount = $transaction_db->total_amount;

                            //udpate the values in transaction table local
                            $transaction_db->order_id = $event->data->id;
                            $transaction_db->payment_status = 1;
                            $transaction_db->save();
                            Log::info("Update Payment Transaction Table");
                            // Add due charges of fully Paid Complusory Amount
                            if ($type_of_fee == 0 && $is_due_charges == 1) {
                                $add_due_charges = new FeesChoiceable();
                                $add_due_charges->student_id = $student_id;
                                $add_due_charges->class_id = $class_id;
                                $add_due_charges->is_due_charges = 1;
                                $add_due_charges->total_amount = $due_charges;
                                $add_due_charges->session_year_id = $session_year_id;
                                $add_due_charges->status= 1;
                                $add_due_charges->save();
                            }

                            if(isset($installment_paid_data) && !empty($installment_paid_data)){
                                foreach($installment_paid_data as $row)
                                {
                                    $db =  PaidInstallmentFee::find($row);
                                    if(!empty($db))
                                    {
                                        if($db->status != 1)
                                        {
                                            $db->status = 1;
                                            $db->save();
                                            Log::info("Paid Installment Fee Status Updated");
                                        }
                                        Log::error("Installment status updated", ['id' => $db->id, 'status' => $db->status]);
                                    }
                                }
                            }else{
                                Log::info('NO INSTALLMENT DATA');
                            }

                            if(isset($optional_paid_data) && !empty($optional_paid_data)){
                                foreach($optional_paid_data as $row)
                                {
                                    $db =  FeesChoiceable::find($row);
                                    if(!empty($db))
                                    {
                                        if($db->status != 1)
                                        {
                                            $db->status = 1;
                                            $db->save();
                                            Log::info("Optional Fees Status Updated");
                                        }
                                        Log::error("FeesChoiceable status updated", ['id' => $db->id, 'status' => $db->status]);
                                    }
                                }

                            }else{
                                Log::info('NO OPTIONAL DATA');
                            }

                            // add the data in fees paid table local
                            $update_fees_paid_query = FeesPaid::where(['student_id'=> $student_id, 'class_id' => $class_id , 'session_year_id' => $session_year_id]);
                            if($update_fees_paid_query->count()){
                                $update_fee_paid_data = FeesPaid::findOrFail($update_fees_paid_query->first()->id);
                                $update_fee_paid_data->total_amount = ($update_fees_paid_query->first()->total_amount + $total_amount);
                                $update_fee_paid_data->is_fully_paid = $is_fully_paid;
                                $update_fee_paid_data->save();
                            }else{
                                $fees_paid_db = new FeesPaid();
                                $fees_paid_db->parent_id = $parent_id;
                                $fees_paid_db->student_id = $student_id;
                                $fees_paid_db->class_id = $class_id;
                                $fees_paid_db->payment_transaction_id = $payment_transaction_id;
                                $fees_paid_db->total_amount = $total_amount;
                                $fees_paid_db->date = $current_date;
                                $fees_paid_db->session_year_id = $session_year_id;
                                $fees_paid_db->is_fully_paid = $is_fully_paid;
                                $fees_paid_db->save();
                            }

                            $user = Parents::where('id', $parent_id)->pluck('user_id');
                            $body = 'Amount :- ' . $total_amount;
                            $type = 'online';
                            $image = null;
                            $userinfo = null;

                            $notification = new Notification();
                            $notification->send_to = 2;
                            $notification->title = 'Payment Success';
                            $notification->message = $body;
                            $notification->type = $type;
                            $notification->date = Carbon::now();
                            $notification->is_custom = 0;
                            $notification->save();

                            foreach($user as $data)
                            {
                                $user_notification = new UserNotification();
                                $user_notification->notification_id = $notification->id;
                                $user_notification->user_id = $data;
                                $user_notification->save();
                            }
                            send_notification($user, 'Payment Success', $body, $type, $image, $userinfo);
                            http_response_code(200);

                        } else {
                            Log::error("Transaction Already Successed --->");

                        }
                    } else {
                        Log::error("Payment Transaction id not found --->");

                    }
                }
            }

        }catch (\Exception $e) {
            // Handle exceptions if any
            Log::error("Error: " . $e->getMessage());
            Log::error('PayStack --> Webhook Error Accured');

        }


    }
}
