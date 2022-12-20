<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class transaksi extends Model
{
    use Notifiable;

    protected $fillable = [
        'customer_id',
        'user_id',
        'tgl_masuk',
        'tgl_transaksi',
        'customer',
        'status_order',
        'status_payment',
        'harga_id',
        'kg',
        'hari',
        'harga',
        'tgl',
        'tgl_ambil',
        'invoice',
        'disc',
        'bulan',
        'tahun',
        'harga_akhir',
        'jenis_pembayaran'
    ];

    public static function generateInvoice($tgl_masuk)
    {
        $prefix = date('Ymd',strtotime($tgl_masuk));
        $result = transaksi::where("invoice","like",$prefix."%")->first();
        if ($result == null){
            $result = $prefix.str_pad("1","3","0",STR_PAD_LEFT);
        }else{
            $result = str_replace($prefix,"",$result->invoice);
            $result++;
            $result = $prefix.str_pad($result,"3","0",STR_PAD_LEFT);
        }

        return $result;
    }

    public function price()
    {
        return $this->belongsTo(harga::class, 'harga_id', 'id');
    }

    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
