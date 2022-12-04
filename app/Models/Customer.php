<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'name', 'no_telp', 'alamat', 'status','karyawan_id', 'point'
    ];

    public function transaksiCustomer()
    {
        return $this->hasMany(transaksi::class,'customer_id','id');
    }
}
