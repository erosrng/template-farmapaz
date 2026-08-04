# Farmapaz Theme — Agent Guide

WordPress/WooCommerce theme (v `1.0.3` in `functions.php:6`). Source lives in `farmapaz-theme/`; Docker local dev in `farmapaz-local/`.

## Commands
```sh
npm run build:tailwind   # tailwindcss -i assets/src/tailwind.css -o assets/css/app.css --minify
npm run watch:tailwind   # same, with --watch
```
No test/lint/typecheck commands exist.

## Local Dev
- Docker: `farmapaz-local/docker-compose.yml` — WP at `localhost:8080`, DB at `3307`, phpMyAdmin at `8081`.
- Theme mounted as volume at `/var/www/html/wp-content/themes/farmapaz-theme`.
- DB table prefix: `Q0i1dfcrc_`. WP debug enabled. Initial DB from gzipped SQL backup.

## Architecture
- **Entrypoint**: `functions.php` (~2000 lines) — enqueues all assets, cart fragments, shipping fee, Spanish `gettext`, Pastillín AI chat, card steppers.
- **Assets**: CSS via `assets/src/tailwind.css` → `assets/css/app.css` (+ `shop.css` for cart/checkout/account). JS: `app.js` (add-to-cart, toast, steppers), `cart.js` (remove/qty AJAX, fragment replacement), `shop.js` (re-bind on dynamic products), `pastillin.js` (chat), `product.js`.
- **WooCommerce overrides**: `woocommerce/content-product.php`, `cart/cart.php`, `checkout/`, `single-product.php`.
- **Image proxy**: `proxy-image.php?url=...` caches to `cache-images/` (24h TTL). Proxy domain: `farmapazvenezuela.com`. Fallback: `assets/images/producto.png`.
- **Nav walker**: `inc/nav-walker.php`.

## Conventions
- **No arbitrary Tailwind classes** — no JIT, no `[value]` syntax. Use standard utilities or inline styles.
- **No emojis** — SVG icons only.
- **Brand colors** (use as `text-brand-green` etc): green `#5A7D43`, blue `#09146E`, yellow `#FEAB0D`, red `#FF1A27`, orange `#F97316`.
- **Font**: Inter (loaded from Google Fonts in header).
- **Animation curve**: `cubic-bezier(0.16, 1, 0.3, 1)` (Apple-ish). Cart bounce: `cubic-bezier(0.34, 1.56, 0.64, 1)`.
- Product queries filter by `_stock_status = instock` + `_thumbnail_id EXISTS`. Stock ≤ 5 hidden from carousels.
- Carousel layout: horizontal full-bleed scroll. Grid layout: CSS grid 2–5 cols.

## WooCommerce AJAX Gotchas
- `/?wc-ajax=update_cart` returns empty body for guests (no `nopriv` hook). Quantity changes use **remove+add combo**: `remove_from_cart` (old key) → `add_to_cart` (same product_id + new qty).
- Working endpoints: `add_to_cart`, `remove_from_cart`, `get_refreshed_fragments`.
- Nonces not required for `remove_from_cart`.
- Controlled medications (`product_cat: medicamentos-controlados`) are not purchasable — `add_to_cart_validation` returns false.
- Cart fragments: `div.cart-totals-rows`, `div.cart-totals-total`, `div.cart-items`, `div.farmapaz-cart-map` (product_id → {key, qty}).

## Key Details
- **Page IDs**: Cart = 9 (`carrito`), Checkout = 10 (`checkout`). Content stored as WPBakery base64 in `[vc_raw_html]` blocks → decoded in `page.php`.
- **Shipping**: Dynamic fee via `woocommerce_cart_calculate_fees` — free (`$0`) if subtotal ≥ $10, otherwise `$2`.
- **Pastillín AI chatbot**: `wp_ajax_farmapaz_pastillin` / `nopriv`. Supports Gemini, OpenRouter, Groq — configured in `Ajustes > Pastillín AI`. Tries providers in order: Gemini → OpenRouter → Groq. Local replies for competitors, greetings, business info. Searches products for context.
- **`shop.css`** loads only on cart, checkout, and account pages.
- **JS data object**: `window.farmapazData` exposes `homeUrl`, `cartUrl`.
- Cart badge (`#cart-count`) shows total units via `WC()->cart->get_cart_contents_count()`.
- `page.php` base64-decodes WPBakery `[vc_raw_html]` content and runs `do_shortcode()`.

## Known Issues
- `/?wc-ajax=update_cart` broken for guests — use remove+add combo instead.
- Empty cart after removing last item: totals show `$0` but empty-state message not shown until reload.
- Card stepper buttons not synced on `wc_fragment_refresh` — manual page reload needed.

## Scraper de imágenes (farmadon.com.ve → WooCommerce)

### Estado actual (14-Jul, último sync)
| Métrica | Inicio | Ahora | Δ |
|---|---|---|---|
| Productos publicados | 8,504 | 8,501 | — |
| Con thumbnail | 6,165 (72%) | 6,453 (76%) | +288 |
| Sin thumbnail | 2,534 (28%) | 2,240 (24%) | -294 |
| Imágenes en VPS | 2,452 | 2,679 | +227 |

### Pipeline completo
```
extractor.py → sync_incremental.sh → asignar_rapido.sh (en VPS)
```

### Scripts en `~/Escritorio/robador/`
| Script | Descripción |
|---|---|
| `extractor.py` | Scraper principal (root). Timeout 15s, 5 hilos, sin sleep. Procesa `lista_links.txt` |
| `sync_incremental.sh` | Flatten → filtrar SKU → tar → SCP → extraer en VPS |
| `progreso.txt` | Progreso del scraper (categoría,página) |
| `progreso_express.txt` | Progreso de intentos rápidos (no prioritario) |
| `imagenes_farmadon/` | Raw output (root-owned: `drwxr-xr-x root root`) |
| `imagenes_planas/` | Flattened únicos por SKU (4184 archivos) |

### Comandos rápidos
```sh
# 1. Scrapear categorías pendientes (correr como root)
cd ~/Escritorio/robador && python3 extractor.py

# 2. Sincronizar al VPS (filtra SKU, upload, import)
bash sync_incremental.sh

# 3. Ver progreso del scraper
cat ~/Escritorio/robador/progreso.txt

# 4. Ver estado en VPS (thumbnail count)
ssh farmapaz@185.27.135.252 "~/.local/bin/wp --path=/home/farmapaz/web/farmapazvenezuela.com/public_html db query \"SELECT COUNT(*) FROM Q0i1dfcrc_posts p LEFT JOIN Q0i1dfcrc_postmeta pm ON p.ID=pm.post_id AND pm.meta_key='_thumbnail_id' WHERE p.post_type='product' AND p.post_status='publish' AND (pm.meta_value IS NULL OR pm.meta_value='')\" --skip-column-names"
```

### Flujo de trabajo
1. **Scrapear**: `python3 extractor.py` (como root). Procesa todas las categorías de `lista_links.txt`. Script optimizado con timeout 15s, 5 hilos paralelos.
2. **Sync**: `bash sync_incremental.sh` — aplana (SKU único), filtra imágenes cuyo SKU existe en WooCommerce, comprime, SCP al VPS, extrae.
3. **Asignar**: En el VPS, el script sube cada imagen como attachment y asigna `_thumbnail_id` al producto correspondiente. Usa SQL para pre-filtrar solo productos sin thumbnail + con imagen disponible.
4. **Verificar**: Consultar estado en VPS (comando #4 arriba).

### Detalles técnicos
- **SKU mismatch**: El scraper extrae el SKU que muestra farmadon.com.ve (puede ser ID interno como `0000000002073` o barcode como `7591519051698`). Las imágenes se guardan con ese nombre. WooCommerce en farmapaz tiene ambos formatos de SKU. El script de asignación matchea por nombre de archivo vs `_sku` en BD.
- **Permisos root**: `imagenes_farmadon/` es root-owned. Cualquier script que escriba ahí debe correr como root.
- **Import en paralelo**: El VPS usa background jobs (4 simultáneos) con `wp media import --porcelain` + `wp post meta update`.
- **Log de importación**: `/tmp/import_rapido.log` en VPS (293 OK, 0 FAIL en última corrida).

### Categorías pendientes (16)
```
salud-cardiovascular, salud-digestiva, salud-respiratoria, salud-sexual
jeringas, alimentos-infantiles, cosmeticos, dermocosmeticos
alimentos, bebidas, confiteria, electronicos, fitness-y-dietetica, hogar-y-mas, mascotas, papeleria
```
El scraper las procesa automáticamente al llegar a ellas en `lista_links.txt`.

### Asignación directa en VPS (si ya hay imágenes)
```sh
ssh farmapaz@185.27.135.252 "export WP_CLI_ALLOW_ROOT=1 && bash /tmp/asignar_rapido.sh"
```
Script en VPS en `/tmp/asignar_rapido.sh` (necesita recrearse si se pierde).

## Push Notifications (OneSignal)

**Integración OneSignal Web Push** — sin plugin externo, todo en el theme.

### Archivos
- `onesignal/OneSignalSDKWorker.js` / `OneSignalSDKUpdaterWorker.js` — service workers (proxy via WordPress rewrite rules, `functions.php:2576-2603`).
- `onesignal/manifest.json` — PWA manifest para iOS Safari.
- `assets/js/notifications.js` — modal custom con branding Farmapaz, init de OneSignal, manejo de cookies.
- `functions.php:103-117` — enqueue condicional (solo si hay App ID configurado).
- `functions.php:2567-2823` — admin page, rewrite rules, AJAX tagging, test notification.

### Admin
`Ajustes > Notificaciones Push` — campos: App ID, REST API Key, retraso del modal (ms).

### Comportamiento
1. Modal aparece tras N ms (`notif_delay`, default 8000) en la primera interacción del usuario.
2. Botón "Sí, quiero recibir ofertas" → OneSignal subscribe + envía tags al servidor.
3. Tags: `has_purchased`, `order_count`, `purchased_categories`, `last_purchase`, `subscribed_date`.
4. "Ahora no" → cookie 7 días; "No volver a preguntar" → cookie 365 días.
5. Si ya aceptó antes o el permiso está concedido, no muestra modal.

### Segmentación en OneSignal Dashboard
Usar tags para campañas:
- `has_purchased = yes` → clientes recurrentes
- `purchased_categories` → filtrar por categorías compradas
- `order_count > 5` → clientes frecuentes

### Test
En `Ajustes > Notificaciones Push` hay un formulario para enviar notificación de prueba a "Todos los suscriptores" o "Subscribed Users".

### Setup inicial
1. Crear cuenta en https://onesignal.com
2. Crear app Web → copiar **App ID** y **REST API Key**
3. Ir a `Ajustes > Notificaciones Push` en WordPress y pegarlos
4. Ir a `Ajustes > Enlaces Permamentes` y dar clic en "Guardar cambios" (flush rewrite rules)
