@extends('layouts.app')

@section('title', 'Forgot Password | Camp FreedivePH')
@section('meta_description', 'Recover your Camp FreedivePH staff account password.')

@section('content')
<div class="container d-flex align-items-center justify-content-center px-3 px-sm-4 py-5" style="min-height: 75vh;">
    <div class="w-100" style="max-width: 420px;">
        
        <!-- Header & Logo -->
        <div class="text-center mb-4">
            <a href="{{ route('landing') }}" class="d-inline-block text-decoration-none mb-3">
                <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH Logo" class="rounded-circle object-fit-contain bg-white shadow-sm border border-light mx-auto" style="width: 4rem; height: 4rem; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            </a>
            <h1 class="h3 fw-extrabold text-dark tracking-tight mb-1" style="color: #1D1D1F;">Recover Password</h1>
            <p class="small text-muted mb-0" style="color: #6E6E73;">Enter your staff email to receive a password reset link.</p>
        </div>

        <!-- Form Card -->
        <div class="card bg-white rounded-4 border border-light p-4 p-sm-5 shadow-sm" style="border-color: #E5E5EA !important;">
            
            @if(session('status'))
                <div class="alert mb-4 p-3 rounded-3 text-xs fw-medium" role="alert" style="background-color: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; font-size: 0.8rem;">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf

                <!-- Email Input -->
                <div class="mb-4">
                    <label for="email" class="form-label fw-bold text-dark small mb-1" style="color: #1D1D1F;">
                        Registered Staff Email <span style="color: #780000;">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}" 
                           placeholder="staff@campfreedive.ph" 
                           required 
                           autofocus
                           class="form-control px-3 py-3 rounded-3 shadow-none @error('email') is-invalid @enderror" 
                           style="border-color: {{ $errors->has('email') ? '#FF3B3C' : '#D1D1D6' }}; font-size: 0.875rem;">
                    
                    @error('email')
                        <div class="invalid-feedback fw-semibold mt-1" style="color: #FF3B3C; font-size: 0.75rem;">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn w-full py-3 text-sm fw-bold shadow-sm text-white" style="background-color: #780000; border-color: #780000;">
                    Send Password Reset Link &rarr;
                </button>
            </form>

            <div class="mt-4 pt-4 border-top text-center" style="border-color: #E5E5EA !important;">
                <a href="{{ route('login') }}" class="small fw-bold text-decoration-none" style="color: #780000;">
                    &larr; Return to Login
                </a>
            </div>
        </div>

    </div>
</div>
@endsection