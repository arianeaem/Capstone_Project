@extends('layouts.app')

@section('title', 'Internal Staff Login | Camp FreedivePH')
@section('meta_description', 'Internal portal login for Camp FreedivePH coaches, administrators, and owner.')

@section('content')
<div class="min-vh-75 d-flex align-items-center justify-content-center px-3 px-sm-4 px-lg-5 py-5">
    <div class="w-100" style="max-width: 28rem;">
        
        <!-- Header & Logo -->
        <div class="text-center mb-4">
            <h1 class="h2 fw-bolder tracking-tight" style="color: #8B011A;;">Log in to your account</h1>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-4 border p-4 p-sm-5 shadow-sm" style="border-color: #E5E5EA !important;">

            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <!-- Email Input -->
                <div class="mb-4">
                    <label for="email" class="form-label fw-bold small mb-1" style="color: #1D1D1F;">
                        Email Address <span style="color: #780000;">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}" 
                           placeholder="staff@campfreedive.ph" 
                           required 
                           autofocus
                           class="form-control rounded-3 py-3 px-3 shadow-none @error('email') is-invalid @enderror"
                           style="border-color: {{ $errors->has('email') ? '#FF3B3C' : '#D1D1D6' }}; font-size: 0.875rem;">
                    
                    @error('email')
                        <div class="invalid-feedback fw-semibold mt-1" style="font-size: 0.75rem; color: #FF3B3C;">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password Input -->
               <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="password" class="form-label fw-bold small mb-0" style="color: #1D1D1F;">
                            Password <span style="color: #780000;">*</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="small fw-semibold text-decoration-none" style="color: #780000;">
                            Forgot password?
                        </a>
                    </div>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           placeholder="••••••••" 
                           required
                           class="form-control rounded-3 py-3 px-3 shadow-none @error('password') is-invalid @enderror"
                           style="border-color: {{ $errors->has('password') ? '#FF3B3C' : '#D1D1D6' }}; font-size: 0.875rem;">
                    
                    @error('password')
                        <div class="invalid-feedback fw-semibold mt-1" style="font-size: 0.75rem; color: #FF3B3C;">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-4 form-check">
                    <input type="checkbox" name="remember" class="form-check-input shadow-none" id="remember" style="border-color: #D1D1D6; cursor: pointer;">
                    <label class="form-check-label small fw-medium" for="remember" style="color: #6E6E73; cursor: pointer;">
                        Remember my session
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn w-100 py-3 fw-bold text-white shadow-sm" style="background-color: #780000; font-size: 0.875rem; border-radius: 0.75rem;">
                    Log In
                </button>
            </form>

        </div>

    </div>
</div>
@endsection
