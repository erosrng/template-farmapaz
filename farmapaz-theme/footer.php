</main>

<!-- Footer -->
<footer class="text-white" style="background: #09146E;">
    <!-- Newsletter -->
    <div class="border-b border-white border-opacity-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
            <div class="grid sm:grid-cols-2 gap-6 sm:gap-8 items-center fade-in-up">
                <div>
                    <h3 class="text-xl sm:text-2xl font-bold">Mantente informado</h3>
                    <p class="text-white text-opacity-70 mt-1 sm:mt-2 text-sm sm:text-base">Recibe ofertas exclusivas y novedades en tu correo.</p>
                </div>
                <div>
                    <form class="flex flex-col sm:flex-row gap-3">
                        <input type="email" placeholder="Tu correo electrónico"
                               class="flex-1 px-4 py-3 rounded-xl text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-brand-yellow text-sm" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                        <button type="submit"
                                class="px-6 py-3 bg-brand-yellow text-brand-blue font-semibold rounded-xl hover:bg-yellow-400 hover:shadow-lg transition-all duration-300 text-sm whitespace-nowrap">
                            Suscribirme
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Footer -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 sm:gap-10">
            <!-- Brand -->
            <div class="col-span-2 md:col-span-1 fade-in-up">
                <div class="mb-4">
                    <?php if (has_custom_logo()): ?>
                        <?php the_custom_logo(); ?>
                    <?php else: ?>
                        <span class="text-xl sm:text-2xl font-bold text-white">Farmapaz</span>
                    <?php endif; ?>
                </div>
                <p class="text-white text-opacity-60 text-xs sm:text-sm leading-relaxed mb-6">
                    Tu farmacia de confianza en Maturín, Edo Monagas, Venezuela. Comprometidos con tu salud y bienestar.
                </p>
                <div class="flex space-x-3">
                    <a href="https://www.instagram.com/farmapazofficial/" target="_blank" rel="noopener"
                       class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center hover:bg-brand-yellow transition-colors" style="background: rgba(255,255,255,0.1);">
                        <?= farmapaz_icon('instagram') ?>
                    </a>
                    <a href="https://wa.me/584128798885" target="_blank" rel="noopener"
                       class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center hover:bg-green-500 transition-colors" style="background: rgba(255,255,255,0.1);">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors" style="background: rgba(255,255,255,0.1);">
                        <?= farmapaz_icon('facebook') ?>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="fade-in-up" style="transition-delay: 0.1s">
                <h4 class="text-xs font-semibold uppercase tracking-wider text-white text-opacity-80 mb-5 sm:mb-6">Enlaces</h4>
                <ul class="space-y-2.5 sm:space-y-3 text-xs sm:text-sm text-white text-opacity-60">
                    <li><a href="<?= esc_url(home_url('/')); ?>" class="hover:text-brand-yellow transition-colors">Inicio</a></li>
                    <li><a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="hover:text-brand-yellow transition-colors">Tienda</a></li>
                    <li><a href="<?= esc_url(home_url('/sucursales')); ?>" class="hover:text-brand-yellow transition-colors">Sucursales</a></li>
                    <li><a href="<?= esc_url(home_url('/guiamedica')); ?>" class="hover:text-brand-yellow transition-colors">Guía Médica</a></li>
                    <li><a href="<?= esc_url(home_url('/encarte-farmapaz-2')); ?>" class="hover:text-brand-yellow transition-colors">Encarte</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="fade-in-up" style="transition-delay: 0.2s">
                <h4 class="text-xs font-semibold uppercase tracking-wider text-white text-opacity-80 mb-5 sm:mb-6">Contacto</h4>
                <ul class="space-y-3 sm:space-y-4 text-xs sm:text-sm text-white text-opacity-60">
                    <li class="flex items-start gap-3">
                        <?= farmapaz_icon('phone') ?>
                        <div>
                            <p>0412-8798885</p>
                            <p>0422-0130448</p>
                            <p>0422-0129206</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <?= farmapaz_icon('location') ?>
                        <span>Maturín, Edo Monagas</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <?= farmapaz_icon('clock') ?>
                        <span>7:30 am - 9:00 pm</span>
                    </li>
                </ul>
            </div>

            <!-- Account -->
            <div class="fade-in-up" style="transition-delay: 0.3s">
                <h4 class="text-xs font-semibold uppercase tracking-wider text-white text-opacity-80 mb-5 sm:mb-6">Cuenta</h4>
                <ul class="space-y-2.5 sm:space-y-3 text-xs sm:text-sm text-white text-opacity-60">
                    <li><a href="<?= esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>" class="hover:text-brand-yellow transition-colors">Iniciar Sesión</a></li>
                    <li><a href="<?= esc_url(wc_get_cart_url()); ?>" class="hover:text-brand-yellow transition-colors">Carrito</a></li>
                    <li><a href="<?= esc_url(wc_get_checkout_url()); ?>" class="hover:text-brand-yellow transition-colors">Checkout</a></li>
                    <li><a href="<?= esc_url(home_url('/politicas-privacidad')); ?>" class="hover:text-brand-yellow transition-colors">Privacidad</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bottom -->
    <div class="border-t border-white border-opacity-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6">
            <div class="flex flex-col sm:flex-row items-center justify-between text-xs sm:text-sm text-white text-opacity-40 gap-2">
                <p>&copy; <?= date('Y'); ?> Farmapaz. Todos los derechos reservados.</p>
                <p>Desarrollada por Farmapaz</p>
            </div>
        </div>
    </div>
</footer>

<!-- WhatsApp Float -->
<?= do_shortcode('[farmapaz_whatsapp]'); ?>

<?php wp_footer(); ?>
</body>
</html>
