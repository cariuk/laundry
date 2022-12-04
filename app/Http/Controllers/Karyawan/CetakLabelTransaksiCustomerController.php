<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\transaksi;
use Illuminate\Http\Request;

class CetakLabelTransaksiCustomerController extends Controller
{
    function index($transaksi)
    {
        $invoice = transaksi::with('price')->with('customers')
            ->where('id',$transaksi)
            ->first();


        $path = storage_path('app/template/CetakLabelCucianFormat1.docx');

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($path);

        $templateProcessor->setValues([
            'laundry_nama' => 'Babussalam Laundry',
            'nama' => $invoice->customers->name,
            'alamat' => $invoice->customers->alamat,
            'no_telp' => $invoice->customers->no_telp,
            'tanggal_masuk' => $invoice->tgl_transaksi,
            'jenis_layanan' => $invoice->price->jenis,
            'berat' => $invoice->kg,
            'harga' => $invoice->harga_akhir,
        ]);

        header("Content-Disposition: attachment; filename=template.docx");

        $templateProcessor->saveAs('php://output');
    }
}
