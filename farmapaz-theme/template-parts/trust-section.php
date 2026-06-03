<section class="relative overflow-hidden py-14 sm:py-20 lg:py-24 section-offscreen futuristic-brand-blue" style="background: linear-gradient(135deg, #09146E 0%, #0a1a7a 30%, #0c2090 60%, #09146E 100%);">

    <!-- Glowing orbs -->
    <div class="orb orb-1" style="background: radial-gradient(circle, rgba(254,171,13,0.25), transparent); width: 400px; height: 400px; top: -150px; right: -100px; left: auto; animation: orbFloat 14s ease-in-out infinite;"></div>
    <div class="orb orb-2" style="background: radial-gradient(circle, rgba(9,20,110,0.2), transparent); width: 350px; height: 350px; bottom: -120px; left: -120px; animation: orbFloat 18s ease-in-out infinite reverse;"></div>
    <div class="orb orb-3" style="background: radial-gradient(circle, rgba(254,171,13,0.12), transparent); width: 250px; height: 250px; top: 40%; left: 55%; animation: orbFloat 12s ease-in-out infinite 3s;"></div>

    <div class="relative z-10" style="max-width: 1280px; margin: 0 auto; padding: 0 1rem;">
        <!-- Section Title -->
        <div class="text-center mb-10 sm:mb-14 lg:mb-16 stagger-children">
            <span class="inline-block px-4 py-1.5 rounded-full text-xs font-semibold tracking-wider uppercase mb-4" style="background: rgba(254,171,13,0.15); color: #FEAB0D; border: 1px solid rgba(254,171,13,0.2);">
                Por qué elegirnos
            </span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white">Tu salud, <span style="color: #FEAB0D;">nuestra prioridad</span></h2>
            <p class="text-sm sm:text-base mt-3 max-w-xl mx-auto" style="color: rgba(255,255,255,0.55);">Más de 18,000 productos originales con delivery rápido y atención personalizada</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 lg:gap-6 stagger-children">
            <?php
            $trust_items = [
                ['icon' => 'shield', 'title' => 'Productos Originales', 'desc' => 'Garantizamos la autenticidad de todos nuestros productos'],
                ['icon' => 'truck', 'title' => 'Delivery Rápido', 'desc' => 'Recibe tus productos en la puerta de tu casa'],
                ['icon' => 'phone', 'title' => 'Atención Personalizada', 'desc' => 'Nuestro equipo está listo para ayudarte'],
                ['icon' => 'star', 'title' => '+18,000 Productos', 'desc' => 'El catálogo más completo para tu salud'],
            ];

            foreach ($trust_items as $item):
            ?>
            <div class="group text-center p-5 sm:p-6 lg:p-8 rounded-2xl stagger-item card-glass-dark" style="border: 1px solid rgba(255,255,255,0.08);">
                <div class="w-14 h-14 sm:w-16 sm:h-16 lg:w-20 lg:h-20 mx-auto mb-4 sm:mb-5 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-[3deg] transition-all duration-500" style="background: rgba(254,171,13,0.1); color: #FEAB0D; box-shadow: 0 0 30px rgba(254,171,13,0.05);">
                    <?= farmapaz_icon($item['icon']); ?>
                </div>
                <h4 class="text-sm sm:text-base lg:text-lg font-bold text-white group-hover:text-brand-yellow transition-colors duration-300"><?= $item['title']; ?></h4>
                <p class="text-xs sm:text-sm mt-2 leading-relaxed" style="color: rgba(255,255,255,0.5);"><?= $item['desc']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
