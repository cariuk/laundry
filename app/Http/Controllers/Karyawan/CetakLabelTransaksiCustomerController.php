<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\transaksi;
use Illuminate\Http\Request;
use clsTinyButStrong;
use PhpOffice\PhpWord\TemplateProcessor;

class CetakLabelTransaksiCustomerController extends Controller
{
    function request(Request $request)
    {

        $invoice = transaksi::with('price')->with('customers')
            ->where('id', $request->transaksi_id)
            ->first();
        $path = storage_path('app/template/CetakLabelCucianFormat1.docx');
        $content = base64_encode( 'CetakThermal|' . $request->url . '|CetakThermal|1|');

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

        $output = storage_path('app/output/CetakLabelCucianFormat_'.$invoice->id.'.docx');
        $templateProcessor->saveAs($output);

        $result = [
            "status" => 200,
            "service" => "printerservices",
            "url" => $request->url,
            "content" => $content,
            "decode" => base64_decode($content),
        ];

        return response()->json($result);
    }

    function index($transaksi)
    {
        $output = storage_path('app/output/CetakLabelCucianFormat_'.$transaksi.'.docx');
        $TBS = new clsTinyButStrong();
        $TBS->LoadTemplate($output);
        $TBS->Show(32);
        $content = base64_encode($TBS->Source);

        return response()->json([
            "status" => 200,
            "content" => $content
        ]);
    }
}
