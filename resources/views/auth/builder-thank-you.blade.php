@extends('platform::auth')
@section('title', 'Registration Successful')

@section('content')
<div class="text-center bg-white p-5 rounded shadow-sm">
    <div class="mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#6FB96F" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </svg>
    </div>

    <h2 class="h4 mb-3">Registration Submitted!</h2>

    <p class="text-muted mb-4">
        Thank you for registering with <strong>KeyWe</strong>.
        <br><br>
        Your account is currently <strong>Pending Verification</strong>.
        Our Admin team will review your KYC documents. You will receive an email once your profile is approved.
    </p>

    <a href="{{ route('platform.login') }}" class="btn btn-link text-primary">Return to Login</a>
</div>
@endsection