<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\WorkmateController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\InstalmentController;
use App\Http\Controllers\WorkmateInterchangeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;



Route::post('/register',[RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', [LoginController::class, 'logout']);
//Route::middleware('auth:api')->group(function () {
//
//
//    // show logged-in user info
//
//
//
//
//});
Route::post('/user', [HomeController::class, 'showUserInfo']);
Route::post('/check_login', [HomeController::class, 'checkLogin']);

//dashbourd info
Route::post('/get_dashboard_info', [HomeController::class, 'getDashboardInfo']);
Route::post('/get_all_intergange_dashboard', [HomeController::class, 'getAllIntergangeDashboard']);

// customers routes
Route::post('customers', [CustomerController::class, 'allCustomers']);
Route::post('/customers_store', [CustomerController::class, 'store']);
Route::post('/customers_show', [CustomerController::class, 'show']);
Route::post('/customers_update', [CustomerController::class, 'update']);
Route::post('/customers_delete', [CustomerController::class, 'delete']);

// workmates routes
Route::post('workmates', [WorkmateController::class, 'allWorkmates']);
Route::post('/workmates_store', [WorkmateController::class, 'store']);
Route::post('/workmates_show', [WorkmateController::class, 'show']);
Route::post('/workmates_update', [WorkmateController::class, 'update']);
Route::post('/workmates_delete', [WorkmateController::class, 'delete']);


//Instalment
Route::post('/add_instalment',[InstalmentController::class,'addInstalment']);
Route::post('/get_instalment',[InstalmentController::class,'getInstalment']);
Route::post('/get_customer_instalment',[InstalmentController::class,'getCustomerInstalment']);
Route::post('/get_instalment_remainder',[InstalmentController::class,'getInstalmentRemiander']);
Route::post('/send_instalment_remainder',[InstalmentController::class,'sendRemainderSms']);
Route::post('/edit_instalment',[InstalmentController::class,'editInstelment']);
Route::post('/delete_instalment',[InstalmentController::class,'deleteInstalment']);


//payment
Route::post('/new_payment',[PaymentController::class,'newPayment']);
Route::post('/new_payment_pic',[PaymentController::class,'saveImages']);
Route::post('/get_customer_payment',[PaymentController::class,'getCustomerPayment']);
Route::post('/edit_payment',[PaymentController::class,'editPayment']);
Route::post('/delete_payment',[PaymentController::class,'deletePayment']);



//WorkmateInterchangeController
Route::post('/new_purchase',[WorkmateInterchangeController::class,'newPurchase']);
Route::post('/new_sale',[WorkmateInterchangeController::class,'newSale']);
Route::post('/new_workmate_payment',[WorkmateInterchangeController::class,'newWorkmatePayment']);
Route::post('/new_workmate_receive',[WorkmateInterchangeController::class,'newWorkmateReceive']);
Route::post('/get_workmate_purchase',[WorkmateInterchangeController::class,'getWorkmatePurchase']);
Route::post('/get_workmate_sale',[WorkmateInterchangeController::class,'getWorkmateSale']);
Route::post('/get_workmate_payment',[WorkmateInterchangeController::class,'getWorkmatePayment']);
Route::post('/get_workmate_receive',[WorkmateInterchangeController::class,'getWorkmateReceive']);

Route::post('/get_workmate_interchange', [WorkmateInterchangeController::class, 'getWorkmateInterchange']);

//report
Route::post('/get_reports',[ReportController::class,'getReports']);

Route::post('/test',function (Request $request){

});
Route::get('/check',function (){
    return['status'=>1];
});

