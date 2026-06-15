<?php
/**
 * Page template
 */

// Cart & Checkout: full-width with WPBakery content decoding
if (is_cart() || is_checkout()):
    get_header(); ?>
    <div class="container-farmapaz py-8 lg:py-12">
        <?php if (have_posts()): while (have_posts()): the_post(); ?>
            <?php
            $content = get_the_content();
            if (preg_match('/\[vc_raw_html[^\]]*\](\S+?)\[\/vc_raw_html\]/', $content, $m)) {
                $decoded = base64_decode($m[1]);
                if ($decoded) {
                    $decoded = urldecode($decoded);
                    echo do_shortcode($decoded);
                }
            } else {
                echo do_shortcode($content);
            }
            ?>
        <?php endwhile; endif; ?>
    </div>
    <?php get_footer();
    return;
endif;

// My Account: strip WPBakery wrappers, process remaining shortcodes
if (is_account_page()):
    get_header(); ?>
    <div class="container-farmapaz py-8 lg:py-12">
        <?php if (have_posts()): while (have_posts()): the_post(); ?>
            <?php
            $content = get_the_content();
            // Remove all WPBakery shortcodes but keep inner content
            $content = preg_replace('/\[\/?vc_\w+[^\]]*\]/', '', $content);
            echo do_shortcode($content);
            ?>
        <?php endwhile; endif; ?>
    </div>
    <?php get_footer();
    return;
endif;
?>

<?php
// Sucursales page — custom modern futuristic design
if (is_page('sucursales')):
    get_header(); ?>
    <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #09146E 0%, #0c1fa0 50%, #1328c6 100%);">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full" style="background: radial-gradient(circle, rgba(254,171,13,0.12), transparent);"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 rounded-full" style="background: radial-gradient(circle, rgba(90,125,67,0.15), transparent);"></div>
            <div class="absolute top-1/3 left-1/2 w-48 h-48 rounded-full" style="background: radial-gradient(circle, rgba(255,255,255,0.04), transparent);"></div>
        </div>
        <div class="relative z-10 container-farmapaz py-20 lg:py-28">
            <div class="max-w-3xl">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight" style="animation: fadeIn 0.6s ease-out;">Sucursales</h1>
                <p class="text-lg sm:text-xl text-white/70 mt-4 max-w-xl" style="animation: slideUp 0.6s ease-out 0.1s both;">17 sucursales en Maturin, Estado Monagas. Te esperamos con los mejores productos para tu salud y bienestar.</p>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="relative">
        <div class="w-full" style="height: 400px; overflow: hidden;">
            <iframe src="https://maps.google.com/maps?q=Maturin+Monagas+Venezuela+Farmapaz&output=embed&z=13"
                    width="100%" height="100%" style="border:0; filter: grayscale(0.15) sepia(0.05);" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <div class="absolute inset-0 pointer-events-none flex items-center justify-center" style="background: linear-gradient(180deg, rgba(9,20,110,0.3) 0%, transparent 40%, transparent 60%, rgba(9,20,110,0.3) 100%);"></div>
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-10 pointer-events-none">
            <div class="px-6 py-3 rounded-full text-white text-sm font-medium backdrop-blur-md" style="background: rgba(9,20,110,0.75); border: 1px solid rgba(255,255,255,0.1);">
                Maturin, Edo Monagas — 17 sucursales
            </div>
        </div>
    </section>

    <!-- Sucursales Grid -->
    <section class="py-16 lg:py-24" style="background: #f8fafc;">
        <div class="container-farmapaz">
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-3xl lg:text-4xl font-bold" style="color: #09146E;">Todas nuestras sucursales</h2>
                <a href="https://www.google.com/maps/search/Farmapaz+Maturin/" target="_blank" rel="noopener"
                   class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 hover:shadow-lg"
                   style="background: rgba(9,20,110,0.06); color: #09146E;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Ver en Google Maps
                </a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-6">
                <?php
                $sucursales = [
                    ['Av. La Paz', 'Av. La Paz Qta. Nro 50-1, Sector las Avenidas.', '0412-8798885', 0.1],
                    ['Juncal', 'Av. Juncal con calle Piar, Local 3, Sector Centro.', '0412-8798885', 0.13],
                    ['Luis del Valle', 'Av. Luis del Valle Garcia, Edif. Doña Maria, P/B Local 1 y 2, Sector las Avenidas.', '0412-8798885', 0.16],
                    ['Hospital', 'Av. Bicentenario, Edif. Torre Fema, P/B Local 01.', '0412-8798885', 0.19],
                    ['Guaritos', 'Av. Principal los Guaritos, Local Nro 12.', '0412-8798885', 0.22],
                    ['Av. El Ejercito', 'Av. El Ejercito, Edif. San Jose, P/B Local 2, Urb. Alto de los Godos.', '0412-8798885', 0.25],
                    ['Tipuro', 'Av. Los Proceres, P/B Local S/N y Local 4, Sector Tipuro, Parroquia Boqueron.', '0412-8798885', 0.28],
                    ['La Cruz', 'Av. Principal de la Cruz, Local 1 y 2, frente a la plaza.', '0412-8798885', 0.31],
                    ['Sabana Grande', 'Calle Principal, Local Nro A-1, Sector 3 de Sabana Grande.', '0412-8798885', 0.34],
                    ['Santa Ines', 'Av. Raul Leoni, Local 7-3. Frente al Comando de GN. Sector Santa Ines.', '0412-8798885', 0.37],
                    ['La Puente', 'Calle Principal, Casa Nro S/N. Sector La Puente.', '0422-0130448', 0.40],
                    ['Azcue', 'Calle Azcue con Carrera 9, Edif. Morve, P/B Local B, Sector Centro.', '0422-0130448', 0.43],
                    ['City', 'Av. Bolivar, Edif. Radio Maturin, P/B Local Nro 4, Sector Centro. (Antiguo City Lamp).', '0422-0130448', 0.46],
                    ['Mercado', 'Calle 7, Local Nro 26, Sector Mercado Viejo.', '0422-0130448', 0.49, '7:30 AM — 5:30 PM'],
                    ['Las Cocuizas', 'CR Nro 7, Local Nro 133, Urb. Las Cocuizas.', '0422-0130448', 0.52],
                    ['Paramaconi', 'Calle Principal, Local S/N, Sector Paramaconi.', '0422-0129206', 0.55],
                ];
                $wa_groups = [];
                foreach ($sucursales as $s):
                    $tel = $s[2];
                    $tel_clean = preg_replace('/[^0-9]/', '', $tel);
                    $tel_wa = '58' . substr($tel_clean, 1);
                    $wa_groups[$tel_wa]['tel'] = $tel;
                    $wa_groups[$tel_wa]['branches'][] = $s[0];
                endforeach;
                foreach ($sucursales as $s):
                    $nombre = $s[0];
                    $dir = $s[1];
                    $delay = $s[3];
                    $hora = $s[4] ?? '24 horas';
                ?>
                <div class="rounded-2xl transition-all duration-500 hover:-translate-y-1 hover:shadow-xl overflow-hidden" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); border: 1px solid rgba(9,20,110,0.06); box-shadow: 0 4px 24px rgba(9,20,110,0.06); animation: slideUp 0.6s ease-out <?= $delay; ?>s both;">
                    <div class="w-full" style="height: 180px; overflow: hidden; background: #e2e8f0;">
                        <img src="<?= get_template_directory_uri(); ?>/assets/images/producto.png"
                             alt="Farmapaz <?= $nombre; ?>"
                             class="w-full h-full" style="object-fit: cover;"
                             loading="lazy">
                    </div>
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-2.5">
                            <h3 class="text-base font-bold leading-tight" style="color: #09146E;">Farmapaz <?= $nombre; ?></h3>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold whitespace-nowrap" style="background: #5A7D43; color: #fff;">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <?= $hora; ?>
                            </span>
                        </div>
                        <p class="text-gray-400 text-xs leading-relaxed"><?= $dir; ?></p>
                    </div>
                    <div class="w-full" style="height: 130px; overflow: hidden;">
                        <iframe scrolling="no" marginheight="0" marginwidth="0"
                                src="https://maps.google.com/maps?q=Farmapaz+<?= urlencode($nombre); ?>+<?= urlencode($dir); ?>+Maturin+Monagas+Venezuela&output=embed&z=17"
                                width="100%" height="100%" style="border:0; pointer-events: none;"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- WhatsApp Footer -->
    <section class="py-16 lg:py-20" style="background: linear-gradient(135deg, #09146E 0%, #0c1fa0 50%, #1328c6 100%);">
        <div class="container-farmapaz">
            <div class="max-w-3xl mx-auto text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-6" style="background: rgba(255,255,255,0.12);">
                    <svg class="w-7 h-7" style="color: #25D366;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-3">Contactanos por WhatsApp</h2>
                <p class="text-white/60 mb-8 max-w-lg mx-auto">Escribenos al numero correspondiente segun tu sucursal de preferencia.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <?php foreach ($wa_groups as $wa => $group): ?>
                    <a href="https://wa.me/<?= $wa; ?>" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-3 px-6 py-3.5 rounded-xl text-white font-semibold text-sm transition-all duration-300 hover:scale-105 hover:shadow-xl"
                       style="background: #25D366;">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        <?= $group['tel']; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6 text-white/40 text-xs">
                    <?php foreach ($wa_groups as $wa => $group): ?>
                    <span class="inline-block mx-2"><?= $group['tel']; ?>: <?= implode(', ', $group['branches']); ?></span><br class="sm:hidden">
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <?php get_footer();
    return;
endif;

get_header(); ?>

<div class="container-farmapaz py-16 lg:py-24">
    <?php if (have_posts()): while (have_posts()): the_post(); ?>
        <div class="max-w-3xl mx-auto">
            <h1 class="text-4xl lg:text-5xl font-bold text-brand-blue mb-6"><?php the_title(); ?></h1>
            <div class="prose prose-lg max-w-none text-gray-600 leading-relaxed">
                <?php the_content(); ?>
            </div>
        </div>
    <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
