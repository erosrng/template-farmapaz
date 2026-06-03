<section class="relative overflow-hidden py-14 sm:py-20 lg:py-24 section-offscreen futuristic-section" style="background: linear-gradient(135deg, #f0f5ff, #ffffff, #e8f0fe);">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="relative z-10" style="max-width: 1280px; margin: 0 auto; padding: 0 1rem;">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8 stagger-children">
            <?php
            $trust_items = [
                ['icon' => 'shield', 'title' => 'Productos Originales', 'desc' => 'Garantizamos la autenticidad de todos nuestros productos'],
                ['icon' => 'truck', 'title' => 'Delivery Rapido', 'desc' => 'Recibe tus productos en la puerta de tu casa'],
                ['icon' => 'phone', 'title' => 'Atencion Personalizada', 'desc' => 'Nuestro equipo esta listo para ayudarte'],
                ['icon' => 'star', 'title' => '+18,000 Productos', 'desc' => 'El catalogo mas completo para tu salud'],
            ];

            foreach ($trust_items as $item):
            ?>
            <div class="group text-center p-4 sm:p-5 lg:p-8 rounded-2xl card-white stagger-item">
                <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 mx-auto mb-3 sm:mb-4 rounded-xl sm:rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500" style="background: linear-gradient(135deg, rgba(9,20,110,0.08), rgba(90,125,67,0.08)); color: #09146E;">
                    <?= farmapaz_icon($item['icon']); ?>
                </div>
                <h4 class="text-sm sm:text-base font-semibold text-gray-900"><?= $item['title']; ?></h4>
                <p class="text-xs sm:text-sm mt-1 sm:mt-2 leading-relaxed" style="color: rgba(9,20,110,0.5);"><?= $item['desc']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
