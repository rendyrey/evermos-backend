<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $table = 'transaction_details';

    public function transaction(){
        return $this->belongsTo('App\Models\Transaction','transaction_id','id');
    }

    public function product(){
        return $this->belongsTo('App\Models\Product','product_id','id');
    }
}
