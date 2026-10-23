<!DOCTYPE html>
<html>
<head>
    <title>Login - Reimburse BBM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Login Reimburse BBM</h2>
        
        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">{{ $errors->first() }}</div>
        @endif

        <form action="/login" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-bold mb-1">Username</label>
                <input type="text" name="username" class="w-full border p-2 rounded" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-bold mb-1">Password</label>
                <input type="password" name="password" class="w-full border p-2 rounded" required>
            </div>
            <button class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 font-bold">Masuk</button>
        </form>
        <div class="mt-4 text-xs text-gray-500 text-center">
            Akun Demo: admin / pegawai / spv / finance (Pass: 123456)
        </div>
    </div>
</body>
</html>