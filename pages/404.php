<?php
require_once __DIR__ . '/../admin/includes/CMS.php';
CMS::getHeader();
?>
<style>* { box-sizing: border-box; } body {margin: 0;}#ijcl{font-size:5rem;color:var(--primary);}#ivgit{margin:2rem 0;font-size:1.2rem;}</style>
<body class="bg-slate-950 text-slate-100 min-h-screen"><main><section class="section-padding"><div class="container text-center"><div class="mb-8"><h1 class="text-6xl font-serif text-[var(--primary)] mb-4" id="ijcl">404</h1><h2 class="section-title">Stránka nenalezenaq</h2></div><p class="text-xl mb-10 opacity-80" id="ivgit">
                    Jejda, tady nic není 👀<br/>
                    Stránka mohla být přesunuta nebo smazána.<br/>
                    Zkuste se vrátit zpět nebo přejít na úvod.
                </p><div class="flex justify-center gap-4"><a href="/" class="btn btn-primary px-8">ZPĚT NA ÚVOD</a></div></div></section></main><!--?php CMS::getFooter(); ?--></body>
<?php
CMS::getFooter();
?>