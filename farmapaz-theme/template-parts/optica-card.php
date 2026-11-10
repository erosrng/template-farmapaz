<?php
/**
 * Tarjeta de montura — usada en la portada de óptica y en el catálogo.
 * Recibe $args['item'] desde get_template_part().
 */
if (!defined('ABSPATH')) exit;

$item = isset($args['item']) ? $args['item'] : [];
if (empty($item) || empty($item['permalink'])) return;

$has_alt = !empty($item['alt_img']);
?>
<article class="op-card<?= $has_alt ? ' has-alt' : ''; ?>"
         data-brand="<?= esc_attr($item['brand']); ?>"
         data-op-product>
    <a class="op-card__media"
       href="<?= esc_url($item['permalink']); ?>"
       aria-label="<?= esc_attr('Ver ' . $item['title'] . ' de ' . $item['brand']); ?>">
        <span class="op-card__aero" aria-hidden="true"></span>
        <img class="op-card__img op-card__img--main"
             src="<?= esc_url($item['img']); ?>"
             alt="<?= esc_attr($item['title']); ?>"
             loading="lazy"
             decoding="async">
        <?php if ($has_alt): ?>
            <img class="op-card__img op-card__img--alt"
                 src="<?= esc_url($item['alt_img']); ?>"
                 alt=""
                 loading="lazy"
                 decoding="async">
        <?php endif; ?>
        <span class="op-card__sheen" aria-hidden="true"></span>
    </a>

    <div class="op-card__body">
        <div class="op-card__brandrow">
            <span class="op-card__brand"><?= esc_html($item['brand']); ?></span>
        </div>

        <h3 class="op-card__name">
            <a href="<?= esc_url($item['permalink']); ?>"><?= esc_html($item['title']); ?></a>
        </h3>

        <div class="op-card__foot">
            <div class="op-price"><?= $item['price_html']; ?></div>
            <a class="op-card__action"
               href="<?= esc_url(farmapaz_whatsapp_url('Hola, quiero consultar por: ' . $item['title'] . ' - ' . $item['permalink'])); ?>"
               target="_blank"
               rel="noopener">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a9.9 9.9 0 0 0-8.5 15L2 22l5.2-1.4A9.9 9.9 0 1 0 12 2Zm4.6 12.1c-.3-.1-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4 0-.5.2-.7l.4-.5c.1-.2.1-.3 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s.9 2.5 1 2.7c.1.2 1.8 2.8 4.4 3.9 1.6.7 2.2.7 3 .6.5-.1 1.6-.6 1.8-1.3.2-.6.2-1.1.2-1.2-.1-.2-.2-.2-.4-.3Z"/></svg>
                Consultar
            </a>
        </div>
    </div>
</article>
