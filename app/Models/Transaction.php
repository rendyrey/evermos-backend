<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function payment(){
        return $this->belongsTo('App\Models\Payment','payment_id','id');
    }

}
