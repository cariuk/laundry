<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use ErrorException;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Requests\AddCustomerRequest;
use Illuminate\Support\Facades\Hash;
use App\Jobs\RegisterCustomerJob;
use Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    // index
    public function index()
    {
        $customer = Customer::orderBy('id', 'DESC')->get();
        return view('karyawan.customer.index', compact('customer'));
    }

    // Detail Customer
    public function detail($id)
    {
        $customer = Customer::with('transaksiCustomer')
            ->where('karyawan_id', Auth::user()->id)
            ->where('id', $id)->first();
        return view('karyawan.customer.detail', compact('customer'));
    }

    // Create
    public function store(AddCustomerRequest $request)
    {
        try {
            DB::beginTransaction();

            $phone_number = preg_replace('/^0/', '62', $request->no_telp);
            $password = str::random(8);

            $addCustomer = Customer::create([
                'karyawan_id' => Auth::id(),
                'name' => $request->name,
                'status' => 'Active',
                'no_telp' => $phone_number,
                'alamat' => $request->alamat
            ]);

            DB::commit();
            Session::flash('success', 'Customer Berhasil Ditambah !');
            return redirect('customers');
        } catch (ErrorException $e) {
            DB::rollback();
            throw new ErrorException($e->getMessage());
        }
    }

    public function update(AddCustomerRequest $request)
    {
        try {
            DB::beginTransaction();

            $phone_number = preg_replace('/^0/', '62', $request->no_telp);
            $password = str::random(8);

            $updateCustomer = Customer::where([
                "id" => $request->id
            ])->update([
                'karyawan_id' => Auth::id(),
                'name' => $request->name,
                'status' => 'Active',
                'no_telp' => $phone_number,
                'alamat' => $request->alamat
            ]);

            DB::commit();
            Session::flash('success', 'Customer Berhasil Diupdate !');
            return redirect('customers');
        } catch (ErrorException $e) {
            DB::rollback();
            throw new ErrorException($e->getMessage());
        }
    }


    // Store
    public function create()
    {
        return view('karyawan.customer.create');
    }

    public function edit($customer)
    {
        $customer = Customer::where("id", $customer)->first();
        return view('karyawan.customer.update', compact('customer'));
    }
}
