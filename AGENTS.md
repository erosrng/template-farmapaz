# Farmapaz Theme — Project Summary

## Goal
Fully-functional cart and checkout with Frutiger Aero aesthetic: add-to-cart toast, cart quantity/remove via AJAX, dynamic shipping fee ($2 / free), navbar badge animation, two-column checkout layout, Spanish translations throughout, live cart totals update via server fragments.

## Constraints & Preferences
- Tailwind CSS with no JIT — no arbitrary `[value]` classes; use standard utilities or inline styles.
- Brand colors: green `#5A7D43`, blue `#09146E`, yellow `#FEAB0D`, red `#FF1A27`, orange `#F97316`.
- Mobile-first, responsive, futuristic glass-morphism aesthetic (Frutiger Aero 2026).
- **No emojis** — SVG icons only.
- Product images proxy through local `proxy-image.php` → `farmapazvenezuela.com`; fallback `assets/images/producto.png`.
- All product queries filter by `_stock_status = instock` + `_thumbnail_id EXISTS`.
- Products with stock ≤ 5 are hidden from carousels; products without real images are hidden.
- Two layouts: `carousel` (horizontal full-bleed scroll) or `grid` (CSS grid, 2–5 cols).

## Version
`1.0.3` — defined in `functions.php:6`

---

## Done

### Cart Totals Live Update (AJAX fragments)
- **`functions.php`**: Added `farmapaz_render_cart_totals_rows()`, `farmapaz_render_cart_total()`, `farmapaz_render_cart_items()`. Registered all three as WooCommerce fragments (`woocommerce_add_to_cart_fragments`) for keys `div.cart-totals-rows`, `div.cart-totals-total`, `div.cart-items`.
- **`cart.php`**: Replaced inline totals HTML with `<?= farmapaz_render_cart_totals_rows() ?>` and `<?= farmapaz_render_cart_total() ?>`. Added `data-unit-price` attribute to each cart item row.
- **`cart.js`**: Added `updateHeaderCount()`, `replaceFragments()`. Remove handler now replaces totals; qty handler replaces totals + entire `div.cart-items` container + re-binds qty buttons via `bindCartQty()`.
- **All amounts update live**: badge, header count, subtotal per item (`.cart-item-price`, `.cart-item-price-mobile`), cart totals rows (subtotal, descuento, envío, IVA), grand total.

### WooCommerce AJAX Notes
- `update_cart` endpoint returns empty body for guests — quantity changes use remove+add combo.
- Working endpoints: `add_to_cart`, `remove_from_cart`, `get_refreshed_fragments`.
- Nonces not required for `remove_from_cart`.

### Add-to-Cart Toast
- `wc_add_to_cart_message_html` filter returns a fixed-position toast (`farmapaz-toast`) with green checkmark, truncated product name + "añadido", and "Ver carrito" link. Slide-in animation, auto-removes after 4s. Suppresses default WooCommerce notice.

### Cart Badge
- `#cart-count` added to `woocommerce_add_to_cart_fragments` filter. Badge content replaced on every fragment refresh. CSS `cart-bounce` keyframe applied via `cartBounce()` on add-to-cart and `updated_cart_totals`.
- Badge updates on add-to-cart, remove, and quantity change — reads `#cart-count` from AJAX response fragments.
- Badge shows **total units** (`get_cart_contents_count()`).

### Cart Remove via AJAX
- `window.farmapaz_remove_from_cart(key)` sends FormData to `/?wc-ajax=remove_from_cart`. Item row slides out with CSS transition. Totals, badge, and header count update from response fragments.

### Cart Quantity Update via AJAX
- Two-step: `remove_from_cart` (old key) → `add_to_cart` (same product_id + new qty). Both endpoints confirmed returning JSON fragments. Items container re-rendered from server fragment; qty buttons re-bound.

### Dynamic Shipping Fee
- `woocommerce_cart_calculate_fees`: "Envío gratis" ($0) when subtotal ≥ $10, "Envío" ($2) when subtotal < $10. Removes prior fee before adding.

### Checkout Two-Column Layout
- `checkout-fields-col` (billing + shipping + notes) vs `checkout-review-col` (order review + payment + confirm button). Sticky right column, responsive flex → column on <1024px.
- `.form-row-first`/`.form-row-last` (48% width, float left/right) on billing/shipping fields. Mobile: full width.
- CSS in `shop.css` (lines ~1486–1650). Grid override removed from `tailwind.css` and `app.css`.

### Image Speed Optimizations
- `content-product.php`: changed from `'full'` to `'woocommerce_thumbnail'` (300×300).
- `<link rel="preconnect">` and `<link rel="dns-prefetch">` for `farmapazvenezuela.com` in `<head>`.

### Spanish Translations
- `gettext` filter maps ~15 WooCommerce strings to Spanish.
- `woocommerce_cart_is_empty_message` filter returns "Tu carrito está vacío."

### Page Content Rendering
- `page.php`: base64-decodes `[vc_raw_html]` content, runs `do_shortcode()`.

### Cart Drawer + Checkout Redesign Reverted
- Cart drawer HTML (footer.php), JS (app.js), and CSS (shop.css) removed. Cart icon links to `/cart/`.
- Checkout redesign (cards, steps, icons) removed from `form-checkout.php`, `review-order.php`, `payment.php`, and `shop.css`.
- Duplicate payment section eliminated; `review-order.php` calls `wc_get_template('checkout/payment.php')` directly.

### Card Cart State Awareness (Quantity Stepper)
- Products already in cart show a quantity stepper (+/−) instead of "Añadir"/"Comprar" button on both shop (`content-product.php`) and homepage carousels/grids (`functions.php`).
- On page load: checks `WC()->cart->find_product_in_cart()` to render stepper or button.
- After AJAX add-to-cart: JS replaces button with stepper after 800ms.
- **Plus (+)**: calls `/?wc-ajax=add_to_cart` with `quantity=1` (WooCommerce consolidates).
- **Minus (−)**: >1 qty uses remove+add combo; at 0 qty removes from cart and restores button via `restoreAddBtn()`.
- Cart map fragment (`div.farmapaz-cart-map`) sent with every AJAX response, also rendered in `wp_footer` — maps `product_id → {key, qty}`.
- `bindCardSteppers()` called on initial load and inside `bindAddToCart(scope)` so dynamically loaded products get stepper binding.
- CSS stepper styles in `shop.css` with compact variant (`.cart-card-stepper-sm`) for homepage compact layout.

---

## In Progress / Blocked
- `/?wc-ajax=update_cart` returns empty body for guests — no `nopriv` hook in this WooCommerce version.
- Empty cart state after removing last item: totals show $0 but empty-state message not shown until reload (minor; acceptable).
- Card states not synced on `wc_fragment_refresh` — if product is added/removed from cart page, card buttons don't update until page reload (minor).

## Key Files
| File | Purpose |
|------|---------|
| `functions.php` | Cart fragments (+ cart map), shipping fee, Spanish gettext, render helpers, card stepper in carousels |
| `woocommerce/cart/cart.php` | Cart page template (items + totals + cross-sells) |
| `woocommerce/checkout/form-checkout.php` | Two-column checkout layout |
| `woocommerce/checkout/review-order.php` | Order review table + payment template |
| `woocommerce/checkout/payment.php` | Payment method radios + place order button |
| `woocommerce/content-product.php` | Product card with cart state awareness (stepper vs button) |
| `assets/js/cart.js` | Remove/update AJAX, badge/totals/items fragment replacement |
| `assets/js/app.js` | Add-to-cart AJAX, toast, badge bounce, card stepper +/- logic |
| `assets/js/shop.js` | Re-binds add-to-cart on dynamically loaded products |
| `assets/css/shop.css` | Checkout layout, toast, badge bounce, form rows, cart styles, card stepper |
| `assets/src/tailwind.css` | Tailwind source (grid removed for checkout) |
| `assets/css/app.css` | Compiled Tailwind (grid removed) |
| `page.php` | Base64 WPBakery content decoding |

## Critical Context
- Cart page ID 9 (`carrito`) / Checkout page ID 10 (`checkout`): content stored as WPBakery base64 in `[vc_raw_html]`.
- `farmapazData` JS object includes `homeUrl` and `cartUrl`.
- Apple animation curve: `cubic-bezier(0.16, 1, 0.3, 1)`. Cart bounce: `cubic-bezier(0.34, 1.56, 0.64, 1)`.
- Docker volume mounts theme at `/home/erosrangel/.../farmapaz-theme/`. Git repo: `https://github.com/erosrng/template-farmapaz.git` (branch `main`).
- shop.css loads only on cart/checkout/account pages.

## Next Steps
1. Test full guest checkout flow end-to-end: add → badge → qty change → remove → checkout → fill fields → payment → place order.
2. Test Firefox and Chrome for confirm-resubmission dialog (should not appear with AJAX).
3. Create banner images at 1920×550 px (center safe zone 960×400) for shop hero.
4. Verify `/carrito/` (ID 9) and `/checkout/` (ID 10) pages render correctly.
