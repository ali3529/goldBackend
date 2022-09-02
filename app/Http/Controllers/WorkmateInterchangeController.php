<?php

namespace App\Http\Controllers;

use App\Models\purchase;
use App\Models\sale;
use App\Models\WorkmatePayment;
use App\Models\totalReceived;
use App\Models\Workmate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkmateInterchangeController extends Controller
{
    function newPurchase()
    {
        $data = request();
        $req = json_decode($data['form_data'], true);
        $workmate = Workmate::where('id', $req['workmate_id'])->first();
        $images = $this->saveImages();
        $req['document_pic'] = json_encode($images);


        $validator = $this->validatenewPurche($req);
        if ($validator->fails()) {
            return response()->json(['status' => '0',
                'errors' => $validator->errors()->all()], 200);
        }


        //save purches remainder in workmate


        if ($workmate->workmate_receive_g != 0) {
            $bigger = $req['amountG'] >= $workmate->workmate_receive_g;
            $reminder = $req['amountG'] > $workmate->workmate_receive_g ? $req['amountG'] - $workmate->workmate_receive_g : $workmate->workmate_receive_g - $req['amountG'];

            if ($bigger) {
                $workmate->workmate_payment_g += $reminder;
                $workmate->workmate_receive_g = 0;
            } else {
                $workmate->workmate_receive_g = $reminder;
            }
        } else {
            $workmate->workmate_payment_g += $req['amountG'];
        }

        //T
        if ($workmate->workmate_payment_t != 0) {
            $bigger = $req['amountT'] >= $workmate->workmate_payment_t;

            $reminder = $req['amountT'] > $workmate->workmate_payment_t ? $req['amountT'] - $workmate->workmate_payment_t : $workmate->workmate_payment_t - $req['amountT'];
//                return ['remainder'=>$reminder,'bigger'=>$bigger];
            if ($bigger) {
                $workmate->workmate_receive_t += $reminder;
                $workmate->workmate_payment_t = 0;
            } else {
                $workmate->workmate_payment_t = $reminder;
            }
        } else {
            $workmate->workmate_receive_t += $req['amountT'];
        }
        purchase::create($req);
        $workmate->update();

        return response()->json([
            'status' => '1',
        ], 200);

    }

    function newSale()
    {
        $data = request();
        $req = json_decode($data['form_data'], true);
        $workmate = Workmate::where('id', $req['workmate_id'])->first();
        $images = $this->saveImages();
        $req['document_pic'] = json_encode($images);
        $validator = $this->validatenewPurche($req);
        if ($validator->fails()) {
            return response()->json(['status' => '0',
                'errors' => $validator->errors()->all()], 200);
        }

        //save sales remainder in workmate


        //G
        if ($workmate->workmate_payment_g != 0) {
            $bigger = $req['amountG'] >= $workmate->workmate_payment_g;

            $reminder = $req['amountG'] > $workmate->workmate_payment_g ? $req['amountG'] - $workmate->workmate_payment_g : $workmate->workmate_payment_g - $req['amountG'];

            if ($bigger) {
                $workmate->workmate_receive_g += $reminder;
                $workmate->workmate_payment_g = 0;
            } else {
                $workmate->workmate_payment_g = $reminder;
            }
        } else {
            $workmate->workmate_receive_g += $req['amountG'];
        }

        //T

        if ($workmate->workmate_receive_t != 0) {
            $bigger = $req['amountT'] >= $workmate->workmate_receive_t;

            $reminder = $req['amountT'] > $workmate->workmate_receive_t ? $req['amountT'] - $workmate->workmate_receive_t : $workmate->workmate_receive_t - $req['amountT'];
//                return ['remainder'=>$reminder,'bigger'=>$bigger];
            if ($bigger) {
                $workmate->workmate_payment_t += $reminder;
                $workmate->workmate_receive_t = 0;
            } else {
                $workmate->workmate_receive_t = $reminder;
            }
        } else {
            $workmate->workmate_payment_t += $req['amountT'];
        }

        sale::create($req);
        $workmate->update();

        return response()->json([
            'status' => '1'
        ], 200);
    }

    function newWorkmatePayment()
    {
        $data = request();
        $req = json_decode($data['form_data'], true);
        $workmate = Workmate::where('id', $req['workmate_id'])->first();
        $images = $this->saveImages();
        $req['document_pic'] = json_encode($images);
        if ($req['payment_method'] == "G") {
            $validator = $this->validatenewPurche($req);
            if ($validator->fails()) {
                return response()->json(['status' => '0',
                    'errors' => $validator->errors()->all()], 200);
            }

            if ($workmate->workmate_receive_g != 0) {
                $bigger = $req['wieght'] >= $workmate->workmate_receive_g;

                $reminder = $req['wieght'] > $workmate->workmate_receive_g ? $req['wieght'] - $workmate->workmate_receive_g : $workmate->workmate_receive_g - $req['wieght'];

                if ($bigger) {
                    $workmate->workmate_payment_g += $reminder;
                    $workmate->workmate_receive_g = 0;
                } else {
                    $workmate->workmate_receive_g = $reminder;
                }
            } else {
                $workmate->workmate_payment_g += $req['wieght'];
            }

        } else {
            if ($workmate->workmate_receive_t != 0) {
                $bigger = $req['payment_amount'] >= $workmate->workmate_receive_t;

                $reminder = $req['payment_amount'] > $workmate->workmate_receive_t ? $req['payment_amount'] - $workmate->workmate_receive_t : $workmate->workmate_receive_t - $req['payment_amount'];
//                return ['remainder'=>$reminder,'bigger'=>$bigger];
                if ($bigger) {
                    $workmate->workmate_payment_t += $reminder;
                    $workmate->workmate_receive_t = 0;
                } else {
                    $workmate->workmate_receive_t = $reminder;
                }
            } else {
                $workmate->workmate_payment_t += $req['payment_amount'];
            }

        }
        WorkmatePayment::create($req);
        $workmate->update();
        //intergange


        return response()->json([
            'status' => '1',
            'massage' => 'پرداختی با موفقیت ثبت شد'
        ], 200);
    }

    function newWorkmateReceive()
    {
        $data = request();
        $req = json_decode($data['form_data'], true);

        $workmate = Workmate::where('id', $req['workmate_id'])->first();
        $images = $this->saveImages();
        $req['document_pic'] = json_encode($images);
        if ($req['payment_method'] == "G") {
            $validator = $this->validatenewPurche($req);
            if ($validator->fails()) {
                return response()->json(['status' => '0',
                    'errors' => $validator->errors()->all()], 200);
            }

            if ($workmate->workmate_payment_g != 0) {
                $bigger = $req['wieght'] >= $workmate->workmate_payment_g;

                $reminder = $req['wieght'] > $workmate->workmate_payment_g ? $req['wieght'] - $workmate->workmate_payment_g : $workmate->workmate_payment_g - $req['wieght'];

                if ($bigger) {
                    $workmate->workmate_receive_g += $reminder;
                    $workmate->workmate_payment_g = 0;
                } else {
                    $workmate->workmate_payment_g = $reminder;
                }
            } else {
                $workmate->workmate_receive_g += $req['wieght'];
            }


        } else {
            if ($workmate->workmate_payment_t != 0) {
                $bigger = $req['payment_amount'] >= $workmate->workmate_payment_t;

                $reminder = $req['payment_amount'] > $workmate->workmate_payment_t ? $req['payment_amount'] - $workmate->workmate_payment_t : $workmate->workmate_payment_t - $req['payment_amount'];
//                return ['remainder'=>$reminder,'bigger'=>$bigger];
                if ($bigger) {
                    $workmate->workmate_receive_t += $reminder;
                    $workmate->workmate_payment_t = 0;
                } else {
                    $workmate->workmate_payment_t = $reminder;
                }
            } else {
                $workmate->workmate_receive_t += $req['payment_amount'];
            }

        }
        totalReceived::create($req);
        $workmate->update();

        return response()->json([
            'status' => '1',
            'massage' => 'دریافتی با موفقیت ثبت شد'
        ], 200);
    }

    function getWorkmatePurchase(Request $request)
    {

        $data = purchase::where('workmate_id', $request->workmate_id)->get();
        foreach ($data as $da) {
            $da->document_pic = json_decode($da->document_pic);
        }
        return response()->json([
            'status' => "1",
            'Workmate_interchange' => $data,
            'Workmate_purchase'
        ], 200);

    }

    function getWorkmateSale(Request $request)
    {


        $data = sale::where('workmate_id', $request->workmate_id)->get();
        foreach ($data as $da) {
            $da->document_pic = json_decode($da->document_pic);
        }
        return response()->json([
            'status' => "1",
            'Workmate_interchange' => $data,
            'Workmate_sale'
        ], 200);

    }

    function getWorkmatePayment(Request $request)
    {

        $data = WorkmatePayment::where('workmate_id', $request->workmate_id)->get();
        foreach ($data as $da) {
            $da->document_pic = json_decode($da->document_pic);
        }
        return response()->json([
            'status' => "1",
            'Workmate_interchange' => $data,
            'Workmate_Payment'
        ], 200);

    }

    function getWorkmateReceive(Request $request)
    {

        $data = totalReceived::where('workmate_id', $request->workmate_id)->get();
        foreach ($data as $da) {
            $da->document_pic = json_decode($da->document_pic);
        }
        return response()->json([
            'status' => "1",
            'Workmate_interchange' => $data,
            'Workmate_Received'
        ], 200);

    }

    private function validatenewPurche($request)
    {
        $validator = Validator::make($request, [
            'date' => ['required',],
            'detail' => ['required',],
            'air' => ['required',],
            'orgin_wieght' => ['required',],
            'wieght' => ['required',],
            'workmate_id' => ['required',],

//            'email' => ['required', 'string', 'email', 'max:255'],
            //'national_code' => ['required', 'max:10', Rule::unique('customers', 'national_code')]
        ]);

        return $validator;
    }

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


//    function getWorkmateInterchange()
//    {
//        $data = request()->all();
//        $workmateId = $data['workmate_id'];
//        $purchase = purchase::where('workmate_id', $workmateId)->get();
//        $sale = sale::where('workmate_id', $workmateId)->get();
//
//        $payment = WorkmatePayment::where('workmate_id', $workmateId)->get();
//        $received = totalReceived::where('workmate_id', $workmateId)->get();
//
//
//        $all_workmate_purchase_g = 0;
//        $all_workmate_purchase_t = 0;
//
//        $all_workmate_sale_g = 0;
//        $all_workmate_sale_t = 0;
//
//        $all_workmate_payment_t = 0;
//        $all_workmate_payment_g = 0;
//
//        $all_workmate_receive_t = 0;
//        $all_workmate_receive_g = 0;
//
//        foreach ($purchase as $purch) {
//            $all_workmate_purchase_g += $purch->amountG;
//            $all_workmate_purchase_t += $purch->amountT;
//        }
//        foreach ($sale as $s) {
//            $all_workmate_sale_g += $s->amountG;
//            $all_workmate_sale_t += $s->amountT;
//        }
//
//        foreach ($payment as $pay) {
//            if ($pay->payment_method == "T") {
//                $all_workmate_payment_t += $pay->payment_amount;
//            } else {
//                $all_workmate_payment_g += $pay->wieght;
//            }
//
//        }
//        foreach ($received as $receiv) {
//            if ($receiv->payment_method == "T") {
//                $all_workmate_receive_t += $receiv->payment_amount;
//            } else {
//                $all_workmate_receive_g += $receiv->wieght;
//            }
//
//        }
//
//
//        return response()->json([
//            'status' => '1',
//            'purchase_g' => $all_workmate_purchase_g,
//            'purchase_t' => $all_workmate_purchase_t,
//
//            'sale_g' => $all_workmate_sale_g,
//            'sale_t' => $all_workmate_sale_t,
//
//            'payment_t' => $all_workmate_payment_t,
//            'payment_g' => $all_workmate_payment_g,
//
//            'receive_t' => $all_workmate_receive_t,
//            'receive_g' => $all_workmate_receive_g,
//
//        ], 200);
//
//
//    }
    //

    //old
//    function getWorkmateInterchange()
//    {
//        $data = request()->all();
//        $workmateId = $data['workmate_id'];
//        $work = Workmate::where('id', $workmateId)->first();
////        G
//        $sales = $work->workmate_sales;
//        $purchases = $work->workmate_purchases;
////        T
//        $salesT = $work->workmate_sales_t;
//        $purchasesT = $work->workmate_purchases_t;
//
//
//        return response()->json([
//            'status' => '1',
//            'sales' => $sales,
//            'purchases' => $purchases,
//            'sales_t' => $salesT,
//            'purchases_t' => $purchasesT,
//        ], 200);
//
//    }

    function getWorkmateInterchange()
    {
        $data = request()->all();
        $workmateId = $data['workmate_id'];
        $work = Workmate::where('id', $workmateId)->first();
//        G
        $sales = $work->workmate_sales;
        $purchases = $work->workmate_purchases;
        $receive = $work->workmate_receive_g;
        $payment = $work->workmate_payment_g;
//        T
        $salesT = $work->workmate_sales_t;
        $purchasesT = $work->workmate_purchases_t;
        $receiveT = $work->workmate_receive_t;
        $paymentT = $work->workmate_payment_t;


        return response()->json([
            'status' => '1',
            'sales' => $sales,
            'purchases' => $purchases,
            'sales_t' => $salesT,
            'purchases_t' => $purchasesT,

            '$receive' => $receive,
            '$payment' => $payment,
            '$receive_t' => $receiveT,
            '$payment_t' => $paymentT,


        ], 200);

    }

    function getAllWorkmateInterchange(Request $request)
    {
        $data = request()->all();
        $sale = sale::where('workmate_id', $request->workmate_id)->get();
        $purchase = purchase::where('workmate_id', $request->workmate_id)->get();
        $WorkmatePayment = WorkmatePayment::where('workmate_id', $request->workmate_id)->get();
        $totalReceived = totalReceived::where('workmate_id', $request->workmate_id)->get();

        $merge1 = $sale->concat($purchase);
        $merge2 = $merge1->concat($WorkmatePayment);
        $All_merged_data = $merge2->concat($totalReceived);

        if ($data["from_date"] != '' && $data['to_date'] != '') {
            $from = date($data['from_date']);
            $to = date($data['to_date']);


            return $All_merged_data->whereBetween('date_m', [$from, $to])->isEmpty() ? response()->json([
                'status' => '0',
                'message' => 'گزارش در این ناریخ ثبت نشده است'
            ]) : response()->json([
                'status' => '1',
                'Workmate_interchange' => $All_merged_data->sortBy('date_m')->sortByDesc('Status')->whereBetween('date_m', [$from, $to])->values(),
//                'Workmate_interchange' => $All_merged_data->sortBy('created_at')->sortByDesc('Status')->whereBetween('date_m', [$from, $to])->values(),

            ]);

        } else {

            return [
                'status' => '2',
//                'Workmate_interchange' => $All_merged_data->sortBy('created_at')->sortByDesc('Status')->values(),
                'Workmate_interchange' => $All_merged_data->sortBy('created_at')->sortByDesc('Status')->values(),

            ];

        }


//        $sale = sale::where('workmate_id', $request->workmate_id)->orderBy('created_at', 'DESC')->whereBetween('date_m', [$from, $to])->get();

//        $purchase = purchase::where('workmate_id', $request->workmate_id)->orderBy('created_at', 'DESC')->whereBetween('date_m', [$from, $to])->get();

//        $WorkmatePayment = WorkmatePayment::where('workmate_id', $request->workmate_id)->orderBy('created_at', 'DESC')->whereBetween('date_m', [$from, $to])->get();

//        $totalReceived = totalReceived::where('workmate_id', $request->workmate_id)->orderBy('created_at', 'DESC')->whereBetween('date_m', [$from, $to])->get();



    }
}
