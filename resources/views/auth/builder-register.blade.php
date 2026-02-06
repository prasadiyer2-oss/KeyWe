@extends('platform::auth')
@section('title', 'Partner with KeyWe')

@section('content')
<div class="text-center mb-4">
    <h1 class="h4 text-black">Builder Registration</h1>
    <p class="text-muted small">Join India's first transaction-ready real estate platform.</p>
</div>

<form class="p-4 bg-white rounded shadow-sm"
      action="{{ route('builder.register.submit') }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    <div class="mb-3">
        <label class="form-label">Organization Name</label>
        <input type="text" name="name" class="form-control" placeholder="KeyWe Developers Pvt Ltd" required value="{{ old('name') }}">
        @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="contact@builder.com" required value="{{ old('email') }}">
        @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="********" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Confirm</label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="********" required>
        </div>
    </div>
    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror

    <hr class="my-4">

    <div class="mb-3 p-3 bg-light rounded border">
        <label class="form-label fw-bold text-dark">KYC Verification Documents</label>
        <p class="text-muted small mb-2">
            Please upload <strong>all relevant documents</strong> (e.g., GST Certificate, RERA Registration, PAN Card).
            <br>
            <span class="text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                </svg>
                Tip: Hold <strong>Ctrl</strong> (or Cmd) to select multiple files at once.
            </span>
        </p>

        <input type="file" 
               name="kyc_documents[]" 
               class="form-control" 
               accept=".pdf,.jpg,.png" 
               multiple 
               required>

        @error('kyc_documents') <span class="text-danger small">{{ $message }}</span> @enderror
        @error('kyc_documents.*') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="d-grid gap-2 mt-4">
        <button type="submit" class="btn btn-primary btn-lg text-white" 
                style="background-color: #6FB96F; border-color: #6FB96F;">
            Submit Application
        </button>
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('platform.login') }}" class="text-muted small">Already have an account? Login</a>
    </div>
</form>
@endsection