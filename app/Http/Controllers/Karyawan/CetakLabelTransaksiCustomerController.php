<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\transaksi;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class CetakLabelTransaksiCustomerController extends Controller
{
    function index($transaksi)
    {
        $invoice = transaksi::with('price')->with('customers')
            ->where('id', $transaksi)
            ->first();

        $path = storage_path('app/template/CetakLabelCucianFormat1.docx');
        $output = storage_path('app/output/CetakLabelCucianFormat1.docx');
        $templateProcessor = new TemplateProcessor($path);

        $templateProcessor->setValues([
            'laundry_nama' => 'Babussalam Laundry',
            'nama' => $invoice->customers->name,
            'alamat' => $invoice->customers->alamat,
            'no_telp' => $invoice->customers->no_telp,
            'tanggal_masuk' => $invoice->created_at,
            'jenis_layanan' => $invoice->price->jenis,
            'berat' => $invoice->kg,
            'harga' => $invoice->harga_akhir,
        ]);

        $templateProcessor->saveAs($output);
        $data = file_get_contents($output);
        $content = base64_encode($data);
        unlink($output);
        return response()->json([
            "content" => $content
        ]);
    }
}
