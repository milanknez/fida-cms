/**
 * Fida editor Configuration
 */

const editor = grapesjs.init({
    container: '#gjs',
    fromElement: false,
    height: '100%',
    width: 'auto',
    storageManager: false,
    
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
    
    blockManager: { 
        appendTo: '#blocks-container',
        blocks: [
            {
                id: 'section-hero',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 3H3C1.9 3 1 3.9 1 5V19C1 20.1 1.9 21 3 21H21C22.1 21 23 20.1 23 19V5C23 3.9 22.1 3 21 3ZM21 19H3V5H21V19ZM8 17H16V15H8V17ZM5 13H19V11H5V13ZM8 9H16V7H8V9Z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Hlavní Hero</div>`,
                category: 'Sekce',
                content: `
                <section class="relative h-[80vh] flex items-center justify-center bg-gray-800 text-white overflow-hidden">
                    <div class="absolute inset-0 bg-black/40"></div>
                    <div class="relative z-10 text-center px-4">
                        <h2 class="text-amber-400 font-serif italic text-3xl mb-4">Vítejte u nás</h2>
                        <h1 class="text-5xl md:text-8xl font-serif font-bold mb-6 text-white">Statek Penzón</h1>
                        <p class="text-xl max-w-2xl mx-auto opacity-90 mb-8 font-light italic">"Místo pro váš klid."</p>
                        <a href="#" class="btn btn-primary px-10">Více o nás</a>
                    </div>
                </section>`
            },
            {
                id: 'section-title',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 4V7H10.5V19H13.5V7H19V4H5Z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Nadpis sekce</div>`,
                category: 'Prvky',
                content: `
                <div class="text-center mb-12">
                    <span class="section-tag">Náš příběh</span>
                    <h2 class="section-title">Vítejte na Statku</h2>
                </div>`
            },
            {
                id: 'room-card',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 7h-7L10.5 2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zm0 11H4V4h5.66l2.5 5H20v9z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Pokoj (Karta)</div>`,
                category: 'Sekce',
                content: `
                <div class="room-card">
                    <div class="room-img" style="background-image: url('https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&q=80&w=800')">
                        <div class="room-price">od 1 200 Kč / noc</div>
                    </div>
                    <div class="room-info">
                        <h3>Apartmán U Lesa</h3>
                        <p>Útulný pokoj s výhledem do zahrady a soukromým vchodem.</p>
                        <div class="room-amenities">
                            <span>2 osoby</span>
                            <span>WiFi</span>
                        </div>
                        <a href="#" class="btn btn-primary">Rezervovat</a>
                    </div>
                </div>`
            },
            {
                id: 'activity-card',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm4.59-12.42L10 14.17l-2.59-2.58L6 13l4 4 8-8z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Aktivita</div>`,
                category: 'Prvky',
                content: `
                <div class="activity-card">
                    <div class="activity-icon">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                    </div>
                    <h3>Domácí snídaně</h3>
                    <p>Každé ráno pečeme čerstvý chleba z naší pece.</p>
                </div>`
            },
            {
                id: 'price-list',
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 6h16V4H4v2zm0 5h16V9H4v2zm0 5h16v-2H4v2zm0 4h16v-2H4v2z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Ceník</div>`,
                category: 'Sekce',
                content: `
                <div class="bg-white p-8 rounded shadow-lg border border-[#E8DCC0]">
                    <h3 class="text-2xl font-serif mb-6 text-center">Ceník ubytování</h3>
                    <ul class="room-inventory">
                        <li>
                            <span class="room-name">Dvoulůžkový pokoj</span>
                            <span class="font-bold">1 200 Kč</span>
                        </li>
                        <li>
                            <span class="room-name">Rodinný apartmán</span>
                            <span class="font-bold">2 400 Kč</span>
                        </li>
                        <li>
                            <span class="room-name">Přistýlka</span>
                            <span class="font-bold">400 Kč</span>
                        </li>
                    </ul>
                </div>`
            },
            { 
                id: 'section-plain', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Sekce (kontejner)</div>`, 
                category: 'Sekce', 
                content: '<section class="section-padding"><div class="container">Sem přetáhněte obsah...</div></section>' 
            },
            { 
                id: 'grid-2', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 11h8V4H4v7zm0 9h8v-7H4v7zM13 4v7h8V4h-8zm0 16h8v-7h-8v7z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">2 Sloupce</div>`, 
                category: 'Prvky', 
                content: '<div class="grid md:grid-cols-2 gap-8 my-8"><div class="p-4 bg-black/5 rounded">Sloupec 1</div><div class="p-4 bg-black/5 rounded">Sloupec 2</div></div>' 
            },
            { 
                id: 'grid-3', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 11h5V4H4v7zm0 9h5v-7H4v7zM10 4v7h5V4h-5zm0 16h5v-7h-5v7zM16 4v7h5V4h-5zm0 16h5v-7h-5v7z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">3 Sloupce</div>`, 
                category: 'Prvky', 
                content: '<div class="grid md:grid-cols-3 gap-6 my-8"><div>Sloupec 1</div><div>Sloupec 2</div><div>Sloupec 3</div></div>' 
            },
            { 
                id: 'link-arrow', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16.01 11H4v2h12.01v3L20 12l-3.99-4v3z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Odkaz s šipkou</div>`, 
                category: 'Prvky', 
                content: '<a href="#" class="btn-link">Přečíst si více <i data-lucide="arrow-right"></i></a>' 
            },
            { 
                id: 'icon-box', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Ikona</div>`, 
                category: 'Prvky', 
                content: '<div class="text-[var(--primary)] mb-4 w-12 h-12"><i data-lucide="star" style="width:100%; height:100%;"></i></div>' 
            },
            { 
                id: 'divider', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 13H5v-2h14v2z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Oddělovač</div>`, 
                category: 'Prvky', 
                content: '<hr class="my-10 border-0 h-px bg-[var(--border)]">' 
            },
            { 
                id: 'video', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9.07.8.1 1.49.1 2.09s-.03 1.29-.1 2.09c-.06.8-.15 1.43-.28 1.9-.13.47-.36.94-.7 1.41-.34.47-.79.84-1.34 1.1-.55.26-1.18.42-1.89.5-.71.08-1.58.12-2.62.12s-1.91-.04-2.62-.12c-.71-.08-1.34-.24-1.89-.5-.55-.26-1-.63-1.34-1.1-.34-.47-.57-.94-.7-1.41-.13-.47-.22-1.1-.28-1.9-.07-.8-.1-1.49-.1-2.09s.03-1.29.1-2.09c.06-.8.15-1.43.28-1.9.13-.47.36-.94.7-1.41.34-.47.79-.84 1.34-1.1.55-.26 1.18-.42 1.89-.5.71-.08 1.58-.12 2.62-.12s1.91.04 2.62.12c.71.08 1.34.24 1.89.5.55.26 1 .63 1.34 1.1.34.47.57.94.7 1.41z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Video</div>`, 
                category: 'Prvky', 
                content: '<div class="aspect-video bg-gray-200 flex items-center justify-center rounded">Zde vložte YouTube embed URL</div>' 
            },
            { 
                id: 'text', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 9H20V11H4V9ZM4 13H20V15H4V13ZM4 17H14V19H4V17ZM4 5H20V7H4V5Z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Text</div>`, 
                category: 'Prvky', 
                content: '<div class="p-4 text-gray-700">Vložte váš text...</div>' 
            },
            { 
                id: 'image', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 19V5C21 3.9 20.1 3 19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 23 20.1 23 19ZM8.5 13.5L11 16.51L14.5 12L19 18H5L8.5 13.5Z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Obrázek</div>`, 
                category: 'Prvky', 
                content: { type: 'image' } 
            }
        ]
    },

    // UI Panels for Icons
    panels: {
        defaults: [
            {
                id: 'actions',
                el: '#panel-actions',
                buttons: [
                    { id: 'sw-visibility', className: 'fa fa-eye', command: 'sw-visibility', active: true, attributes: { title: 'Zobrazit vodítka' } },
                    { id: 'undo', className: 'fa fa-undo', command: 'undo', attributes: { title: 'Zpět' } },
                    { id: 'redo', className: 'fa fa-repeat', command: 'redo', attributes: { title: 'Vpřed' } },
                    { id: 'canvas-clear', className: 'fa fa-trash', command: 'canvas-clear', attributes: { title: 'Vyčistit' } }
                ]
            }
        ]
    },

    canvas: {
        scripts: [
            'https://cdn.tailwindcss.com',
            'https://unpkg.com/lucide@latest',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
        ],
        styles: [
            'https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Montserrat:wght@300;400;600&family=Pinyon+Script&display=swap',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            '../style.css'
        ]
    }
});

// Load the initial content
if (window.INITIAL_CONTENT) {
    const content = `<base href="../">` + window.INITIAL_CONTENT;
    editor.setComponents(content);
}

// Handle Save Button
document.getElementById('save-btn').addEventListener('click', () => {
    const html = editor.getHtml();
    const css = editor.getCss();
    const finalHtml = `<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penzion Statek Straňovice</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Montserrat:wght@300;400;600&family=Pinyon+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>${css}</style>
</head>
<body>
    ${html.replace(/<base[^>]*>/g, '')}
    <script src="script.js"></script>
    <script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>`;

    fetch('save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ html: finalHtml })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const msg = document.getElementById('status-msg');
            msg.innerText = window.UI_LANG === 'en' ? 'Saved!' : 'Uloženo!';
            msg.style.opacity = '1';
            setTimeout(() => msg.style.opacity = '0', 3000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => alert('Error saving.'));
});
