@extends('layouts.backend')
@section('title','Edit Data Order')
@section('content')

    @if($message = Session::get('error'))
      <div class="alert alert-danger alert-block">
      <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>{{ $message }}</strong>
      </div>
    @endif

    <div class="card card-outline-info">
      <div class="card-header">
          <h4 class="card-title">Form Tambah Data Order</h4>
      </div>
      <div class="card-body">
        {{-- Cek Apakah Customer ada --}}

          <form action="{{route('pelayanan.update',[$order->id])}}" method="POST">
            @csrf
            <input name="id" type="hidden" value="{{$order->id}}">
            <div class="form-body">
              <div class="row p-t-20">
                  <div class="col-md-6">
                      <div class="form-group has-success">
                          <label class="control-label">Nama Customer</label>
                          <select name="customer_id" id="customer_id" class="form-control select2 @error('customer_id') is-invalid @enderror" >
                              <option value="">-- Pilih Customer --</option>
                              @foreach ($customer as $customers)
                                  <option value="{{$customers->id}}" {{old('customer_id') == $customers->id ? 'selected' : ''}} selected >{{$customers->name}}</option>
                              @endforeach
                          </select>
                      </div>
                  </div>
                  <div class="col-md-6">
                      <div class="form-group has-success">
                          <label class="control-label">Tanggal Dan Waktu Masuk</label>
                          <input id="tgl_masuk" type="datetime-local" class="form-control form-control-danger @error('tgl_masuk') is-invalid @enderror" value="{{date('Y-m-d\TH:i',strtotime($order->tgl_masuk))}}" name="tgl_masuk" placeholder="Tanggal Dan Waktu Masuk" autocomplete="off" >
                          @error('tgl_masuk')
                          <span class="invalid-feedback text-danger" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                          @enderror
                      </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group has-success">
                        <label class="control-label">Status Pembayaran</label>
                        <select class="form-control custom-select @error('status_payment') is-invalid @enderror" name="status_payment" >
                            <option value="">-- Pilih Status Payment --</option>
                            <option value="Pending" {{$order->status_payment == 'Pending' ? 'selected' : ''}} >Belum Dibayar</option>
                            <option value="Success" {{$order->status_payment == 'Success' ? 'selected' : ''}}>Sudah Dibayar</option>
                        </select>
                        @error('status_payment')
                          <span class="invalid-feedback text-danger" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                        @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                      <div class="form-group has-success">
                          <label class="control-label">Jenis Pembayaran</label>
                          <select class="form-control custom-select @error('jenis_pembayaran') is-invalid @enderror" name="jenis_pembayaran" >
                              <option value="">-- Pilih Jenis Pembayaran --</option>
                              <option value="Tunai" {{$order->jenis_pembayaran == 'Tunai' ? 'selected' : ''}} >Tunai</option>
                              <option value="Transfer" {{$order->jenis_pembayaran == 'Transfer' ? 'selected' : ''}}>Transfer</option>
                          </select>
                          @error('jenis_pembayaran')
                          <span class="invalid-feedback text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                          @enderror
                      </div>
                  </div>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="orm-group has-success">
                      <label class="control-label">Pilih Jenis Layanan</label>
                      <select id="jenis_layanan" name="harga_id" class="form-control select2 @error('harga_id') is-invalid @enderror" >
                          <option value="">-- Jenis Layanan --</option>
                          @foreach($jenisPakaian as $jenis)
                            <option value="{{$jenis->id}}" {{$order->price->id == $jenis->id ? 'selected' : '' }} >{{$jenis->jenis}}</option>
                          @endforeach
                      </select>
                      @error('harga_id')
                        <span class="invalid-feedback text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                      @enderror
                  </div>
                </div>
                  <div class="col-md-2">
                      <div class="form-group has-success">
                          <label class="control-label">Berat Pakaian</label>
                          <input id="berat" type="text" class="form-control form-control-danger @error('kg') is-invalid @enderror" value="{{$order->kg}}" name="kg" placeholder="Berat Pakaian" autocomplete="off" >
                          @error('kg')
                          <span class="invalid-feedback text-danger" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                          @enderror
                      </div>
                  </div>
                  <div class="col-md-2">
                      <div class="form-group has-success">
                          <label class="control-label">Harga</label>
                          <input id="harga" type="number" name="harga" class="form-control" value="{{$order->price->harga}}" readonly>
                      </div>
                  </div>
                  <div class="col-md-2">
                      <div class="form-group has-success">
                          <label class="control-label">Hari</label>
                          <input id="hari" type="number" name="hari" class="form-control" value="{{$order->price->hari}}" readonly>
                      </div>
                  </div>
                <div class="col-md-2">
                  <div class="form-group has-success">
                      <label class="control-label">Disc</label>
                      <input id="discount" type="number" name="disc" value="{{$order->disc}}" placeholder="Tulis Disc" class="form-control @error('disc') is-invalid @enderror">
                      @error('disc')
                        <span class="invalid-feedback text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                      @enderror
                  </div>
                </div>
              </div>
                <hr/>
                <div class="text-right">
                    <h1>Total : <span id="total">{{number_format($order->kg * $order->price->harga)}}</span></h1>

                </div>
                <!--/row-->
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary mr-1 mb-1">Update</button>
              <a href="{{url('pelayanan')}}" class="btn btn-outline-warning mr-1 mb-1">Batal</a>
            </div>
          </form>
      </div>
    </div>
@endsection
@section('scripts')
<script type="text/javascript">
    // Filter Harga
    function calculateTotal(){
        console.log("Calculate");
        console.log("Berat "+$("#berat").val());
        console.log("Discount "+$("#discount").val());
        console.log("Harga "+$("#harga").val());

        var total = ($("#harga").val() *  $("#berat").val()) - $("#discount").val();

        console.log(total);
        $("#total").text(Number(total).toLocaleString());
    }

    $("#berat").keyup(function (){
        calculateTotal()
    });

    $("#discount").keyup(function (){
        calculateTotal()
    });

    $("#jenis_layanan").change(function (){
        var id = $(this).val();
        $.ajax({
            type	: 'GET',
            url		: '{{ Url("listharga") }}',
            data	: {'_token': $('meta[name=csrf-token]').attr('content'),id:id},
            dataType: "json",
            success	: function (response){
                $("#harga").val(response.data.harga);
                $("#hari").val(response.data.hari);
                calculateTotal();
            },
            complete: function () {},
            error: function (xhr, thrownError, err) {}
        });
    });
</script>
@endsection
