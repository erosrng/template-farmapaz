<?php
defined('ABSPATH') || exit;

do_action('woocommerce_before_my_account');
?>

<div class="farmapaz-account-wrap">
    <!-- Sidebar -->
    <aside class="farmapaz-account-sidebar">
        <div class="farmapaz-account-logo">
            <?php
            $logo_svg_path = get_template_directory() . '/assets/images/logo.svg';
            $logo_png_path = get_template_directory() . '/assets/images/logo.png';
            $logo_svg = get_template_directory_uri() . '/assets/images/logo.svg';
            $logo_png = get_template_directory_uri() . '/assets/images/logo.png';
            if (file_exists($logo_svg_path)):
            ?>
            <img src="<?= $logo_svg; ?>" alt="Farmapaz" class="farmapaz-account-logo-img">
            <?php elseif (file_exists($logo_png_path)): ?>
            <img src="<?= $logo_png; ?>" alt="Farmapaz" class="farmapaz-account-logo-img">
            <?php elseif (has_custom_logo()):
                the_custom_logo();
            endif; ?>
        </div>
        <div class="farmapaz-account-user">
            <div class="farmapaz-account-avatar">
                <?= get_avatar(get_current_user_id(), 80); ?>
            </div>
            <div class="farmapaz-account-greeting">
                <span class="farmapaz-account-hello">Hola,</span>
                <span class="farmapaz-account-name"><?= wp_get_current_user()->display_name; ?></span>
            </div>
        </div>

        <nav class="farmapaz-account-nav">
            <?php foreach (wc_get_account_menu_items() as $endpoint => $label): ?>
                <a href="<?= esc_url(wc_get_account_endpoint_url($endpoint)); ?>"
                   class="farmapaz-account-nav-item <?= wc_is_current_account_menu_item($endpoint) ? 'active' : ''; ?>">
                    <?= farmapaz_account_nav_icon($endpoint); ?>
                    <span><?= esc_html($label); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Content -->
    <main class="farmapaz-account-content">
        <?php do_action('woocommerce_account_content'); ?>
    </main>
</div>

<?php
do_action('woocommerce_after_my_account');

// Helper for nav icons
function farmapaz_account_nav_icon($endpoint) {
    $icons = [
        'dashboard'       => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        'orders'          => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
        'downloads'       => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        'edit-account'    => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
        'edit-address'    => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'payment-methods' => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
        'customer-logout' => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>',
    ];
    return $icons[$endpoint] ?? '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>';
}
