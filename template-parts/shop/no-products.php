<?php
/**
 * template-parts/shop/no-products.php
 */
defined( 'ABSPATH' ) || exit;
?>
<div style="text-align:center;padding:4rem 2rem;color:var(--clr-text-muted);">
    <div style="font-size:48px;margin-bottom:1rem;">🔍</div>
    <h3 style="font-size:1.25rem;font-weight:600;color:var(--clr-text);margin-bottom:.5rem;">
        <?php esc_html_e( 'No products found', 'micaonline' ); ?>
    </h3>
    <p style="font-size:.875rem;max-width:320px;margin:0 auto 1.5rem;">
        <?php esc_html_e( 'Try adjusting your filters or search terms.', 'micaonline' ); ?>
    </p>
    <button class="btn btn-ghost" id="filter-clear-all-empty">
        <?php esc_html_e( 'Clear all filters', 'micaonline' ); ?>
    </button>
</div>
<script>
document.getElementById('filter-clear-all-empty')?.addEventListener('click', () => {
    document.getElementById('filter-clear-all')?.click();
});
</script>
