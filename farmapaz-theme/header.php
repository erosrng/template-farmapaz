<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= get_template_directory_uri(); ?>/assets/images/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= get_template_directory_uri(); ?>/assets/images/apple-touch-icon.png">
    <?php wp_head(); ?>
</head>
<body <?php body_class('font-sans text-gray-900 antialiased'); ?>>

<?php wp_body_open(); ?>

<header class="sticky top-0 z-50 transition-shadow duration-300">

    <!-- Top Bar -->
    <div class="text-white text-xs" style="background: linear-gradient(to right, #09146E, #0a1a7a, #09146E);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-9 sm:h-10">
                <div class="flex items-center space-x-2 sm:space-x-5 overflow-hidden">
                    <span class="flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="hidden xs:inline">7:30 am - 9:00 pm</span>
                    </span>
                    <span class="flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="hidden sm:inline">Delivery Maturín</span>
                    </span>
                    <span class="hidden md:flex items-center gap-1.5 whitespace-nowrap animate-pulse">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        <span class="font-semibold text-brand-yellow">Delivery</span>
                    </span>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <a href="https://www.instagram.com/farmapazofficial/" target="_blank" rel="noopener" class="hover:text-brand-yellow transition-colors p-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <span class="hidden lg:flex items-center gap-1" style="color: rgba(255,255,255,0.8);">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <a href="https://wa.me/584128798885" target="_blank" class="font-medium text-white hover:text-brand-yellow transition-colors">0412-8798885</a>
                        <span style="color: rgba(255,255,255,0.4);">|</span>
                        <a href="https://wa.me/584220130448" target="_blank" class="text-white hover:text-brand-yellow transition-colors">0422-0130448</a>
                        <span style="color: rgba(255,255,255,0.4);">|</span>
                        <a href="https://wa.me/584220129206" target="_blank" class="text-white hover:text-brand-yellow transition-colors">0422-0129206</a>
                    </span>

                    <a href="https://wa.me/584128798885" target="_blank"
                       class="flex items-center gap-1.5 bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-full text-xs font-semibold transition-all hover:shadow-lg">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <div style="background: linear-gradient(135deg, #ffffff 0%, #f8faf6 50%, #fefaf0 100%); border-bottom: 2px solid #5A7D43;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14 sm:h-16 lg:h-20">

                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="<?= esc_url(home_url('/')); ?>" class="block">
                        <?php
                        $logo_url = get_template_directory_uri() . '/assets/images/logo.png';
                        $logo_path = get_template_directory() . '/assets/images/logo.png';
                        $logo_svg = get_template_directory_uri() . '/assets/images/logo.svg';
                        $logo_svg_path = get_template_directory() . '/assets/images/logo.svg';
                        if (file_exists($logo_svg_path)):
                        ?>
                            <img src="<?= $logo_svg; ?>" alt="Farmapaz" class="h-8 sm:h-10 w-auto">
                        <?php elseif (file_exists($logo_path)): ?>
                            <img src="<?= $logo_url; ?>" alt="Farmapaz" class="h-8 sm:h-10 w-auto">
                        <?php elseif (has_custom_logo()): ?>
                            <?php the_custom_logo(); ?>
                        <?php else: ?>
                            <span class="text-xl sm:text-2xl font-bold text-brand-green">Farmapaz</span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Search -->
                <div class="flex flex-1 max-w-lg mx-2 sm:mx-4 lg:mx-8">
                    <div class="relative w-full" data-search-container>
                        <form role="search" method="get" action="<?= esc_url(home_url('/')); ?>" class="search-form">
                            <input type="text" name="s" placeholder="Buscar..."
                                   class="farmapaz-search-input w-full pl-8 lg:pl-10 pr-8 py-1.5 lg:py-2.5 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-green"
                                   style="background: #fafcfa; border: 2px solid rgba(90,125,67,0.2);"
                                   autocomplete="off">
                            <span class="absolute left-2 lg:left-3 top-1/2 -translate-y-1/2 text-brand-green pointer-events-none">
                                <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <span class="farmapaz-spinner absolute right-2 lg:right-3 top-1/2 hidden pointer-events-none"></span>
                            <input type="hidden" name="post_type" value="product">
                        </form>
                        <div class="farmapaz-suggestions absolute top-full left-0 right-0 z-50 mt-1 bg-white rounded-xl shadow-xl border border-gray-100 hidden"></div>
                    </div>
                </div>

                <!-- Right Icons -->
                <div class="flex items-center space-x-2 sm:space-x-3 lg:space-x-5">
                    <a href="<?= esc_url(wc_get_account_endpoint_url('dashboard')); ?>"
                       class="hidden sm:flex items-center space-x-1.5 text-gray-600 hover:text-brand-green transition-colors text-xs sm:text-sm font-medium p-1 sm:p-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="hidden md:inline">Mi Cuenta</span>
                    </a>

                    <a href="<?= esc_url(wc_get_cart_url()); ?>"
                       class="relative flex items-center space-x-1.5 text-gray-600 hover:text-brand-green transition-colors text-xs sm:text-sm font-medium p-1 sm:p-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        <span class="hidden md:inline">Carrito</span>
                        <?php if (WC()->cart): ?>
                            <span class="absolute -top-1.5 -right-1.5 sm:-top-2 sm:-right-2 bg-brand-orange text-white text-xs font-bold rounded-full w-4 h-4 sm:w-5 sm:h-5 flex items-center justify-center shadow-sm">
                                <?= WC()->cart->get_cart_contents_count(); ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Mobile menu button -->
                    <button id="mobile-menu-btn" class="lg:hidden p-2 -mr-2 text-brand-green hover:text-brand-orange transition-colors" aria-label="Menú">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Navigation -->
    <nav class="hidden lg:block" style="background: linear-gradient(90deg, #5A7D43 0%, #4a6a36 100%);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-11">
                <!-- Categories Mega Dropdown -->
                <div class="relative group">
                    <button class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white hover:text-brand-yellow transition-colors rounded-lg hover:bg-white hover:bg-opacity-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        Categorías
                        <svg class="w-3 h-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="absolute top-full left-0 mt-0 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-5" style="min-width: 600px;">
                            <?php
                            $cats = get_terms([
                                'taxonomy'   => 'product_cat',
                                'hide_empty' => true,
                                'parent'     => 0,
                                'orderby'    => 'count',
                                'order'      => 'DESC',
                            ]);
                            if (!empty($cats) && !is_wp_error($cats)):
                            ?>
                            <div class="grid grid-cols-3 gap-2">
                                <?php foreach ($cats as $cat): ?>
                                <a href="<?= get_term_link($cat); ?>"
                                   class="flex items-center gap-3 p-3 rounded-xl hover:bg-brand-green hover:bg-opacity-5 hover:text-brand-green transition-all group/cat">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-brand-green transition-all" style="background: rgba(90,125,67,0.1);">
                                        <?= farmapaz_cat_icon($cat->slug); ?>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-700 group-hover/cat:text-brand-green transition-colors"><?= $cat->name; ?></span>
                                        <span class="text-xs text-gray-400 block"><?= $cat->count; ?> prod.</span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <div class="mt-3 pt-3 border-t border-gray-100 text-center">
                                <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="text-sm text-brand-green font-semibold hover:underline">
                                    Ver todas las categorías →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'flex items-center ml-6',
                    'fallback_cb'    => false,
                    'depth'          => 3,
                    'walker'         => new Farmapaz_Walker_Nav(),
                ]);
                ?>
            </div>
        </div>
    </nav>
</header>

<!-- Mobile Menu -->
<div id="mobile-menu" class="fixed inset-0 z-50 hidden lg:hidden">
    <div class="absolute inset-0 bg-black bg-opacity-40 backdrop-blur-sm" id="mobile-menu-overlay"></div>
    <div class="absolute top-0 right-0 bg-white shadow-2xl" style="width: 320px; max-width: 85vw; height: 100%;">
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <span class="font-semibold text-brand-green">Menú</span>
            <button id="mobile-menu-close" class="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-4 overflow-y-auto" style="height: calc(100% - 65px);">
            <form role="search" method="get" action="<?= esc_url(home_url('/')); ?>" class="relative mb-6">
                <input type="text" name="s" placeholder="Buscar productos..."
                       class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-brand-green">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="hidden" name="post_type" value="product">
            </form>

            <!-- Mobile Categories -->
            <div class="mb-6">
                <button id="mobile-cat-toggle" class="flex items-center justify-between w-full p-3 text-sm font-semibold text-gray-800 rounded-xl hover:bg-gray-50 transition-colors" style="background: rgba(9,20,110,0.04);">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        Categorías
                    </span>
                    <svg id="mobile-cat-chevron" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="mobile-cat-list" class="hidden mt-1 space-y-0.5">
                    <?php
                    $mobile_cats = get_terms([
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                        'parent'     => 0,
                        'orderby'    => 'count',
                        'order'      => 'DESC',
                    ]);
                    if (!empty($mobile_cats) && !is_wp_error($mobile_cats)):
                        foreach ($mobile_cats as $mcat):
                    ?>
                    <a href="<?= get_term_link($mcat); ?>" class="flex items-center gap-3 p-3 rounded-xl text-sm text-gray-600 hover:text-brand-green hover:bg-gray-50 transition-all">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(90,125,67,0.08); color: #5A7D43;">
                            <?= farmapaz_cat_icon($mcat->slug); ?>
                        </div>
                        <span><?= $mcat->name; ?></span>
                        <span class="ml-auto text-xs text-gray-400"><?= $mcat->count; ?></span>
                    </a>
                    <?php endforeach; endif; ?>
                    <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="flex items-center justify-center gap-1 p-3 text-sm font-medium text-brand-green hover:underline mt-1">
                        Ver todas las categorías
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <?php
            wp_nav_menu([
                'theme_location' => 'mobile',
                'container'      => false,
                'menu_class'     => 'space-y-1',
                'fallback_cb'    => false,
                'depth'          => 2,
            ]);
            ?>
            <div class="mt-6 pt-6 border-t border-gray-100 space-y-3">
                <a href="<?= esc_url(wc_get_account_endpoint_url('dashboard')); ?>"
                   class="flex items-center gap-3 p-3 text-gray-600 hover:text-brand-green rounded-xl transition-colors text-sm font-medium" style="background: rgba(90,125,67,0.05);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Mi Cuenta
                </a>
                <a href="<?= esc_url(wc_get_cart_url()); ?>"
                   class="flex items-center gap-3 p-3 text-gray-600 hover:text-brand-green rounded-xl transition-colors text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    Carrito
                </a>
                <a href="https://wa.me/584128798885" target="_blank"
                   class="flex items-center gap-3 p-3 text-green-600 rounded-xl transition-colors text-sm font-medium" style="background: rgba(22,163,74,0.05);">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

<main class="min-h-screen">
