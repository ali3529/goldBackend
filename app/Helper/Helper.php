<?php
namespace App\Helper;
use App\Http\Controllers\ReportController;
class Helper{
public static function createReport($operation,$customer,$status){

        $rp=new ReportController();
        return $rp->addReport($operation,$customer,$status);

}
}
