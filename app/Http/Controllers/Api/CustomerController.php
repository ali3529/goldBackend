<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Instalment;
use App\Http\Controllers\ReportController;

class CustomerController extends Controller
{

    public function allCustomers()
    {

        $customers = Customer::query();

        if ($search = request('search')) {
            $customers = Customer::where('fullname', 'LIKE', '%' . $search . '%')->orWhere('national_code', 'LIKE', '%' . $search . '%');
        }

        return CustomerResource::collection($customers->orderBy('fullname')->paginate(100));
    }


    public function store(Request $request)
    {
        $validator = $this->validateCreateCustomer($request);

        if ($validator->fails()) {
            return response()->json([
                'status'=>'0',
                'message' => 'Error Occured While Creating new Customer',
                'errors' => $validator->errors()->all()
            ], 200);
        }
//        dd($request->all());
        $customer = Customer::create([
            'fullname' => $request->fullname,
            'national_code' => $request->national_code,
            'phone_number' => $request->phone_number,
            'card_number' => $request->card_number,
            'tell_number' => $request->tell_number,
            'address' => $request->address,
        ]);

        $this->saveReport("افزودن کاربر جدید",$customer->fullname,$customer->id,"1");
        return response()->json([
            'status'=>'1',
            'message' => 'Customer Created Successfully!',
            'customer_information' => $customer
        ], 200);
    }


    public function show(Request $request)
    {
//        return["svdsdv"=>$request->id];
        $customer = Customer::findOrFail($request->id);
        return new CustomerResource($customer);
    }


    public function update(Request $request)
    {
        $customer = Customer::findOrFail($request->id);

        $validator = $this->validateUpdateCustomer($request);


        if ($validator->fails()) {
            return response()->json([
                'status'=>'0',
                'message' => 'Error Occured While Creating new Customer',
                'errors' => $validator->errors()->all()
            ], 200);
        }

        $customer->update($request->all());
        $this->saveReport(" ویرایش کاربر ",$customer->fullname,$customer->id,"2");
        return response()->json([
            'status'=>'1',
            'message' => 'Customer Created Successfully!',
            'customer_information' => $customer
        ], 200);
    }


    public function delete(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);

        $instell=Instalment::where('customer_id',$request->customer_id)->get();
        $instellmet_count=$instell->count();

        if ($instellmet_count == 0){
            $customer->delete();
            $this->saveReport(" حذف  کاربر",$customer->fullname,$customer->id,"4");
            return response()->json([
                'status' => '3',
                'customer_status' =>  "user deleted"
            ], 200);
        }

        if ($customer->status == 0){
            $customer->update(array("status"=>1));
            $this->saveReport(" فعال کردن کاربر",$customer->fullname,$customer->id,"3");
            return response()->json([
                'status' => '2',
                'customer_status' =>  $customer->status
            ], 200);
        }else{
            $customer->update(array("status"=>0));
            $this->saveReport(" غیر فعال کردن کاربر",$customer->fullname,$customer->id,"3");
            return response()->json([
                'status' => '1',
                'customer_status' =>  $customer->status
            ], 200);
        }
        }



    private function validateCreateCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => ['required', 'string', 'max:255'],
//            'email' => ['required', 'string', 'email', 'max:255'],
            'national_code' => ['required', 'max:10', Rule::unique('customers', 'national_code')]
        ],[
            "fullname"=>'نام ازامی است',
            'national_code.unique' => 'این کد ملی قبلا در سیستم ثبت شده است',
        ]);

        return $validator;
    }


    private function validateUpdateCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => ['required', 'string', 'max:255'],
//            'email' => ['required', 'string', 'email', 'max:255'],
            'national_code' => ['required', 'max:10']
        ],[
            'national_code.unique' => 'این کد ملی قبلا در سیستم ثبت شده است',
        ]);


        return $validator;
    }

    function saveReport($operation,$customer,$id,$status){
        $rp=new ReportController();
         $rp->addReport($operation,$customer,$id,$status);
    }
}
