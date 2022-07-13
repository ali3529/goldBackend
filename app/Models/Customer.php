<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $casts = [
        'status' => 'integer',

    ];
//    protected $fillable = [];
//    protected $fillable = ['fullname', 'email', 'national_code'];
}
