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
                        <h1 class="text-5xl md:text-7xl font-bold mb-6 text-white">Vítejte na našem webu</h1>
                        <p class="text-xl max-w-2xl mx-auto opacity-90 mb-8 font-light">Místo pro vaše nápadité projekty.</p>
                        <a href="#" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-8 py-4 rounded-xl shadow-lg">Více informací</a>
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
                <div class="text-center mb-12 py-6">
                    <h2 class="text-3xl font-bold text-slate-800 mb-2">Vítejte u nás</h2>
                    <p class="text-slate-500">Stručný popis sekce</p>
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
                content: '<section class="py-12 px-6 max-w-6xl mx-auto"><div class="container">Sem přetáhněte obsah...</div></section>' 
            },
            { 
                id: 'grid-2', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 11h8V4H4v7zm0 9h8v-7H4v7zM13 4v7h8V4h-8zm0 16h8v-7h-8v7z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">2 Sloupce</div>`, 
                category: 'Prvky', 
                content: '<div class="grid md:grid-cols-2 gap-8 my-8"><div>Sloupec 1</div><div>Sloupec 2</div></div>' 
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
                id: 'text', 
                label: `
                    <svg class="gjs-block-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 9H20V11H4V9ZM4 13H20V15H4V13ZM4 17H14V19H4V17ZM4 5H20V7H4V5Z"/>
                    </svg>
                    <div class="gjs-block-label text-xs mt-1">Text</div>`, 
                category: 'Prvky', 
                content: '<p class="py-2 text-slate-700">Vložte váš text...</p>' 
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

    canvas: {
        scripts: [
            'https://cdn.tailwindcss.com'
        ]
    }
});

// Load the initial content
if (window.INITIAL_CONTENT) {
    editor.on('load', () => {
        editor.setComponents(window.INITIAL_CONTENT);

        const bodyClassMatch = window.INITIAL_CONTENT.match(/<body[^>]*class=["']([^"']*)["']/i);
        if (bodyClassMatch && bodyClassMatch[1]) {
            const wrapper = editor.getWrapper();
            if (wrapper) {
                const classes = bodyClassMatch[1].split(/\s+/).filter(Boolean);
                wrapper.addClass(classes);
            }
        }
    });
}

// Handle Save Button
const saveBtn = document.getElementById('save-btn');
if (saveBtn) {
    saveBtn.addEventListener('click', () => {
        const html = editor.getHtml();
        const css = editor.getCss();
        const meta = window.PAGE_META || {};
        
        const wrapper = editor.getWrapper();
        const bodyClasses = (wrapper && wrapper.getClasses().length) 
            ? wrapper.getClasses().join(' ') 
            : 'bg-slate-900 text-slate-100 min-h-screen flex flex-col items-center justify-center p-6';
        
        const finalHtml = `<?php
require_once __DIR__ . '/admin/includes/CMS.php';
$meta = CMS::getPageMeta();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta['title'] ?? '') ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta['description'] ?? '') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>${css}</style>
</head>
<body class="${bodyClasses}">
    ${html}
</body>
</html>`;

        fetch('save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ html: finalHtml, metadata: meta })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' || data.status === 'warning') {
                const msg = document.getElementById('status-msg');
                if (msg) {
                    msg.innerText = window.UI_LANG === 'en' ? 'Saved!' : 'Uloženo!';
                    msg.style.opacity = '1';
                    setTimeout(() => msg.style.opacity = '0', 3000);
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => alert('Error saving page.'));
    });
}
