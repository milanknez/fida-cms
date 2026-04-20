<?php
require_once 'config.php';

$error = '';

if (isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Nesprávné heslo.';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení | Web editor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="bg-[#FDF5E6] flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md border border-[#E8DCC0]">
        <div class="text-center mb-8">
            <div class="inline-block p-3 bg-[#8B5E3C] rounded-full text-white mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-[#2C241E]">Web editor</h1>
            <p class="text-gray-500 text-sm">Zadejte heslo pro úpravu webu</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-500 p-3 rounded mb-4 text-sm border border-red-100">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Heslo</label>
                <input type="password" name="password" required autofocus
                    class="w-full px-4 py-3 rounded border border-gray-200 focus:border-[#8B5E3C] focus:ring-2 focus:ring-[#8B5E3C]/20 outline-none transition-all">
            </div>
            <button type="submit" 
                class="w-full bg-[#8B5E3C] text-white font-bold py-3 rounded border-b-4 border-[#5D3E28] hover:bg-[#5D3E28] transition-all">
                Přihlásit se
            </button>
        </form>
        <div class="mt-8 text-center text-xs text-gray-400 font-medium">
            Created by <a href="https://fidamedia.cz" target="_blank" class="hover:text-[#8B5E3C] transition-colors underline decoration-[#8B5E3C]/30">Fidamedia.cz</a>
        </div>
    </div>
</body>
</html>
