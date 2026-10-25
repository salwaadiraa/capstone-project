<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login - Reimburse BBM</title>
    <script src="{{ asset('js/tailwind.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}">
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-lg border border-slate-100 w-96">
        <h2 class="text-2xl font-extrabold mb-6 text-center text-slate-800 flex items-center justify-center gap-2">
            <i class="fa-solid fa-wallet text-blue-600"></i> REIMBURSE-APP
        </h2>
        
        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm font-medium">{{ $errors->first() }}</div>
        @endif

        <form action="/login" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-bold mb-1 text-slate-700">Username</label>
                <input type="text" name="username" class="w-full border p-2.5 rounded-lg" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-bold mb-1 text-slate-700">Password</label>
                <input type="password" name="password" class="w-full border p-2.5 rounded-lg" required>
            </div>
            <button class="w-full bg-blue-600 text-white p-2.5 rounded-lg hover:bg-blue-700 font-bold transition shadow-md">
                <i class="fa-solid fa-right-to-bracket mr-1"></i> Masuk
            </button>
        </form>
        <div class="mt-4 text-xs text-slate-500 text-center">
            Akun Demo: admin / pegawai / spv / finance (Pass: 123456)
        </div>
    </div>
</body>
</html>