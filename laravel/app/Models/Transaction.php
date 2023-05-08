<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Allocations;
use App\Models\Vouchers;
use App\Models\FoTrans;
use App\Models\Accounts;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = ['FOTrans','CheckNo','Object','Amount','Specifications','Allocation','PPA'];
    protected $primaryKey = 'TransNo';

    /**
     * Get the user that owns the phone.
     */
    public function voucher()
    {
        return $this->belongsTo(Vouchers::class,'CheckNo','CheckNo');
    }
    
    /**
     * Get the user that owns the phone.
     */
    public function allocation()
    {
        return $this->belongsTo(Allocations::class,'Allocation','AllocationNo');
    }

    /**
     * Get the user that owns the phone.
     */
    public function fotrans()
    {
        return $this->belongsTo(FoTrans::class,'FOTrans','FOTrans');
    }
    
    /**
     * Get the user that owns the phone.
     */
    public function pap()
    {
        return $this->belongsTo(PapCodes::class,'PPA','PAPCode');
    }

}
