/**
 * Fida editor Configuration
 */

const editor = grapesjs.init({
    container: '#gjs',
    fromElement: false,
    height: '100%',
    width: 'auto',
    storageManager: false,
    
    panels: {
        defaults: [
            {
                id: 'options',
                el: '#panel-actions',
                buttons: [
                    { id: 'sw-visibility', active: true, icon: 'fa fa-square-o', command: 'sw-visibility' },
                    { id: 'export-template', icon: 'fa fa-code', command: 'export-template' }
                ]
            }
        ]
    },
    
    // I18N Handling
    i18n: {
        locale: window.UI_LANG || 'cs',
        detectLocale: false,
        messages: {
            cs: {
                blockManager: {
                    labels: { 'Sekce': 'Sekce a Rozvržení', 'Obsah': 'Základní prvky' },
                    categories: { 'Sekce': 'Sekce', 'Prvky': 'Základní prvky' }
                },
                styleManager: {
                    sectors: {
                        'general': 'Obecné',
                        'layout': 'Rozvržení',
                        'typography': 'Typografie',
                        'decorations': 'Vzhled',
                        'extra': 'Ostatní',
                        'flex': 'Uspořádání (Flex)',
                        'dimension': 'Rozměry'
                    },
                    properties: {
                        'float': 'Obtékání',
                        'display': 'Zobrazení',
                        'position': 'Pozice',
                        'top': 'Shora',
                        'right': 'Zprava',
                        'left': 'Zleva',
                        'bottom': 'Zespoda',
                        'width': 'Šířka',
                        'height': 'Výška',
                        'max-width': 'Max. šířka',
                        'min-height': 'Min. výška',
                        'margin': 'Vnější okraj',
                        'padding': 'Vnitřní okraj',
                        'font-family': 'Písmo',
                        'font-size': 'Velikost',
                        'font-weight': 'Tloušťka',
                        'letter-spacing': 'Rozestup',
                        'color': 'Barva textu',
                        'line-height': 'Výška řádku',
                        'text-align': 'Zarovnání',
                        'background-color': 'Barva pozadí',
                        'border-radius': 'Zaoblení',
                        'border': 'Okraj',
                        'opacity': 'Průhlednost'
                    }
                },
                traitManager: {
                    traits: {
                        labels: {
                            'id': 'ID pr.',
                            'title': 'Titulek',
                            'href': 'Odkaz (URL)',
                            'target': 'Cíl',
                            'src': 'Zdroj obr.',
                            'alt': 'Popis (Alt)'
                        }
                    }
                }
            }
        }
    },

    styleManager: { appendTo: '#styles-container' },
    traitManager: { appendTo: '#traits-container' },
    layerManager: { appendTo: '#layers-container' },
    assetManager: {
        embedAsBase64: false,
        autoAdd: true,
        showUrlInput: true,
        openAssetsOnDrop: true,
        upload: 'files.php?action=upload',
        uploadName: 'files[]',
        uploadFile: function(e) {
            const files = e.dataTransfer ? e.dataTransfer.files : e.target.files;
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            fetch('files.php?action=upload', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const addedAssets = (res.data || []).concat(
                        (res.uploaded || []).map(p => ({
                            src: (p.startsWith('http') || p.startsWith('data:')) ? p : '../' + p,
                            type: 'image'
                        }))
                    );
                    editor.AssetManager.add(addedAssets);
                    if (typeof showToast === 'function') {
                        showToast(res.message || 'Obrázek nahrán!');
                    }
                } else {
                    alert(res.message || 'Chyba při nahrávání.');
                }
            })
            .catch(err => alert('Chyba při nahrávání souboru.'));
        },
        assets: []
    },
    
    blockManager: { 
        appendTo: '#blocks-container',
        blocks: [
            // --- SEKCE ---
            {
                id: 'section-hero',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 3H3C1.9 3 1 3.9 1 5V19C1 20.1 1.9 21 3 21H21C22.1 21 23 20.1 23 19V5C23 3.9 22.1 3 21 3ZM21 19H3V5H21V19ZM8 17H16V15H8V17ZM5 13H19V11H5V13ZM8 9H16V7H8V9Z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Hero Sekce</div>`,
                category: { id: 'Sekce', label: 'Sekce a Kompletní bloky', open: true },
                content: `
                <section class="relative py-24 px-6 bg-slate-900 text-white overflow-hidden border-b border-slate-800">
                    <div class="absolute inset-0 bg-gradient-to-r from-amber-500/10 via-transparent to-indigo-500/10 pointer-events-none"></div>
                    <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-12 items-center relative z-10">
                        <div class="space-y-6">
                            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <i class="fa fa-bolt"></i> Nonstop Elektrikář Plzeň
                            </span>
                            <h1 class="text-4xl md:text-6xl font-black tracking-tight leading-tight text-white">
                                Rychlý & Spolehlivý <span class="text-amber-400">Elektroservis</span>
                            </h1>
                            <p class="text-lg text-slate-300 leading-relaxed">
                                Kompletní elektromontáže, havarijní služby 24/7, revize i opravy v Plzni a širokém okolí. Přijedeme do 45 minut.
                            </p>
                            <div class="flex flex-wrap gap-4 pt-2">
                                <a href="tel:+420777123456" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-black px-8 py-4 rounded-xl shadow-xl shadow-amber-500/20 transition-all flex items-center gap-3">
                                    <i class="fa fa-phone text-lg"></i> Volat Pohotovost
                                </a>
                                <a href="sluzby.php" class="bg-slate-800 hover:bg-slate-700 text-white font-bold px-8 py-4 rounded-xl border border-white/10 transition-all">
                                    Naše Služby
                                </a>
                            </div>
                        </div>
                        <div class="bg-slate-800/80 p-8 rounded-3xl border border-white/10 shadow-2xl space-y-6">
                            <h3 class="text-xl font-bold text-white border-b border-white/10 pb-4">Rychlé objednání servisu</h3>
                            <form class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Jméno a Příjmení</label>
                                    <input type="text" placeholder="Jan Novák" class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white text-sm outline-none focus:border-amber-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Telefonní číslo</label>
                                    <input type="tel" placeholder="+420 777 000 000" class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white text-sm outline-none focus:border-amber-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Stručný popis závady</label>
                                    <textarea rows="3" placeholder="Potřebuji zapojit desku / výpadek proudu..." class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white text-sm outline-none focus:border-amber-500"></textarea>
                                </div>
                                <button type="button" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 rounded-xl shadow-lg transition-all text-sm uppercase">Odeslat Poptávku</button>
                            </form>
                        </div>
                    </div>
                </section>`
            },
            {
                id: 'section-emergency',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L1 21h22L12 2zm1 14h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">24/7 Pohotovost Banner</div>`,
                category: { id: 'Sekce', label: 'Sekce a Kompletní bloky', open: true },
                content: `
                <section class="py-12 px-6 bg-gradient-to-r from-amber-600 to-amber-500 text-slate-950 my-8 rounded-3xl max-w-6xl mx-auto shadow-2xl">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="space-y-2 text-center md:text-left">
                            <span class="bg-slate-950 text-amber-400 font-extrabold text-[10px] uppercase px-3 py-1 rounded-full tracking-wider">Havarijní výjezdy Plzeň</span>
                            <h2 class="text-3xl md:text-4xl font-black tracking-tight text-slate-950">Máte výpadek proudu nebo zkrat?</h2>
                            <p class="text-slate-900 font-medium">Jsme v pohotovosti 24 hodin denně, 7 dní v týdnu. Dojezd po Plzni do 45 minut.</p>
                        </div>
                        <a href="tel:+420777123456" class="bg-slate-950 hover:bg-slate-900 text-amber-400 font-black text-xl px-8 py-5 rounded-2xl shadow-xl transition-all flex items-center gap-3 shrink-0">
                            <i class="fa fa-phone"></i> 777 123 456
                        </a>
                    </div>
                </section>`
            },
            {
                id: 'section-services-grid',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 11h5V4H4v7zm0 9h5v-7H4v7zM10 4v7h5V4h-5zm0 16h5v-7h-5v7zM16 4v7h5V4h-5zm0 16h5v-7h-5v7z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Karty Služeb (3)</div>`,
                category: { id: 'Sekce', label: 'Sekce a Kompletní bloky', open: true },
                content: `
                <section class="py-16 px-6 max-w-6xl mx-auto">
                    <div class="text-center max-w-2xl mx-auto mb-12 space-y-3">
                        <span class="text-amber-500 font-bold text-xs uppercase tracking-wider">Co pro vás děláme</span>
                        <h2 class="text-3xl font-black text-white">Naše Hlavní Služby</h2>
                        <p class="text-slate-400 text-sm">Zajišťujeme kompletní elektroinstalační práce s garancí kvality a odborné revize.</p>
                    </div>
                    <div class="grid md:grid-cols-3 gap-8">
                        <div class="bg-slate-900 border border-white/10 p-8 rounded-2xl space-y-4 hover:border-amber-500/50 transition-all">
                            <div class="w-14 h-14 bg-amber-500/10 text-amber-400 rounded-2xl flex items-center justify-center text-2xl font-bold">
                                <i class="fa fa-bolt"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white">Havarijní Služba 24/7</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">Rychlé řešení zkratů, výpadků jističů a poruch v bytech i komerčních objektech.</p>
                            <a href="sluzby.php" class="inline-flex items-center gap-2 text-amber-400 font-bold text-xs uppercase tracking-wider hover:gap-3 transition-all">Zjistit více <i class="fa fa-arrow-right"></i></a>
                        </div>
                        <div class="bg-slate-900 border border-white/10 p-8 rounded-2xl space-y-4 hover:border-amber-500/50 transition-all">
                            <div class="w-14 h-14 bg-amber-500/10 text-amber-400 rounded-2xl flex items-center justify-center text-2xl font-bold">
                                <i class="fa fa-home"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white">Elektroinstalace & Rekonstrukce</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">Nové rozvody v novostavbách, výměny starých hliníkových kabelů a modernizace rozvaděčů.</p>
                            <a href="sluzby.php" class="inline-flex items-center gap-2 text-amber-400 font-bold text-xs uppercase tracking-wider hover:gap-3 transition-all">Zjistit více <i class="fa fa-arrow-right"></i></a>
                        </div>
                        <div class="bg-slate-900 border border-white/10 p-8 rounded-2xl space-y-4 hover:border-amber-500/50 transition-all">
                            <div class="w-14 h-14 bg-amber-500/10 text-amber-400 rounded-2xl flex items-center justify-center text-2xl font-bold">
                                <i class="fa fa-plug"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white">Zapojení Spotřebičů & Revize</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">Odborné zapojení varných desek, trub a bojlerů s razítkem pro záruční list a oficiální revize.</p>
                            <a href="sluzby.php" class="inline-flex items-center gap-2 text-amber-400 font-bold text-xs uppercase tracking-wider hover:gap-3 transition-all">Zjistit více <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </section>`
            },
            {
                id: 'section-stats',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2V9h2v8zm4 0h-2V7h2v10zm-8 0H6v-4h2v4z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Počítadla / Statistiky</div>`,
                category: { id: 'Sekce', label: 'Sekce a Kompletní bloky', open: true },
                content: `
                <section class="py-12 bg-slate-900/80 border-y border-white/10 my-8">
                    <div class="max-w-6xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                        <div class="space-y-1">
                            <div class="text-4xl font-black text-amber-400">12+</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Let praxe v oboru</div>
                        </div>
                        <div class="space-y-1">
                            <div class="text-4xl font-black text-amber-400">1 500+</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Spokojených zákazníků</div>
                        </div>
                        <div class="space-y-1">
                            <div class="text-4xl font-black text-amber-400">45 min</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Průměrný dojezd v Plzni</div>
                        </div>
                        <div class="space-y-1">
                            <div class="text-4xl font-black text-amber-400">100%</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Garance kvality & NV 194</div>
                        </div>
                    </div>
                </section>`
            },
            {
                id: 'section-pricing',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Tabulka Ceníku</div>`,
                category: { id: 'Sekce', label: 'Sekce a Kompletní bloky', open: true },
                content: `
                <section class="py-16 px-6 max-w-5xl mx-auto">
                    <div class="text-center max-w-xl mx-auto mb-12 space-y-2">
                        <span class="text-amber-500 font-bold text-xs uppercase tracking-wider">Transparentní Ceny</span>
                        <h2 class="text-3xl font-black text-white">Orientační Ceník Služeb</h2>
                    </div>
                    <div class="bg-slate-900 border border-white/10 rounded-2xl overflow-hidden shadow-2xl">
                        <div class="divide-y divide-white/5">
                            <div class="p-5 flex items-center justify-between hover:bg-slate-800/50 transition-colors">
                                <div>
                                    <div class="font-bold text-white text-base">Hodinová sazba elektrikáře</div>
                                    <div class="text-xs text-slate-400">Běžné elektroinstalační práce v pracovní době</div>
                                </div>
                                <div class="text-amber-400 font-black text-lg">od 550 Kč / hod</div>
                            </div>
                            <div class="p-5 flex items-center justify-between hover:bg-slate-800/50 transition-colors">
                                <div>
                                    <div class="font-bold text-white text-base">Zapojení varné desky / trouby</div>
                                    <div class="text-xs text-slate-400">Včetně potvrzení záručního listu</div>
                                </div>
                                <div class="text-amber-400 font-black text-lg">800 – 1 200 Kč</div>
                            </div>
                            <div class="p-5 flex items-center justify-between hover:bg-slate-800/50 transition-colors">
                                <div>
                                    <div class="font-bold text-white text-base">Výjezd 24/7 Havarijní pohotovost</div>
                                    <div class="text-xs text-slate-400">Plzeň město – paušální poplatek za výjezd</div>
                                </div>
                                <div class="text-amber-400 font-black text-lg">od 950 Kč</div>
                            </div>
                        </div>
                    </div>
                </section>`
            },
            {
                id: 'section-testimonials',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Reference & Hodnocení</div>`,
                category: { id: 'Sekce', label: 'Sekce a Kompletní bloky', open: true },
                content: `
                <section class="py-16 px-6 max-w-6xl mx-auto">
                    <div class="text-center max-w-xl mx-auto mb-12 space-y-2">
                        <span class="text-amber-500 font-bold text-xs uppercase tracking-wider">Co o nás říkají klienti</span>
                        <h2 class="text-3xl font-black text-white">Reference Zákazníků</h2>
                    </div>
                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="bg-slate-900 border border-white/10 p-8 rounded-2xl space-y-4">
                            <div class="text-amber-400 flex gap-1 text-sm">
                                <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                            </div>
                            <p class="text-slate-300 text-sm italic leading-relaxed">"Pán přijel v neděli večer do 40 minut od zavolání. Rychle našel zkrat v rozvaděči a vše opravil. Skvělá domluva a férová cena!"</p>
                            <div class="pt-4 border-t border-white/5 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-sm">MK</div>
                                <div>
                                    <div class="font-bold text-white text-sm">Martin K.</div>
                                    <div class="text-xs text-slate-500">Plzeň - Slovany</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-900 border border-white/10 p-8 rounded-2xl space-y-4">
                            <div class="text-amber-400 flex gap-1 text-sm">
                                <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                            </div>
                            <p class="text-slate-300 text-sm italic leading-relaxed">"Kompletní nová elektroinstalace v bytě 3+1. Práce proběhla přesně podle rozpočtu i časového harmonogramu. Mohu jen doporučit."</p>
                            <div class="pt-4 border-t border-white/5 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-sm">JP</div>
                                <div>
                                    <div class="font-bold text-white text-sm">Jana P.</div>
                                    <div class="text-xs text-slate-500">Plzeň - Bory</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>`
            },
            {
                id: 'section-faq',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 16h-2v-2h2v2zm1.07-7.75l-.9.92C12.45 11.9 12 12.5 12 14h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Časté Dotazy (FAQ)</div>`,
                category: { id: 'Sekce', label: 'Sekce a Kompletní bloky', open: true },
                content: `
                <section class="py-16 px-6 max-w-4xl mx-auto space-y-8">
                    <div class="text-center space-y-2">
                        <span class="text-amber-500 font-bold text-xs uppercase tracking-wider">Otázky & Odpovědi</span>
                        <h2 class="text-3xl font-black text-white">Často Kladené Dotazy</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-slate-900 border border-white/10 p-6 rounded-2xl space-y-2">
                            <h4 class="font-bold text-white text-base">Za jak dlouho dorazíte při havarijním výjezdu?</h4>
                            <p class="text-slate-400 text-sm">V rámci města Plzně přijíždíme obvykle do 30 až 45 minut od telefonického nahlášení výpadku.</p>
                        </div>
                        <div class="bg-slate-900 border border-white/10 p-6 rounded-2xl space-y-2">
                            <h4 class="font-bold text-white text-base">Potvrzujete záruční listy při zapojení spotřebičů?</h4>
                            <p class="text-slate-400 text-sm">Ano, naši elektrikáři mají platnou kvalifikaci dle NV 194/2022 Sb. a potvrdí vám záruční list s razítkem.</p>
                        </div>
                    </div>
                </section>`
            },

            // --- ZÁKLADNÍ PRVKY & LAYOUT ---
            { 
                id: 'grid-2', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 11h8V4H4v7zm0 9h8v-7H4v7zM13 4v7h8V4h-8zm0 16h8v-7h-8v7z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">2 Sloupce</div>`, 
                category: { id: 'Prvky', label: 'Základní prvky', open: true }, 
                content: '<div class="grid md:grid-cols-2 gap-8 my-8"><div class="bg-slate-900/50 border border-dashed border-white/20 p-6 rounded-xl">Sloupec 1</div><div class="bg-slate-900/50 border border-dashed border-white/20 p-6 rounded-xl">Sloupec 2</div></div>' 
            },
            { 
                id: 'grid-3', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 11h5V4H4v7zm0 9h5v-7H4v7zM10 4v7h5V4h-5zm0 16h5v-7h-5v7zM16 4v7h5V4h-5zm0 16h5v-7h-5v7z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">3 Sloupce</div>`, 
                category: { id: 'Prvky', label: 'Základní prvky', open: true }, 
                content: '<div class="grid md:grid-cols-3 gap-6 my-8"><div class="bg-slate-900/50 border border-dashed border-white/20 p-6 rounded-xl">Sloupec 1</div><div class="bg-slate-900/50 border border-dashed border-white/20 p-6 rounded-xl">Sloupec 2</div><div class="bg-slate-900/50 border border-dashed border-white/20 p-6 rounded-xl">Sloupec 3</div></div>' 
            },
            { 
                id: 'grid-4', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 11h4.2V4H3v7zm0 9h4.2v-7H3v7zM8.4 4v7h4.2V4H8.4zm0 16h4.2v-7H8.4v7zM13.8 4v7h4.2V4h-4.2zm0 16h4.2v-7h-4.2v7zM19.2 4v7H23.5V4h-4.3zm0 16h4.3v-7h-4.3v7z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">4 Sloupce</div>`, 
                category: { id: 'Prvky', label: 'Základní prvky', open: true }, 
                content: '<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 my-8"><div class="bg-slate-900/50 border border-dashed border-white/20 p-4 rounded-xl">Sloupec 1</div><div class="bg-slate-900/50 border border-dashed border-white/20 p-4 rounded-xl">Sloupec 2</div><div class="bg-slate-900/50 border border-dashed border-white/20 p-4 rounded-xl">Sloupec 3</div><div class="bg-slate-900/50 border border-dashed border-white/20 p-4 rounded-xl">Sloupec 4</div></div>' 
            },
            {
                id: 'heading-h1',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 4v3h5.5v12h3V7H19V4H5z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Velký Nadpis H1</div>`,
                category: { id: 'Prvky', label: 'Základní prvky', open: true },
                content: '<h1 class="text-4xl md:text-5xl font-black text-white tracking-tight my-4">Váš Hlavní Titulek Stránky</h1>'
            },
            {
                id: 'section-title',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 4V7H10.5V19H13.5V7H19V4H5Z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Nadpis s Podnadpisem</div>`,
                category: { id: 'Prvky', label: 'Základní prvky', open: true },
                content: `
                <div class="text-center my-8 py-4">
                    <span class="text-amber-500 font-bold text-xs uppercase tracking-wider">Podnadpis</span>
                    <h2 class="text-3xl font-black text-white mt-1">Hlavní nadpis sekce</h2>
                </div>`
            },
            {
                id: 'button-cta',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 6H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 10H5V8h14v8z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">CTA Tlačítko</div>`,
                category: { id: 'Prvky', label: 'Základní prvky', open: true },
                content: '<a href="#" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black px-8 py-3.5 rounded-xl shadow-lg transition-all text-sm uppercase">Kontaktovat Elektrikáře <i class="fa fa-arrow-right"></i></a>'
            },
            {
                id: 'card-box',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Karta / Box</div>`,
                category: { id: 'Prvky', label: 'Základní prvky', open: true },
                content: `
                <div class="bg-slate-900 border border-white/10 p-8 rounded-2xl space-y-4 my-6 shadow-xl">
                    <h3 class="text-xl font-bold text-white">Titulek Karty</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Vložte libovolný popis nebo obsah do této stylové karty.</p>
                </div>`
            },
            {
                id: 'icon-list',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Seznam s Ikonami</div>`,
                category: { id: 'Prvky', label: 'Základní prvky', open: true },
                content: `
                <ul class="space-y-3 text-slate-300 text-sm my-6">
                    <li class="flex items-center gap-3"><i class="fa fa-check-circle text-amber-400 text-base"></i> Certifikovaní elektrikáři s licencí</li>
                    <li class="flex items-center gap-3"><i class="fa fa-check-circle text-amber-400 text-base"></i> Bezplatná kalkulace a obhlídka</li>
                    <li class="flex items-center gap-3"><i class="fa fa-check-circle text-amber-400 text-base"></i> Záruka 36 měsíců na veškeré práce</li>
                </ul>`
            },
            { 
                id: 'text', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 9H20V11H4V9ZM4 13H20V15H4V13ZM4 17H14V19H4V17ZM4 5H20V7H4V5Z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Textový odstavec</div>`, 
                category: { id: 'Prvky', label: 'Základní prvky', open: true }, 
                content: '<p class="py-2 text-slate-300 leading-relaxed text-sm">Zde napište váš textový obsah. Můžete libovolně upravovat styly i zarovnání.</p>' 
            },
            { 
                id: 'image', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 19V5C21 3.9 20.1 3 19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 23 20.1 23 19ZM8.5 13.5L11 16.51L14.5 12L19 18H5L8.5 13.5Z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Obrázek</div>`, 
                category: { id: 'Prvky', label: 'Základní prvky', open: true }, 
                content: { type: 'image' } 
            },
            {
                id: 'badge-tag',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Štítek / Odznak</div>`,
                category: { id: 'Prvky', label: 'Základní prvky', open: true },
                content: '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-wider"><i class="fa fa-bolt"></i> Nonstop Služba</span>'
            },
            {
                id: 'quote-box',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 17h3l2-4V7H5v6h3l-2 4zm8 0h3l2-4V7h-6v6h3l-2 4z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Citace</div>`,
                category: { id: 'Prvky', label: 'Základní prvky', open: true },
                content: `
                <blockquote class="border-l-4 border-amber-500 pl-6 py-3 my-6 italic text-slate-300 text-base bg-slate-900/40 rounded-r-2xl">
                    "Bezpečná elektroinstalace je základem každého domova. Spolehněte se na profesionály s odbornou licencí."
                </blockquote>`
            },
            {
                id: 'divider-line',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 11h16v2H4z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Oddělovací čára</div>`,
                category: { id: 'Prvky', label: 'Základní prvky', open: true },
                content: '<hr class="border-t border-white/10 my-10">'
            },
            {
                id: 'alert-box',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Informační Box</div>`,
                category: { id: 'Prvky', label: 'Základní prvky', open: true },
                content: `
                <div class="bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 p-5 rounded-2xl my-4 flex items-start gap-4 text-xs">
                    <i class="fa fa-info-circle text-lg shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="font-bold block text-sm text-white mb-1">Důležité Upozornění</strong>
                        Provozní záruku na elektroinstalaci poskytujeme v délce 36 měsíců.
                    </div>
                </div>`
            },
            {
                id: 'video-embed',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Video (YouTube)</div>`,
                category: { id: 'Prvky', label: 'Základní prvky', open: true },
                content: `
                <div class="aspect-video w-full rounded-2xl overflow-hidden shadow-2xl my-6 border border-white/10">
                    <iframe class="w-full h-full" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>`
            },
            {
                id: 'map-embed',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Mapa (Google Maps)</div>`,
                category: { id: 'Prvky', label: 'Základní prvky', open: true },
                content: `
                <div class="aspect-[16/9] w-full rounded-2xl overflow-hidden shadow-2xl my-6 border border-white/10">
                    <iframe class="w-full h-full border-0" src="https://maps.google.com/maps?q=Plze%C5%88&t=&z=13&ie=UTF8&iwloc=&output=embed" loading="lazy"></iframe>
                </div>`
            }
        ]
    },

    canvas: {
        scripts: [
            'https://cdn.tailwindcss.com'
        ],
        styles: [
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'
        ]
    }
});

// Load the initial content and apply body classes to editor canvas
if (window.INITIAL_CONTENT !== undefined) {
    editor.on('load', () => {
        editor.setComponents(window.INITIAL_CONTENT);

        const bodyClass = window.INITIAL_BODY_CLASS || 'bg-slate-900 text-slate-100 min-h-screen flex flex-col items-center justify-center p-6';
        const wrapper = editor.getWrapper();
        if (wrapper) {
            const classes = bodyClass.split(/\s+/).filter(Boolean);
            wrapper.addClass(classes);
        }

        const syncIframeBody = () => {
            const iframeBody = editor.Canvas.getBody();
            if (iframeBody) {
                const currentWrapper = editor.getWrapper();
                const currentClasses = (currentWrapper && currentWrapper.getClasses().length)
                    ? currentWrapper.getClasses().join(' ')
                    : bodyClass;
                iframeBody.className = currentClasses;
            }
        };

        // Ensure block manager categories are opened
        const bm = editor.BlockManager;
        if (bm && bm.getCategories) {
            const categories = bm.getCategories();
            if (categories && categories.each) {
                categories.each(cat => cat.set('open', true));
            }
        }

        // Auto load images from /assets/ into GrapesJS Asset Manager
        fetch('files.php?action=list')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && Array.isArray(data.files)) {
                    const assets = data.files
                        .filter(f => f.type === 'image')
                        .map(f => {
                            const src = (f.url.startsWith('http') || f.url.startsWith('data:')) ? f.url : '../' + f.url;
                            return {
                                src: src,
                                name: f.name,
                                type: 'image'
                            };
                        });
                    if (editor && editor.AssetManager) {
                        editor.AssetManager.add(assets);
                    }
                }
            })
            .catch(err => {});

        syncIframeBody();
        setTimeout(syncIframeBody, 200);
        setTimeout(syncIframeBody, 600);

        editor.on('component:update:classes', syncIframeBody);
    });
}

// Add expand/fullscreen button to Code Modal (viewCode)
editor.on('modal:open', () => {
    const modalHeader = document.querySelector('.gjs-mdl-header');
    const modalDialog = document.querySelector('.gjs-mdl-dialog');
    if (modalHeader && modalDialog && !document.getElementById('gjs-modal-expand-btn')) {
        const expandBtn = document.createElement('button');
        expandBtn.id = 'gjs-modal-expand-btn';
        expandBtn.className = 'gjs-mdl-btn-close';
        expandBtn.style.marginRight = '8px';
        expandBtn.style.fontSize = '14px';
        expandBtn.title = 'Zvětšit / Zmenšit okno kódu';
        expandBtn.innerHTML = '<i class="fa fa-expand"></i>';
        expandBtn.onclick = () => {
            modalDialog.classList.toggle('gjs-mdl-fullscreen');
            const isFull = modalDialog.classList.contains('gjs-mdl-fullscreen');
            expandBtn.innerHTML = isFull ? '<i class="fa fa-compress"></i>' : '<i class="fa fa-expand"></i>';
        };
        const closeBtn = modalHeader.querySelector('.gjs-mdl-btn-close');
        if (closeBtn) {
            modalHeader.insertBefore(expandBtn, closeBtn);
        } else {
            modalHeader.appendChild(expandBtn);
        }
    }
});

window.editor = editor;
window.EDIT_MODE = 'page';

function showSaveMessage(msgText) {
    const msg = document.getElementById('status-msg');
    if (msg) {
        msg.innerText = msgText;
        msg.style.opacity = '1';
        setTimeout(() => msg.style.opacity = '0', 3000);
    }
}

// Handle Save Button
const saveBtn = document.getElementById('save-btn');
if (saveBtn) {
    saveBtn.addEventListener('click', () => {
        const html = editor.getHtml();
        const css = editor.getCss();

        if (window.EDIT_MODE === 'theme_header') {
            fetch('themes.php?action=save_header', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: html })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showSaveMessage(window.UI_LANG === 'en' ? 'Header Saved!' : 'Hlavička uložena!');
                } else {
                    alert('Chyba: ' + data.message);
                }
            })
            .catch(err => alert('Chyba při ukládání hlavičky.'));
            return;
        }

        if (window.EDIT_MODE === 'theme_footer') {
            fetch('themes.php?action=save_footer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: html })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showSaveMessage(window.UI_LANG === 'en' ? 'Footer Saved!' : 'Patička uložena!');
                } else {
                    alert('Chyba: ' + data.message);
                }
            })
            .catch(err => alert('Chyba při ukládání patičky.'));
            return;
        }

        const meta = window.PAGE_META || {};
        const wrapper = editor.getWrapper();
        const bodyClasses = (wrapper && wrapper.getClasses().length) 
            ? wrapper.getClasses().join(' ') 
            : 'bg-slate-900 text-slate-100 min-h-screen flex flex-col items-center justify-center p-6';
        
        const topPhp = (window.ORIGINAL_TOP_PHP && window.ORIGINAL_TOP_PHP.trim())
            ? window.ORIGINAL_TOP_PHP.trim() + "\n"
            : `<?php\nrequire_once __DIR__ . '/admin/includes/CMS.php';\nCMS::getHeader();\n?>\n`;
        
        const finalHtml = `${topPhp}${html}\n<?php\nCMS::getFooter();\n?>`;

        fetch('save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ html: finalHtml, metadata: meta })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' || data.status === 'warning') {
                showSaveMessage(window.UI_LANG === 'en' ? 'Saved!' : 'Uloženo!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => alert('Error saving page.'));
    });
}

// Auto switch tab to styles when an element is selected on canvas
editor.on('component:selected', () => {
    if (typeof window.switchRightTab === 'function') {
        const stylesBtn = document.getElementById('tab-btn-styles');
        if (stylesBtn && !stylesBtn.classList.contains('active')) {
            window.switchRightTab('styles');
        }
    }
});
