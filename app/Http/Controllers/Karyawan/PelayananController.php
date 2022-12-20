<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Requests\UpdateOrderRequest;
use carbon\carbon;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddOrderRequest;
use Illuminate\Support\Facades\Session;
use App\Models\{Customer, transaksi, User, harga, DataBank, Notification};
use App\Jobs\DoneCustomerJob;
use App\Notifications\{OrderMasuk, OrderSelesai};

class PelayananController extends Controller
{
    // Halaman list order masuk
    public function index()
    {
        $order = transaksi::with('price')->where('user_id', Auth::user()->id)
            ->orderBy('id', 'DESC')->get();
        return view('karyawan.transaksi.order', compact('order'));
    }

    // Proses simpan order
    public function store(AddOrderRequest $request)
    {
        /*Cek*/
        $hargas = harga::where("id",$request->harga_id)->first();

        try {
            DB::beginTransaction();
            $order = new transaksi();
            $order->invoice = transaksi::generateInvoice($request->tgl_masuk);
            $order->tgl_masuk = $request->tgl_masuk;
            $order->tgl_transaksi = Carbon::now()->parse($order->tgl_transaksi)->format('d-m-Y');
            $order->status_payment = $request->status_payment;
            $order->harga_id = $request->harga_id;
            $order->customer_id = $request->customer_id;
            $order->user_id = Auth::user()->id;
            $order->customer = namaCustomer($order->customer_id);
            $order->hari = $request->hari;
            $order->kg = $request->kg;
            $order->harga = $hargas->harga;
            $order->disc = $request->disc;

            /*Calculate*/
            $hitung = $order->kg * $order->harga;
            if ($request->disc != NULL) {
                $total = $hitung - $request->disc;
                $order->harga_akhir = $total;
            } else {
                $order->harga_akhir = $hitung;
            }
            $order->jenis_pembayaran = $request->jenis_pembayaran;
            $order->tgl = Carbon::now()->day;
            $order->bulan = Carbon::now()->month;
            $order->tahun = Carbon::now()->year;
            $order->save();

            if ($order) {
                // Notification Telegram
                if (setNotificationTelegramIn(1) == 1) {
                    $order->notify(new OrderMasuk());
                }

                // Notification email
                if (setNotificationEmail(1) == 1) {
                    // Menyiapkan data Email
                    $bank = DataBank::get();
                    $jenisPakaian = harga::where('id', $order->harga_id)->first();
                    $data = array(
                        'email' => $order->email_customer,
                        'invoice' => $order->invoice,
                        'customer' => $order->customer,
                        'tgl_transaksi' => $order->tgl_transaksi,
                        'pakaian' => $jenisPakaian->jenis,
                        'berat' => $order->kg,
                        'harga' => $order->harga,
                        'harga_disc' => ($hitung * $order->disc) / 100,
                        'disc' => $order->disc,
                        'total' => $order->kg * $order->harga,
                        'harga_akhir' => $order->harga_akhir,
                        'laundry_name' => Auth::user()->nama_cabang,
                        'bank' => $bank
                    );
                }
                DB::commit();
                Session::flash('success', 'Order Berhasil Ditambah !');
                return redirect('pelayanan');
            }
        } catch (ErrorException $e) {
            DB::rollback();
            throw new ErrorException($e->getMessage());
        }
    }

    // Proses update order
    public function update(UpdateOrderRequest $request)
    {
        /*Cek*/
        $hargas = harga::where("id",$request->harga_id)->first();

        try {
            DB::beginTransaction();
            $order = new transaksi();
            $order->id = $request->id;
            $order->exists = true;

            $order->tgl_masuk = $request->tgl_masuk;
            $order->status_payment = $request->status_payment;
            $order->harga_id = $request->harga_id;
            $order->user_id = Auth::user()->id;

            $order->hari = $request->hari;
            $order->kg = $request->kg;
            $order->harga = $hargas->harga;
            $order->disc = $request->disc;

            /*Calculate*/
            $hitung = $order->kg * $order->harga;
            if ($request->disc != NULL) {
                $total = $hitung - $request->disc;
                $order->harga_akhir = $total;
            } else {
                $order->harga_akhir = $hitung;
            }

            $order->jenis_pembayaran = $request->jenis_pembayaran;
            $order->save();

            if ($order) {
                // Notification Telegram
                if (setNotificationTelegramIn(1) == 1) {
                    $order->notify(new OrderMasuk());
                }

                // Notification email
                if (setNotificationEmail(1) == 1) {
                    // Menyiapkan data Email
                    $bank = DataBank::get();
                    $jenisPakaian = harga::where('id', $order->harga_id)->first();
                    $data = array(
                        'email' => $order->email_customer,
                        'invoice' => $order->invoice,
                        'customer' => $order->customer,
                        'tgl_transaksi' => $order->tgl_transaksi,
                        'pakaian' => $jenisPakaian->jenis,
                        'berat' => $order->kg,
                        'harga' => $order->harga,
                        'harga_disc' => ($hitung * $order->disc) / 100,
                        'disc' => $order->disc,
                        'total' => $order->kg * $order->harga,
                        'harga_akhir' => $order->harga_akhir,
                        'laundry_name' => Auth::user()->nama_cabang,
                        'bank' => $bank
                    );
                }
                DB::commit();
                Session::flash('success', 'Order Berhasil Ditambah !');
                return redirect('pelayanan');
            }
        } catch (ErrorException $e) {
            DB::rollback();
            throw new ErrorException($e->getMessage());
        }
    }

    // Tambah Order
    public function addorders($customer_id = null)
    {
        if ($customer_id == null) {
            $customer = Customer::get();
        } else {
            $customer = Customer::where("id", $customer_id)->get();
        }

        $jenisPakaian = harga::where('user_id', Auth::id())->where('status', '1')->get();

        $cek_harga = harga::where('user_id', Auth::user()->id)->where('status', 1)->first();
        $cek_customer = Customer::select('id', 'karyawan_id')->count();

        return view('karyawan.transaksi.addorder', compact('customer_id', 'customer', 'cek_harga', 'cek_customer', 'jenisPakaian'));
    }

    // Edit Order
    public function editorders($order)
    {
        $order = transaksi::with(['customers','price','user'])->where("id",$order)->first();

        $customer = Customer::where("id", $order->customers->id)->get();

        $jenisPakaian = harga::where('user_id', Auth::id())->where('status', '1')->get();

        $cek_harga = harga::where('user_id', Auth::user()->id)->where('status', 1)->first();
        $cek_customer = Customer::select('id', 'karyawan_id')->count();

        return view('karyawan.transaksi.editorder', compact('order','customer', 'cek_harga', 'cek_customer', 'jenisPakaian'));
    }

    // Filter List Harga
    public function listharga(Request $request)
    {
        $list_harga = harga::select('id', 'harga', 'hari')
            ->where('id', $request->id)->first();

        return response()->json([
            'status' => 200,
            'data' => $list_harga
        ]);
    }


    // Update Status Laundry
    public function updateStatusLaundry(Request $request)
    {
        $transaksi = transaksi::find($request->id);
        if ($transaksi->status_payment == 'Pending') {
            $transaksi->update([
                'status_payment' => 'Success'
            ]);
        } elseif ($transaksi->status_payment == 'Success') {
            if ($transaksi->status_order == 'Process') {
                $transaksi->update([
                    'status_order' => 'Done'
                ]);

                // Tambah point +1
                $points = User::where('id', $transaksi->customer_id)->firstOrFail();
                $points->point = $points->point + 1;
                $points->update();

                // Create Notifikasi
                $id = $transaksi->id;
                $user_id = $transaksi->customer_id;
                $title = 'Pakaian Selesai';
                $body = 'Pakaian Sudah Selesai dan Sudah Bisa Diambil :)';
                $kategori = 'info';
                sendNotification($id, $user_id, $kategori, $title, $body);

                // Cek email notif
                if (setNotificationEmail(1) == 1) {

                    // Menyiapkan data
                    $data = array(
                        'email' => $transaksi->email_customer,
                        'invoice' => $transaksi->invoice,
                        'customer' => $transaksi->customer,
                        'nama_laundry' => Auth::user()->nama_cabang,
                        'alamat_laundry' => Auth::user()->alamat_cabang,
                    );

                    // Kirim Email
                    dispatch(new DoneCustomerJob($data));
                }

                // Cek status notif untuk telegram
                if (setNotificationTelegramFinish(1) == 1) {
                    $transaksi->notify(new OrderSelesai());
                }

                // Notifikasi WhatsApp
                if (setNotificationWhatsappOrderSelesai(1) == 1 && getTokenWhatsapp() != null) {
                    $waCustomer = $transaksi->customers->no_telp; // get nomor whatsapp customer
                    $nameCustomer = $transaksi->customers->name; // get name customer
                    notificationWhatsapp(
                        getTokenWhatsapp(), // Token
                        $waCustomer, // nomor whatsapp
                        'Halo Kak ' . $nameCustomer . ' Laundry kamu sudah selesai dan sudah bisa diambil nih :) ' // pesan
                    );
                }

            } elseif ($transaksi->status_order == 'Done') {
                $transaksi->update([
                    'status_order' => 'Delivery'
                ]);
            }
        }

        if ($transaksi->status_payment == 'Success') {
            Session::flash('success', "Status Pembayaran Berhasil Diubah !");
        }
        if ($transaksi->status_order == 'Done' || $transaksi->status_order == 'Delivery') {
            Session::flash('success', "Status Laundry Berhasil Diubah !");
        }
    }
}
