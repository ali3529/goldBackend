<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\payment;
use App\Models\Instalment;
use App\Helper\Helper;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Hash;

class PaymentController extends Controller
{
    function newPayment()
    {
        $data = request();
        $req = json_decode($data['form_data'], true);
        $images = $this->saveImages();

        $req['document_pic'] = json_encode($images);
        $paymentData = payment::create($req);

        $customerInstelments = Instalment::where('customer_id', $req['customerId'])->orderBy('created_at', 'asc')->get();;
        // $instelment = $customerInstelments[0];
        $remind = 0;
        $reminder_type = '';

        //for toman
        $payG = $req['payment_g'] * $req['day_rate'];
        $allPay = $payG + $req['payment_t'];
        //fore Geram
        $payT = $req['payment_t'] / $req['day_rate'];
        $allPayT = $payT + $req['payment_g'];

//        $ff=0;
//        foreach ($customerInstelments as $instelment) {
//           $ff +=$instelment->reminder;
//        }
//        if ($ff==0){
//            return response()->json([
//                'status' => '0',
//                'massage' => 'jjj'
//            ], 200);
//        }

        $this->addCashReport("پرداخت قسط", $req['customer_name'], $req['customerId'], "8", $req['payer_name'], $allPay, null, $req['day_rate']);

        foreach ($customerInstelments as $instelment) {
            if ($instelment->paymentMethod == "T") {
                if ($instelment->remainder == 0) {

                } else {
                    if ($remind > 0) {
                        if ($reminder_type == "G") {
                            $remind = $remind * $req['day_rate'];
                            $allPay = $remind;
                        }
                        $re = $instelment->remainder - $remind;
                        Instalment::where('id', $instelment->id)->update(array("remainder" => $re));
                    }
                    if ($instelment->remainder < $allPay) {
                        $remind = $allPay - $instelment->remainder;
                        $f = $allPay - $remind;
                        $reeee = $instelment->remainder - $f;
                        $allPay = $remind;
                        $reminder_type = "T";
                        Instalment::where('id', $instelment->id)->update(array("remainder" => $reeee));
                    } else {

                        $re = $instelment->remainder - $allPay;
                        Instalment::where('id', $instelment->id)->update(array("remainder" => $re));
                        return response()->json([
                            'status' => '1',
                            'instelment' => $instelment
                        ], 200);
                    }
                }


            } else if ($instelment->paymentMethod == "G") {
                if ($instelment->remainder == 0) {

                } else {
                    if ($remind > 0) {
                        if ($reminder_type == "T") {
                            $remind = $remind / $req['day_rate'];
                            $allPayT = $remind;
                        }

                        $re = $instelment->remainder - $remind;

                        Instalment::where('id', $instelment->id)->update(array("remainder" => $re));
                        //}

                    }

                    if ($instelment->remainder < $allPayT) {

                        $remind = $allPayT - $instelment->remainder;

                        $f = $allPayT - $remind;
                        $reeee = $instelment->remainder - $f;

                        $allPayT = $remind;
                        $reminder_type = "G";
                        Instalment::where('id', $instelment->id)->update(array("remainder" => $reeee));
                    } else {

                        $re = $instelment->remainder - $allPayT;
                        Instalment::where('id', $instelment->id)->update(array("remainder" => $re));
                        return response()->json([
                            'status' => '1',
                            'instelment' => $instelment
                        ], 200);
                    }
                }

            }


        }
        //here
        $this->liquedet($customerInstelments);
        return response()->json([
            'status' => '1',
            'instelment' => $instelment
        ], 200);

    }

    function liquedet($customerInstelments)
    {
//        $req = request()->all();
//        $customerInstelments = Instalment::where('customer_id', $req['customer_id'])->orderBy('created_at', 'asc')->get();
        foreach ($customerInstelments as $instelment) {

            if ($instelment->remainder <= 0.001) {
                Instalment::where('id', $instelment->id)->update(array("remainder" => 0));
            }
        }


//        return response()->json([
//            'status' => '1',
//           // 'instelment' =>  $ddd<=0.001?"ff":"bb"
//            //'instelment' =>  $ddd
//        ], 200);

    }
//    function saveImages($request)
//    {
//        if ($request->hasfile('document_pic')) {
//            $images = $request->file('document_pic');
//            $image_urls = array();
//
//            foreach ($images as $image) {
//                $random_name = mt_rand(1000000000, 9999999999);
//                $destination = '../public_html/uploads';
//                $img_name = $random_name . "." . $image->extension();
//                $image->move($destination, $img_name);
//                $url = "https://azarsai.ir/uploads/" . $img_name;
//                $image_urls[] = $url;
//            }
//            return $image_urls;
//        }else return [];
//
//
//
//        //payment::where('id',$paymentData->id)->update(array('document_pic'=>$image_urls));
//        //dd($image_urls);
//    }
    function saveImages()

    {

        $request = request();

        if ($request->hasfile('document_pic')) {
            $images = $request->file('document_pic');
            $image_urls = array();

            foreach ($images as $image) {
                $random_name = mt_rand(1000000000, 9999999999);
                $destination = '../uploads';
                $img_name = $random_name . "." . $image->extension();
                $image->move($destination, $img_name);
                $url = "https://zarirangold.com/uploads/" . $img_name;
                $image_urls[] = $url;
            }

            return $image_urls;
        } else return [];


        //payment::where('id',$paymentData->id)->update(array('document_pic'=>$image_urls));
        //dd($image_urls);
    }

    function getCustomerPayment()
    {
        $req = request()->all();
        $data = payment::where('customerId', $req['customer_id'])->get();
        foreach ($data as $da) {
            $da->document_pic = json_decode($da->document_pic);
        }

        return response()->json([
            'status' => '1',
            'payments' => $data,

        ], 200);

    }

    function editPayment()
    {
        $req = request();
        $data = json_decode($req['form_data'], true);
        if ($this->checkPassword($data['password'])) {
            return $this->handleEditPayment($data['customerId'], $data['payment_g'], $data['payment_t'], $data['day_rate'],
                $data['payment_id'], $data['payer_name'], $data['customer_name']);
        } else {
            return [
                'status' => '2',
                'massage' => 'پسورد اشتباه است'
            ];
        }
    }

    //

    function handleEditPayment($customerId, $payment_g, $payment_t, $day_rate, $payment_id, $payer_name, $customer_name)
    {
        $paymentData = payment::where('id', $payment_id)->first();
//        get remainder
        $newPaymentamount = $payment_t + ($payment_g * $day_rate);
        $remainder_new_old = $newPaymentamount - ($paymentData->payment_t + ($paymentData->payment_g * $paymentData->day_rate));

        //report
        $this->addCashReport("ویرایش پرداختی", $customer_name, $customerId,
            "9", $payer_name, ($paymentData->payment_t + ($paymentData->payment_g * $paymentData->day_rate))
            , $newPaymentamount, $day_rate);
        //report

//        set new pays
        $paymentData->payment_t = $payment_t;
        $paymentData->payment_g = $payment_g;
        $paymentData->day_rate = $day_rate;
        $paymentData->update();

        $customerInstelments = Instalment::where('customer_id', $customerId)->orderBy('created_at', 'asc')->get();

        $remind = 0;
        $reminder_type = '';

        $remind_M = null;
        $reminder_type_M = '';


        //for toman
        $allPay = $remainder_new_old;
        //fore Geram
        $allPayT = $remainder_new_old / $day_rate;

        foreach ($customerInstelments as $instelment) {

//            $remind_M!=null?$Pay=$instelment->paymentMethod == "G" && $reminder_type_M!="G"?$Pay=$remind_M/$day_rate:$Pay=$remind_M:
//                $Pay=$instelment->paymentMethod == "G"?
//                $remainder_new_old / $day_rate:$remainder_new_old;

            $remind_M != null ? $Pay = $instelment->paymentMethod == "G" && $reminder_type_M != "G" ?
                $Pay = $remind_M / $day_rate : $Pay = $instelment->paymentMethod == "T" && $reminder_type_M != "T" ?
                    $Pay = $remind_M * $day_rate : $Pay = $remind_M
                : $Pay = $instelment->paymentMethod == "G" ?
                $remainder_new_old / $day_rate : $remainder_new_old;


            if ($Pay < 0) {

                $initAmount = $instelment->paymentMethod == "G" ? $instelment->initial_payment / $instelment->day_rate : $instelment->initial_payment;
                $amount_con = $instelment->paymentMethod == "G" ? $instelment->amount / $instelment->day_rate : $instelment->amount;
                $remianderWithoutInitPay = ($amount_con - $initAmount) - $instelment->remainder;

                if ($remianderWithoutInitPay < abs($Pay)) {

                    $remind_M = $remianderWithoutInitPay + $Pay;
                    $bbbbb = $remianderWithoutInitPay % $Pay;


                    Instalment::where('id', $instelment->id)->update(array("remainder" => $bbbbb + $instelment->remainder));;
                    if ($remind_M > 0) {
                        return response()->json([
                            'status' => '1',
                            'message' => 'پرداخت با موفقیت ویرایش شد ',
                            'state' => 'zero inn if'
                        ]);
                    }

                } else {

                    Instalment::where('id', $instelment->id)->update(array("remainder" => $instelment->remainder - $Pay));
                    $remind_M = 0;
                    return response()->json([
                        'status' => '1',
                        'message' => 'پرداخت با موفقیت ویرایش شد ',
                        'state' => 'zero inn else'
                    ]);
                }
                $reminder_type_M = $instelment->paymentMethod;

            } //          minez


            else {
                if ($instelment->paymentMethod == "T") {
                    if ($instelment->remainder == 0) {

                    } else {
                        if ($remind > 0) {
                            if ($reminder_type == "G") {
                                $remind = $remind * $day_rate;
                                $allPay = $remind;
                            }
                            $re = $instelment->remainder - $remind;
                            Instalment::where('id', $instelment->id)->update(array("remainder" => $re));
                        }
                        if ($instelment->remainder < $allPay) {
                            $remind = $allPay - $instelment->remainder;
                            $f = $allPay - $remind;
                            $reeee = $instelment->remainder - $f;
                            $allPay = $remind;
                            $reminder_type = "T";
                            Instalment::where('id', $instelment->id)->update(array("remainder" => $reeee));
                        } else {

                            $re = $instelment->remainder - $allPay;
                            Instalment::where('id', $instelment->id)->update(array("remainder" => $re));
                            return response()->json([
                                'status' => '1',
                                'message' => 'پرداخت با موفقیت ویرایش شد ',
                                'state' => 'else'
                            ]);
                        }
                    }


                } else if ($instelment->paymentMethod == "G") {
                    if ($instelment->remainder == 0) {

                    } else {
                        if ($remind > 0) {
                            if ($reminder_type == "T") {
                                $remind = $remind / $day_rate;
                                $allPayT = $remind;
                            }

                            $re = $instelment->remainder - $remind;

                            Instalment::where('id', $instelment->id)->update(array("remainder" => $re));
                            //}

                        }

                        if ($instelment->remainder < $allPayT) {

                            $remind = $allPayT - $instelment->remainder;

                            $f = $allPayT - $remind;
                            $reeee = $instelment->remainder - $f;

                            $allPayT = $remind;
                            $reminder_type = "G";
                            Instalment::where('id', $instelment->id)->update(array("remainder" => $reeee));
                        } else {

                            $re = $instelment->remainder - $allPayT;
                            Instalment::where('id', $instelment->id)->update(array("remainder" => $re));
                            return response()->json([
                                'status' => '1',
                                'message' => 'پرداخت با موفقیت ویرایش شد ',
                                'state' => 'elsee'
                            ]);
                        }
                    }

                }
            }


        }

        return $customerInstelments;
    }

    function deletePayment()
    {
        $data = request()->all();

//        if ($this->checkPassword($data['password'])) {
            $payment_id = $data['payment_id'];
            $paymentData = payment::where('id', $payment_id)->first();
//        $this->saveReport("حذف پرداختی",'',$paymentData->customerId,"10");

            //report
            $this->addCashReport("حذف پرداختی", $paymentData->customer_name, $paymentData->customerId,
                "10", $paymentData->payer_name, ($paymentData->payment_t + ($paymentData->payment_g * $paymentData->day_rate))
                , '', $paymentData->day_rate);

            $customerInstelments = Instalment::where('customer_id', $paymentData->customerId)->orderBy('created_at', 'asc')->get();

            $remainder_new_old = ($paymentData->payment_t + ($paymentData->payment_g * $paymentData->day_rate));

            $remind_M = null;
            $reminder_type_M = '';


            foreach ($customerInstelments as $instelment) {


                $remind_M != null ? $Pay = $instelment->paymentMethod == "G" && $reminder_type_M != "G" ?
                    $Pay = $remind_M / $paymentData->day_rate : $Pay = $instelment->paymentMethod == "T" && $reminder_type_M != "T" ?
                        $Pay = $remind_M * $paymentData->day_rate : $Pay = $remind_M
                    : $Pay = $instelment->paymentMethod == "G" ?
                    ($remainder_new_old / $paymentData->day_rate * -1) : $remainder_new_old * -1;
//                ($remainder_new_old / $paymentData->day_rate ) : $remainder_new_old ;


                $initAmount = $instelment->paymentMethod == "G" ? $instelment->initial_payment / $instelment->day_rate : $instelment->initial_payment;
                $amount_con = $instelment->paymentMethod == "G" ? $instelment->amount / $instelment->day_rate : $instelment->amount;
                $remianderWithoutInitPay = ($amount_con - $initAmount) - $instelment->remainder;

                if ($remianderWithoutInitPay < abs($Pay)) {

                    $remind_M = $remianderWithoutInitPay + $Pay;

                    $bbbbb = $remianderWithoutInitPay % $Pay;

                    Instalment::where('id', $instelment->id)->update(array("remainder" => $bbbbb + $instelment->remainder));;
                    if ($remind_M > 0) {
                        $paymentData->delete();
                        return response()->json([
                            'status' => '1',
                            'message' => 'پرداخت با موفقیت حذف شد ',
                            'state' => 'zero inn if'
                        ]);
                    }

                } else {

                    Instalment::where('id', $instelment->id)->update(array("remainder" => $instelment->remainder - $Pay));
                    $remind_M = 0;
                    $paymentData->delete();
                    return response()->json([
                        'status' => '1',
                        'message' => 'پرداخت با موفقیت حذف شد ',
                        'state' => 'zero inn else'
                    ]);
                }
                $reminder_type_M = $instelment->paymentMethod;

            }

            return $paymentData;
//        }else{
//            return [
//                'status' => '2',
//                'massage' => 'پسورد اشتباه است'
//            ];
//        }
    }

    function saveReport($operation, $customer, $id, $status)
    {
        $rp = new ReportController();
        $rp->addReport($operation, $customer, $id, $status);
    }

    function addCashReport($operation, $customer, $id, $status, $payer_name, $pay, $edited_pay, $day_rate)
    {
        $rp = new ReportController();
        $rp->addCashReport($operation, $customer, $id, $status, $payer_name, $pay, $edited_pay, $day_rate);
    }


    function checkPassword($password)
    {
        $user = User::all()->first();
        $is_tass_true = Hash::check($password, $user->password);
        return $is_tass_true;
    }
}
