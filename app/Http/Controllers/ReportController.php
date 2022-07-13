<?php

namespace App\Http\Controllers;

use App\Models\report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use mysql_xdevapi\Exception;
use function PHPUnit\Framework\isEmpty;

class ReportController extends Controller
{
    public function addReport($operation, $customer, $id, $status)
    {
        $rep = report::create(array(
//            'user'=>auth()->user(),
            'user' => 'admin',
            'operation' => $operation,
            'customer' => $customer,
            'customer_id' => $id,
            'status' => $status,
            'date' => Carbon::now()->format('Y-m-d'),
        ));
        return $rep;
    }

    public function addReportInstelment($operation, $customer, $id, $status, $init_pay, $day_rate, $amount)
    {
        $rep = report::create(array(
//            'user'=>auth()->user(),
            'user' => 'admin',
            'operation' => $operation,
            'customer' => $customer,
            'customer_id' => $id,
            'status' => $status,
            'init_pay' => $init_pay,
            'day_rate' => $day_rate,
            'pay' => $amount,
            'date' => Carbon::now()->format('Y-m-d'),
        ));
        return $rep;
    }

    public function addReportInstelmentEdite($operation, $customer, $id, $status, $init_pay,
                                             $init_pay_new, $day_rate, $day_rate_edit, $amount, $amount_new)
    {
        $rep = report::create(array(
//            'user'=>auth()->user(),
            'user' => 'admin',
            'operation' => $operation,
            'customer' => $customer,
            'customer_id' => $id,
            'status' => $status,
            'init_pay' => $init_pay,
            'init_pay_edit' => $init_pay_new,
            'day_rate' => $day_rate,
            'day_rate_edit' => $day_rate_edit,
            'pay' => $amount,
            'edited_pay' => $amount_new,
            'date' => Carbon::now()->format('Y-m-d'),
        ));
        return $rep;
    }

    public function addCashReport($operation, $customer, $id, $status, $payer_name, $pay, $edited_pay, $day_rate)
    {
        $rep = report::create(array(
//            'user'=>auth()->user(),
            'user' => 'admin',
            'operation' => $operation,
            'customer' => $customer,
            'customer_id' => $id,
            'payer_name' => $payer_name,
            'pay' => $pay,
            'day_rate' => $day_rate,
            'status' => $status,
            'edited_pay' => $edited_pay,
            'date' => Carbon::now()->format('Y-m-d'),
        ));
        return $rep;
    }


    function getReports()
    {
        $data = request()->all();
//        return $data;
//        if (request()->has('from_date') && request()->has('to_date')) {
        if ($data["from_date"]!='' && $data['to_date']!='') {
            $from = date($data['from_date']);
            $to = date($data['to_date']);
//            $rep = report::orderBy('created_at', 'DESC')->whereBetween('date', [$from, $to])->paginate(50);
            $rep = report::orderBy('created_at', 'DESC')->whereBetween('date', [$from, $to])->get();
            return $rep->isEmpty() ? response()->json([
                'status' => '0',
                'message' => 'گزارش در این ناریخ ثبت نشده است'
            ]) : response()->json([
                'status' => '1',
                'reports' => $rep
            ]);
        } else {
            $rep = report::orderBy('created_at', 'DESC')->paginate(50);
            return [
                'status' => '2',
                'reports' => $rep
            ];

        }


//


    }
}
