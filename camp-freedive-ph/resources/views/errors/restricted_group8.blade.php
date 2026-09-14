<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice | Camp FreedivePH</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white min-h-screen flex flex-col items-center justify-center p-6 text-center select-none font-sans">
    <div class="space-y-6 max-w-md">
        <h1 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
            bawal po gr 8 di pa sya tapos hehehehhe
        </h1>
        
        <div class="pt-4 flex items-center justify-center gap-3">
            <a href="{{ route('admin.bookings.index') }}" 
               class="btn-primary px-5 py-2.5 text-sm font-bold">
                Go to Booking Management
            </a>
            <a href="{{ route('admin.payments.index') }}" 
               class="btn-secondary px-5 py-2.5 text-sm font-semibold">
                Go to Payments
            </a>
        </div>
    </div>
</body>
</html>
