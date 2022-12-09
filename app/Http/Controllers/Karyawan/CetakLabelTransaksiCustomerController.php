<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\transaksi;
use Illuminate\Http\Request;
use clsTinyButStrong;
use clsOpenTBS;
use PhpOffice\PhpWord\TemplateProcessor;

class CetakLabelTransaksiCustomerController extends Controller
{
    function request(Request $request)
    {
        $content = base64_encode('CetakLabelCucianFormat1|' . $request->url . '|CetakKartu|1|');
        $result = [
            "status" => 200,
            "service" => "printerservices",
            "url" => $request->url,
            "content" => $content,
        ];

        return response()->json($result);
    }

    function index($transaksi)
    {
        $path = storage_path('app/template/CetakLabelCucianFormat1.docx');
        $invoice = transaksi::with('price')->with('customers')
            ->where('id', $transaksi)
            ->first();

        $TBS = new clsTinyButStrong();
        $TBS->Plugin(TBS_INSTALL, 'clsOpenTBS');
        $TBS->LoadTemplate($path, OPENTBS_ALREADY_UTF8);
        $TBS->VarRef['laundry_nama'] = 'Babussalam Laundry';
        $TBS->VarRef['nama'] = $invoice->customers->name ?? '';
        $TBS->VarRef['alamat'] = $invoice->customers->alamat ?? '';
        $TBS->VarRef['no_telp'] = $invoice->customers->no_telp ?? '';
        $TBS->VarRef['tgl_masuk'] = (string) $invoice->created_at ?? '';
        $TBS->VarRef['jenis_layanan'] = (string) $invoice->price->jenis ?? '';
        $TBS->VarRef['berat'] = (string) $invoice->kg ?? '';
        $TBS->VarRef['harga'] = (string) $invoice->harga_akhir?? '';
        $TBS->Show(OPENTBS_STRING);
        $reports = base64_encode($TBS->Source);
        $reports = stripslashes($reports);

        return response()->json([
            "status" => 200,
            "content" => $reports
        ]);
    }
}
