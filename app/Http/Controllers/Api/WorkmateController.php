<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\WorkmateResource;
use App\Models\Workmate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WorkmateController extends Controller
{

    public function allWorkmates()
    {

        $workmates = Workmate::query();

        if ($search = request('search')) {
            $workmates = Workmate::where('fullname', 'LIKE', '%' . $search . '%')->orWhere('national_code', 'LIKE', '%' . $search . '%');
        }

        return WorkmateResource::collection($workmates->orderBy('fullname')->paginate(50));
    }


    public function store(Request $request)
    {
        $validator = $this->validateCreateWorkmate($request);

        if ($validator->fails()) {
            return response()->json([
                'status'=>'0',
                'message' => 'Error Occured While Creating new Workmate',
                'errors' => $validator->errors()->all()
            ], 200);
        }
        //dd($request);
        $workmate = Workmate::create($request->all());

        return response()->json([
            'status'=>'1',
            'workmate_information' => $workmate
        ], 200);
    }


    public function show(Request $request)
    {
        $workmate = Workmate::findOrFail($request->id);
        return new WorkmateResource($workmate);
    }


    public function update(Request $request)
    {
        $workmate = Workmate::findOrFail($request->id);

        $validator = $this->validateUpdateWorkmate($request, $workmate);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error Occured While Updating Workmate Info',
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $workmate->update([
            'fullname' => $request->fullname,
            'email' => $request->email,
            'national_code' => $request->national_code
        ]);

        return response()->json([
            'message' => 'Workmate Inforamtion Updated Successfully!',
            'customer_information' => new WorkmateResource($workmate)
        ], 200);
    }


    public function delete(Request $request)
    {
        $workmate = Workmate::findOrFail($request->id);

        $tmp = $workmate;

        $workmate->delete();

        return response()->json([
            'message' => 'Workmate Deleted Successfully',
            'deleted_workmate' => new WorkmateResource($tmp)
        ], 200);
    }


    private function validateCreateWorkmate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => ['required', 'string'],
            'phone' => ['required'],
            'national_code' => ['required', 'max:10', Rule::unique('workmates', 'national_code')]
        ],[
            "fullname"=>'نام ازامی است',
            'national_code.unique' => 'این کد ملی قبلا در سیستم ثبت شده است',
        ]);

        return $validator;
    }

    private function validateUpdateWorkmate(Request $request, Workmate $workmate)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'national_code' => ['required', 'max:10']
        ]);

        return $validator;
    }
}
