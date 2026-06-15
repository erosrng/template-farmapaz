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
