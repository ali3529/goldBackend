<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Models\Instalment;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Controllers\ReportController;

class InstalmentController extends Controller
{
    function addInstalment()
    {

        $req = request();
        $data = json_decode($req['form_data'], true);

        $images = $this->saveImages();
        $validator = $this->validateCreateInstalment($data);
        $data['document_pic'] = json_encode($images);

        if ($validator->fails()) {
            return response()->json(['status' => '0',
                'errors' => $validator->errors()->all()], 200);
        }
        if ($data['paymentMethod'] == "T") {
            $data['remainder'] = $data['amount'] - $data['initial_payment'];
        } elseif ($data['paymentMethod'] == "G") {
            $data['remainder'] = ($data['amount'] - $data['initial_payment']) / $data['day_rate'];
        }


        $result = Instalment::create($data);

//        $this->saveReport("افزودن قسط جدید",'',$data['customer_id'],"5");
        $this->addReportInstelment("افزودن قسط جدید", $data['customer_name'], $data['customer_id'], "5",
            $data['initial_payment'], $data['day_rate'], $data['amount']);
        return response()->json([
            'status' => '1'
        ], 200);
    }

    function getInstalment()
    {
        $search = request('search');

        $data = Instalment::join('customers', 'customers.id', "=", 'instalments.customer_id')
            ->select('customers.*')
            ->where('fullname', 'LIKE', '%' . $search . '%')->orWhere('national_code', 'LIKE', '%' . $search . '%')
            ->get();

        return $data;

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

    function getCustomerInstalment()
    {
        $req = request()->all();
//       $search = request('search');
//        if ($search = request('search')) {
//            $customers = Instalment::where('recep_id', 'LIKE', '%' . $search . '%')->get();
//            return $customers;
//        }
        $data = Instalment::where('customer_id', $req['customer_id'])->get();
        $amountT = 0;
        $amountG = 0;
        foreach ($data as $p) {
//            $am=$p->amount-$p->initial_payment;
//            $amountT+=$am;
            if ($p->paymentMethod == "G") {
                $amountG += $p->remainder;
            } else {
                $amountT += $p->remainder;
            }
//            $amountG+=$am/$p->day_rate;

        }
        foreach ($data as $da) {
            $da->document_pic = json_decode($da->document_pic);
        }

        return response()->json([
            'status' => '1',
            'customer_instalments' => $data,
            'amountT' => $amountT,
            'amountG' => $amountG
        ], 200);
        //  return $data;

    }

    private function validateCreateInstalment($request)
    {
        $validator = Validator::make($request, [
            'recep_id' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'int', 'max:255'],
//            'email' => ['required', 'string', 'email', 'max:255'],
            //'national_code' => ['required', 'max:10', Rule::unique('customers', 'national_code')]
        ]);

        return $validator;
    }

    function getInstalmentRemiander()
    {
        $req = request()->all();
        $date = $req['date'];
        $data = Instalment::all();
        $instalmentRemainder = [];
        $customer = Customer::all();
        foreach ($data as $instalment) {
            if ($date > $instalment->max_date) {
                $name = '';
                $phone = '';
                if ($instalment->remainder >= 0.001) {
                    foreach ($customer as $cus) {
                        if ($cus->id == $instalment->customer_id) {
                            $name = $cus->fullname;
                            $phone = $cus->phone_number;
                        }
                    }
                    $instalmentRemainder[] = array("name" => $name, "phone" => $phone, "instell" => $instalment);;


                }

            }
        }
        return
            $instalmentRemainder;


    }

    function sendRemainderSms()
    {
        $apiKey = 'ZMinIFkBD3c2QnUJjbzcHfButSnUXNMJbEdJXXXVsxY=';
        $client = new \IPPanel\Client($apiKey);
        $req = request()->all();

        $name = $req['name'];
        $phone = $req['phone'];

        $patternValues = [
            "name" => $name,
        ];

        $bulkID = $client->sendPattern(
            "6d01epculxdv57n",    // pattern code
            "+98100020400",      // originator
            $phone,  // recipient
            $patternValues  // pattern values
        );

        return [
            "status" => '1',
            "massage" => "پیام با موفقیت ارسال شد"
        ];
    }

    function editInstelment()
    {
        $req = request();
        $data = json_decode($req['form_data'], true);

        if ($this->checkPassword($data['password'])) {
            //images
            $images = $this->saveImages();
            if ($data['old_document_pic']!='empty')
            foreach ($data['old_document_pic'] as $img){
                $images[]=$img;
            }

            $data['document_pic'] = json_encode($images);


            $instelment = Instalment::where('id', $data['instelment_id'])->first();
            $customer = Customer::where('id', $data['customer_id'])->first();


            //report
            $this->addReportInstelmentEdite("ویرایش قسط ", $customer->fullname, $customer->id, "6",
                $instelment->initial_payment, $data['initial_payment'], $instelment->day_rate,
                $data['day_rate'], $instelment->amount, $data['amount']);

//report


            if ($instelment->remainder == 0) {
                return [
                    "status" => '0',
                    "massage" => "امکان ویرایش قسط تسویه شده وجود ندارد"
                ];
            } else {
                $payments = "0";
//        convert amount
                $amount = $instelment->paymentMethod == "G" ? $data['amount'] / $data['day_rate'] : $data['amount'];
                $amount_old = $instelment->paymentMethod == "G" ? $instelment->amount / $data['day_rate'] : $instelment->amount;

//        get payments
                $init = $instelment->paymentMethod == "G" ? $instelment->initial_payment / $data['day_rate']
                    : $instelment->initial_payment;


                if ($instelment->remainder != "0") {
                    $payments = $amount_old - $instelment->remainder;
                }


//        set amount
                $instelment->amount = $data['amount'];

                if ($amount < $payments) {
                    $creditor = $payments - $amount;
                    $instelment->remainder = 0;


                    if ($instelment->paymentMethod == "G") {
                        $customer->wallet_g += $creditor;
                    } else {
                        $customer->wallet_t += $creditor;
                    }
                    $customer->update();
                } else {

//        set new reminder
                    if ($instelment->remainder != "0") {
//                    return $data['initial_payment']-$instelment->initial_payment;

                        if ($data['initial_payment'] == $instelment->initial_payment) {
                            $instelment->remainder = strval($amount - $payments);
                        } else {
                            $init_convert = $instelment->paymentMethod == 'G' ?
                                $instelment->initial_payment / $instelment->day_rate : $instelment->initial_payment;

//                        return ['dd'=>($instelment->paymentMethod=='G'?$data['initial_payment']/$instelment->day_rate:$data['initial_payment']-$init_convert )> $instelment->remainder?true:false];
//                        return $data['initial_payment']-$instelment->initial_payment ;


                            if (($instelment->paymentMethod == 'G' ? $data['initial_payment'] / $instelment->day_rate : $data['initial_payment'] - $init_convert) > $instelment->remainder) {
                            } else {

                                $instelment->remainder = strval($amount - $payments);
                                $instelment->remainder = $instelment->remainder - ($instelment->paymentMethod == 'G' ?
                                        (($data['initial_payment'] - $instelment->initial_payment) / $instelment->day_rate) : ($data['initial_payment'] - $instelment->initial_payment));
                            }

                        }

                    } else {
                        $instelment->remainder = strval($amount - $init);
                    }


//            return $payments;
                }


//       updates

                $instelment->recep_id = $data['recep_id'];
                $instelment->date = $data['date'];
                $instelment->max_date = $data['max_date'];
                $instelment->warranty = $data['warranty'];
                $instelment->monthly_profit = $data['monthly_profit'];
                $instelment->day_rate = $data['day_rate'];

                $instelment->initial_payment_gram = $data['initial_payment_gram'];
                $instelment->initial_payment_t = $data['initial_payment_t'];
                $instelment->initial_payment = $data['initial_payment'];
                $instelment->document_pic = $data['document_pic'];
                $instelment->update();

                return [
                    "status" => '1',
                    "massage" => "قسط جدید با موفقیت ویرایش شد"
                ];
            }
        } else {
            return [
                'status' => '2',
                'massage' => 'پسورد اشتباه است'
            ];
        }

    }

    function deleteInstalment()
    {


        $data = request()->all();
        if ($this->checkPassword($data['password'])) {
            $instelment = Instalment::where('id', $data['instelment_id'])->first();
            $customer = Customer::where('id', $instelment->customer_id)->first();
            if ($instelment->remainder == "0") {
                return response()->json([
                    "status" => '0',
                    'message' => "امکان حذف قسط تسویه شده وجود ندارد"]);
            }
            $amount = $instelment->paymentMethod == "G" ? $instelment->amount / $instelment->day_rate : $instelment->amount;

            $pays = abs($amount - $instelment->remainder);
//        $this->saveReport("حدف قسط ", $customer->fullname, $customer->id, "7");

            $this->addReportInstelment("حدف قسط ", $customer->fullname, $customer->id, "7",
                $instelment->initial_payment, $instelment->day_rate, $instelment->amount);


            if ($data['in_wallet']) {


                if ($instelment->paymentMethod == "G") {
                    $customer->wallet_g += $pays;

                } else {
                    $customer->wallet_t += $pays;
                }
                $customer->update();
                $instelment->delete();
                $this->saveReport("حدف قسط ", $customer->fullname, $customer->id, "7");
                return response()->json([
                    'status' => '1',
                    'message' => 'قسط با موفقیت حذف شد',
                    'in_wallet' => true
                ], 200);
            } else {
                $instelment->delete();
                return response()->json([
                    'status' => '1',
                    'message' => 'قسط با موفقیت حذف شد'
                ]);
            }
            return $data;
        } else {
            return [
                'status' => '2',
                'massage' => 'پسورد اشتباه است'
            ];
        }
    }

    function saveReport($operation, $customer, $id, $status)
    {
        $rp = new ReportController();
        $rp->addReport($operation, $customer, $id, $status);
    }

    function addReportInstelment($operation, $customer, $id, $status, $init_pay, $day_rate, $amount)
    {
        $rp = new ReportController();
        $rp->addReportInstelment($operation, $customer, $id, $status, $init_pay, $day_rate, $amount);
    }

    function addReportInstelmentEdite($operation, $customer, $id, $status, $init_pay, $init_pay_new, $day_rate, $day_rate_edit, $amount, $amount_new)
    {
        $rp = new ReportController();
        $rp->addReportInstelmentEdite($operation, $customer, $id, $status, $init_pay, $init_pay_new, $day_rate, $day_rate_edit, $amount, $amount_new);
    }

    function checkPassword($password)
    {
        $user = User::all()->first();
        $is_tass_true = Hash::check($password, $user->password);
        return $is_tass_true;
    }
}
