<?php
require_once __DIR__ . '/../admin/includes/CMS.php';
CMS::getHeader();
?>
<style>* { box-sizing: border-box; } body {margin: 0;}</style>
<body class="bg-slate-950 text-slate-100 min-h-screen"><main><section class="section-padding"><div class="container text-center"><div class="mb-8"><h1 id="ijcl" class="text-6xl font-serif text-[var(--primary)] mb-4">404</h1><h2 class="section-title">Stránka nenalezena</h2></div><p id="ivgit" class="text-xl mb-10 opacity-80">
                    Jejda, tady nic není 👀<br/>
                    Stránka mohla být přesunuta nebo smazána.<br/>
                    Zkuste se vrátit zpět nebo přejít na úvod.
                </p><div class="flex justify-center gap-4"><a href="/" class="btn btn-primary px-8">ZPĚT NA ÚVOD</a></div></div></section></main><!--?php CMS::getFooter(); ?--></body>
<?php
CMS::getFooter();
?>