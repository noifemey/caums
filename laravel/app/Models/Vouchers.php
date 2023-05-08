<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;

class Vouchers extends Model
{
    use HasFactory;    
    
    /**
    * Get the user that owns the phone.
    */
   public function transactions()
   {
       return $this->hasMany(Transaction::class,'CheckNo','CheckNo');
   }
   
}
