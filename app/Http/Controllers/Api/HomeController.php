<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Instalment;
use App\Models\Customer;
use App\Models\Workmate;
use App\Models\WorkmatePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\purchase;
use App\Models\sale;
use App\Models\totalReceived;



class HomeController extends Controller
{
    public function showUserInfo()
    {
        $user = User::all()[0];
        return $user;
//           $user = Auth::user();
//          if ($user == null){
//              return ["isLogin"=>false];
//          }else return ["isLogin"=>true
//          ,new UserResource($user)];


    }

    function checkLogin()
    {
        $user = User::all()[0];
        $data = request()->all();
        $token = $data['token'];

        if ($token == $user->auth_id) {
            return [
                "status" => $user->is_login == 1 ? true : false
            ];
        } else {
            return ["status" => false];
        }


    }

    function getDashboardInfo()
    {
        $instalments = Instalment::all()->count();
        $workmates = Workmate::all()->count();
        $customers = Customer::all()->count();

        return response()->json([
            'status' => '1',
            'customer' => $customers,
            'workmate' => $workmates,
            'instalment' => $instalments,
            'user' => $customers+$workmates,
        ], 200);


    }

    function getAllIntergangeDashboard()
    {
        $instalments = Instalment::all();
        $purchase=purchase::all();
        $sale=sale::all();

        $payment=WorkmatePayment::all();
       $received= totalReceived::all();

        $all_instell_t=0;
        $all_instell_g=0;


        $all_workmate_purchase_g=0;
        $all_workmate_purchase_t=0;

        $all_workmate_sale_g=0;
        $all_workmate_sale_t=0;

        $all_workmate_payment_t=0;
        $all_workmate_payment_g=0;

        $all_workmate_receive_t=0;
        $all_workmate_receive_g=0;
        foreach ($instalments as $intel){
            if($intel->paymentMethod =="T"){
                $all_instell_t+=$intel->remainder;
            }else{
                $all_instell_g+=$intel->remainder;
            }

        }
        foreach ($purchase as $purch){
            $all_workmate_purchase_g+=$purch->amountG;
            $all_workmate_purchase_t+=$purch->amountT;
        }
        foreach ($sale as $s){
            $all_workmate_sale_g+=$s->amountG;
            $all_workmate_sale_t+=$s->amountT;
        }

        foreach ($payment as $pay){
            if ($pay->payment_method == "T"){
                $all_workmate_payment_t+=$pay->payment_amount;
            }else{
                $all_workmate_payment_g+=$pay->wieght;
            }

        }
        foreach ($received as $receiv){
            if ($receiv->payment_method == "T"){
                $all_workmate_receive_t+=$receiv->payment_amount;
            }else{
                $all_workmate_receive_g+=$receiv->wieght;
            }

        }


        return response()->json([
            'status' => '1',
            
            'all_instalment_t' => $all_instell_t,
            'all_instalment_g' => $all_instell_g,

            'purchase_g' => $all_workmate_purchase_g,
            'purchase_t' => $all_workmate_purchase_t,

            'sale_g' => $all_workmate_sale_g,
            'sale_t' => $all_workmate_sale_t,

            'payment_t' => $all_workmate_payment_t,
            'payment_g' => $all_workmate_payment_g,

            'receive_t' => $all_workmate_receive_t,
            'receive_g' => $all_workmate_receive_g,

        ], 200);


    }


}
