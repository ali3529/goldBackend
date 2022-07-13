<?php

namespace App\Http\Controllers;

use App\Models\purchase;
use App\Models\sale;
use App\Models\WorkmatePayment;
use App\Models\totalReceived;
use App\Models\Workmate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkmateInterchangeController extends Controller
{
    function newPurchase()
    {
        $data = request();
        $req = json_decode($data['form_data'], true);

        $images = $this->saveImages();
        $req['document_pic'] = json_encode($images);

        $validator = $this->validatenewPurche($req);
        if ($validator->fails()) {
            return response()->json(['status' => '0',
                'errors' => $validator->errors()->all()], 200);
        }

        purchase::create($req);
        //save purches remainder in workmate
        $work = Workmate::where('id', $req['workmate_id'])->first();
        if ($req['p_method'] == "G") {
            $purches = $work->workmate_purchases;
            $sales = $work->workmate_sales;
            $purches += $req['amountG'];
            //save intergange G
            if ($sales != 0) {
                $remainde = $purches - $sales;
                if ($purches < $sales) {
                    $work->update(array('workmate_sales' => abs($remainde)));
                } else {

                    $work->update(array('workmate_purchases' => $remainde));
                    $work->update(array('workmate_sales' => 0));
                }

            } else {
                $work->update(array('workmate_purchases' => $purches));
            }
        } else {
            $purches = $work->workmate_purchases_t;
            $sales = $work->workmate_sales_t;
            $purches += $req['amountT'];
            //save intergange T
            if ($sales != 0) {
                $remainde = $purches - $sales;
                if ($purches < $sales) {
                    $work->update(array('workmate_sales_t' => abs($remainde)));
                } else {

                    $work->update(array('workmate_purchases_t' => $remainde));
                    $work->update(array('workmate_sales_t' => 0));
                }

            } else {
                $work->update(array('workmate_purchases_t' => $purches));
            }
        }


        return response()->json([
            'status' => '1',
        ], 200);

    }

    function newSale()
    {
        $data = request();
        $req = json_decode($data['form_data'], true);

        $images = $this->saveImages();
        $req['document_pic'] = json_encode($images);
        $validator = $this->validatenewPurche($req);
        if ($validator->fails()) {
            return response()->json(['status' => '0',
                'errors' => $validator->errors()->all()], 200);
        }
        sale::create($req);
        //save sales remainder in workmate
        $work = Workmate::where('id', $req['workmate_id'])->first();

        if ($req['p_method'] == "G") {
            $sales = $work->workmate_sales;
            $purches = $work->workmate_purchases;
            $sales += $req['amountG'];

            //save intergange G
            if ($purches != 0) {
                $remainde = $sales - $purches;
                if ($sales < $purches) {
                    $work->update(array('workmate_purchases' => abs($remainde)));
                } else {
                    $work->update(array('workmate_purchases' => 0));
                    $work->update(array('workmate_sales' => $remainde));
                }

            } else {
                $work->update(array('workmate_sales' => $sales));
            }
        } else {
            $sales = $work->workmate_sales_t;
            $purches = $work->workmate_purchases_t;
            $sales += $req['amountT'];

            //save intergange T
            if ($purches != 0) {
                $remainde = $sales - $purches;
                if ($sales < $purches) {
                    $work->update(array('workmate_purchases_t' => abs($remainde)));
                } else {
                    $work->update(array('workmate_purchases_t' => 0));
                    $work->update(array('workmate_sales_t' => $remainde));
                }

            } else {
                $work->update(array('workmate_sales_t' => $sales));
            }
        }

        return response()->json([
            'status' => '1'
        ], 200);
    }

    function newWorkmatePayment()
    {
        $data = request();
        $req = json_decode($data['form_data'], true);

        $images = $this->saveImages();
        $req['document_pic'] = json_encode($images);
        if ($data['payment_method'] == "G") {
            $validator = $this->validatenewPurche($req);
            if ($validator->fails()) {
                return response()->json(['status' => '0',
                    'errors' => $validator->errors()->all()], 200);
            }
        }
        WorkmatePayment::create($req);
        //intergange

        $work = Workmate::where('id', $req['workmate_id'])->first();

        if ($req['payment_method'] == "T") {
            if ($work->workmate_purchases_t != 0) {
                $payment_amount = $req['payment_amount'];
                $purchases_t = $work->workmate_purchases_t;
                $remainder = $purchases_t - $payment_amount;
                $work->update(array('workmate_purchases_t' => abs($remainder)));
            } else {
                return response()->json([
                    'status' => '2',
                    'massage' => 'مقدار بدهی صفر میباشد'
                ], 200);
            }

        } else {
            if ($work->workmate_purchases != 0) {
                $wieght = $req['wieght'];
                $purchases_g = $work->workmate_purchases;
                $remainder = $purchases_g - $wieght;
                $work->update(array('workmate_purchases' => abs($remainder)));
            } else {
                return response()->json([
                    'status' => '2',
                    'massage' => 'مقدار بدهی صفر میباشد'
                ], 200);
            }
        }
        return response()->json([
            'status' => '1'
        ], 200);
    }

    function newWorkmateReceive()
    {
        $data = request();
        $req = json_decode($data['form_data'], true);

        $images = $this->saveImages();
        $req['document_pic'] = json_encode($images);
        if ($data['payment_method'] == "G") {
            $validator = $this->validatenewPurche($req);
            if ($validator->fails()) {
                return response()->json(['status' => '0',
                    'errors' => $validator->errors()->all()], 200);
            }
        }
        totalReceived::create($req);
        //intergange

        $work = Workmate::where('id', $req['workmate_id'])->first();

        if ($req['payment_method'] == "T") {
            if ($work->workmate_sales_t != 0) {
                $payment_amount = $req['payment_amount'];
                $sales_t = $work->workmate_sales_t;
                $remainder = $sales_t - $payment_amount;
                $work->update(array('workmate_sales_t' => abs($remainder)));
            } else {
                return response()->json([
                    'status' => '2',
                    'massage' => 'مقدار بدهی صفر میباشد'
                ], 200);
            }

        } else {
            if ($work->workmate_sales != 0) {
                $wieght = $req['wieght'];
                $sales_g = $work->workmate_purchases;
                $remainder = $sales_g - $wieght;
                $work->update(array('workmate_sales' => abs($remainder)));
            } else {
                return response()->json([
                    'status' => '2',
                    'massage' => 'مقدار بدهی صفر میباشد'
                ], 200);
            }
        }
        return response()->json([
            'status' => '1'
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

    function getWorkmateInterchange()
    {
        $data = request()->all();
        $workmateId = $data['workmate_id'];
        $work = Workmate::where('id', $workmateId)->first();
//        G
        $sales = $work->workmate_sales;
        $purchases = $work->workmate_purchases;
//        T
        $salesT = $work->workmate_sales_t;
        $purchasesT = $work->workmate_purchases_t;


        return response()->json([
            'status' => '1',
            'sales' => $sales,
            'purchases' => $purchases,
            'sales_t' => $salesT,
            'purchases_t' => $purchasesT,
        ], 200);

    }
}
