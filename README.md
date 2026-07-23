# Fida CMS

Samostatná administrace a CMS engine pro rychlou tvorbu a správu webových stránek.

## Struktura Fida CMS
- `admin/` – Vizuální editor (GrapesJS) a správa obsahu webu.
- `includes/CMS.php` – PHP Engine pro zpracování meta tagů, zkrácených URL a kešování.

## Jak připojit k novému webu (jako Git Submodule)

1. V kořenové složce vašeho webu přidejte submodule:
   ```bash
   git submodule add https://github.com/milanknez/fida-cms.git admin
   ```

2. Vytvořte složku `includes/` ve vašem webu a načtěte CMS engine:
   ```php
   <?php
   require_once __DIR__ . '/admin/includes/CMS.php';
   ```

3. Nastavte konfiguraci v `admin/config.php`:
   - `ADMIN_PASSWORD` pro přístup do administrace.
   - `REPO_URL` pro kontrolu aktualizací CMS.

## Aktualizace Fida CMS
Na jakémkoliv klientském webu aktualizujete CMS jednoduše příkazem:
```bash
git submodule update --remote --merge
```
nebo stiskem tlačítka **Aktualizace** přímo v rozhraní administrace.
