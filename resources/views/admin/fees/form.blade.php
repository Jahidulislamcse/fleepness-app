@extends('admin.admin_dashboard')

@section('main')
<div class="page-inner">
    <h4 class="page-title">Platform Fee's and other fees</h4>



    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

     <form action="{{ route('admin.fees.storeOrUpdate') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="vat" class="form-label">VAT (%)</label>
            <input type="text" name="vat" class="form-control" value="{{ old('vat', $fee->vat ?? '') }}">
            @error('vat')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="mb-3">
            <label for="platform_fee" class="form-label">Platform Fee (BDT)</label>
            <input type="text" name="platform_fee" class="form-control" value="{{ old('platform_fee', $fee->platform_fee ?? '') }}">
            @error('platform_fee')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="mb-3">
            <label for="commission" class="form-label">Commission (%)</label>
            <input type="text" name="commission" class="form-control" value="{{ old('commission', $fee->commission ?? '') }}">
            @error('commission')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <button type="submit" class="btn btn-primary">Save Fees</button>
    </form>

    @if($fee)
    <hr>
    <h3>Current Fees</h3>
    <ul>
        <li>VAT: {{ $fee->vat }}%</li>
        <li>Platform Fee: {{ $fee->platform_fee }} BDT</li>
        <li>Commission: {{ $fee->commission }}%</li>
    </ul>
    @endif

   
</div>
@endsection
