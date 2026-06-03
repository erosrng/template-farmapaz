<?php
/**
 * Sidebar template
 */

if (!is_active_sidebar('shop-sidebar')) {
    return;
}
?>

<div class="space-y-6">
    <?php dynamic_sidebar('shop-sidebar'); ?>
</div>
