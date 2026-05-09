<?php get_header(); ?>
<div class="container flex-center flex-col" style="min-height:60vh;gap:var(--space-4);text-align:center;padding:var(--space-16) 0;">
    <span style="font-size:5rem;">🔍</span>
    <h1 style="font-size:var(--font-size-4xl);">Page not found</h1>
    <p class="text-muted" style="max-width:400px;">The page you're looking for doesn't exist or has moved.</p>
    <div class="flex gap-4" style="margin-top:var(--space-4);">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">Go Home</a>
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn btn-secondary">Browse Shop</a>
    </div>
</div>
<?php get_footer();
