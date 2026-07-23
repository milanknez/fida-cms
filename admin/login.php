<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/AuthManager.php';

AuthManager::initSession();

$error = '';

if (isset($_GET['logout'])) {
    AuthManager::logout();
    header('Location: login.php');
    exit;
}

if (isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Nesprávné heslo.';
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení | Fida CMS</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2222%22 fill=%22%234f46e5%22/><path d=%22M55 35a2.121 2.121 0 0 1 3 3L37 62l-7 2 2-7 21-22z%22 fill=%22none%22 stroke=%22white%22 stroke-width=%225%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22/></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 flex items-center justify-center min-h-screen text-slate-100">
    <div class="bg-slate-900 p-8 rounded-2xl shadow-2xl w-full max-w-md border border-white/5">
        <div class="text-center mb-8">
            <div class="inline-block p-4 bg-indigo-600 rounded-2xl text-white mb-4 shadow-lg shadow-indigo-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <h1 class="text-2xl font-black uppercase tracking-tight text-white">Fida CMS</h1>
            <p class="text-slate-400 text-sm mt-1">Zadejte heslo pro úpravu webu</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 text-red-400 p-3 rounded-lg mb-4 text-sm border border-red-500/20">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Heslo</label>
                <input type="password" name="password" required autofocus
                    class="w-full px-4 py-3 bg-slate-950 border border-white/10 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all text-white">
            </div>
            <button type="submit" 
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg shadow-lg shadow-indigo-600/20 active:transform active:scale-[0.98] transition-all">
                Přihlásit se
            </button>
        </form>
        <div class="mt-8 text-center text-xs text-slate-500 font-medium">
            Created by <a href="https://fidamedia.cz" target="_blank" class="hover:text-indigo-400 transition-colors underline decoration-indigo-400/30">Fidamedia.cz</a>
        </div>
    </div>
</body>
</html>
