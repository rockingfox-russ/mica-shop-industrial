<?php
/**
 * front-page.php — Homepage (Industrial variant)
 */

defined( 'ABSPATH' ) || exit;
get_header();

$top_cats      = mica_get_categories( 0 );
$popular_args  = mica_build_query_args( [ 'orderby' => 'popularity' ] );
$popular_args['posts_per_page'] = 8;
$popular_query = new WP_Query( $popular_args );

$sale_args  = mica_build_query_args( [ 'on_sale' => true ] );
$sale_args['posts_per_page'] = 8;
$sale_query = new WP_Query( $sale_args );
?>

<div class="container" style="padding-top:var(--space-6);padding-bottom:var(--space-16);">

    <!-- ① Hero Banner -->
    <section class="hero-banner" style="margin-bottom:var(--space-6);">
        <div class="hero-content">
            <span class="hero-eyebrow">Mica Hardware &amp; Building Supplies</span>
            <h1 class="hero-title">
                Built for the<br>
                <span class="accent">trade professional.</span>
            </h1>
            <p class="hero-subtitle">
                Tools, hardware, paint, garden &amp; more. Shop online, collect in-store — or we deliver nationwide.
            </p>
            <div class="hero-ctas">
                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
                   class="btn btn-primary btn-lg">
                    Shop All Products
                </a>
                <a href="#departments" class="btn btn-lg"
                   style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);border:1.5px solid rgba(255,255,255,.25);">
                    Browse Departments
                </a>
            </div>
        </div>
    </section>

    <!-- ② Promo Stripe -->
    <div class="promo-stripe" style="border-radius:var(--radius-md);margin-bottom:var(--space-8);">
        <div class="promo-stripe-inner">
            <span class="promo-stripe-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Secure checkout with PayFast
            </span>
            <span class="promo-stripe-sep">|</span>
            <span class="promo-stripe-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Nationwide delivery 5–7 days
            </span>
            <span class="promo-stripe-sep">|</span>
            <span class="promo-stripe-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                100% South African owned
            </span>
            <span class="promo-stripe-sep">|</span>
            <span class="promo-stripe-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                Click &amp; Collect available
            </span>
        </div>
    </div>

    <!-- ③ Shop by Department -->
    <?php if ( ! empty( $top_cats ) ) : ?>
    <section id="departments" style="margin-bottom:var(--space-10);">
        <div class="section-header">
            <h2 class="section-title">Shop by Department</h2>
            <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="section-link">
                All departments →
            </a>
        </div>
        <div class="category-grid">
            <?php foreach ( $top_cats as $cat ) :
                $thumb_id  = get_term_meta( $cat->term_id, 'thumbnail_id', true );
                $thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
            ?>
            <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="category-card">
                <div class="category-card-icon">
                    <?php if ( $thumb_url ) : ?>
                        <img src="<?php echo esc_url( $thumb_url ); ?>"
                             alt="<?php echo esc_attr( $cat->name ); ?>"
                             style="width:100%;height:100%;object-fit:contain;padding:8px;">
                    <?php else : ?>
                        <?php echo mica_icon( 'store', '' ); ?>
                    <?php endif; ?>
                </div>
                <span class="category-card-name"><?php echo esc_html( $cat->name ); ?></span>
                <span class="category-card-count"><?php echo (int) $cat->count; ?> products</span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ④ Weekly Specials / On Sale -->
    <?php if ( $sale_query->have_posts() ) : ?>
    <section style="margin-bottom:var(--space-10);">
        <div class="section-header">
            <div style="display:flex;align-items:center;gap:var(--space-3);">
                <h2 class="section-title">Weekly Specials</h2>
                <span style="background:var(--clr-red);color:#fff;font-family:var(--font-condensed);font-size:var(--font-size-xs);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;padding:3px 10px;border-radius:var(--radius-sm);">SALE</span>
            </div>
            <a href="<?php echo esc_url( add_query_arg( 'on_sale', '1', get_permalink( wc_get_page_id( 'shop' ) ) ) ); ?>"
               class="section-link">All deals →</a>
        </div>
        <div class="products-grid">
            <?php while ( $sale_query->have_posts() ) :
                $sale_query->the_post();
                $product = wc_get_product( get_the_ID() );
                if ( $product ) mica_part( 'content/product-card', [ 'product' => $product ] );
            endwhile;
            wp_reset_postdata(); ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ⑤ Top Sellers -->
    <?php if ( $popular_query->have_posts() ) : ?>
    <section style="margin-bottom:var(--space-10);">
        <div class="section-header">
            <h2 class="section-title">Top Sellers</h2>
            <a href="<?php echo esc_url( add_query_arg( 'orderby', 'popularity', get_permalink( wc_get_page_id( 'shop' ) ) ) ); ?>"
               class="section-link">View all →</a>
        </div>
        <div class="products-grid">
            <?php while ( $popular_query->have_posts() ) :
                $popular_query->the_post();
                $product = wc_get_product( get_the_ID() );
                if ( $product ) mica_part( 'content/product-card', [ 'product' => $product ] );
            endwhile;
            wp_reset_postdata(); ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ⑥ Featured Brands -->
    <section style="margin-bottom:var(--space-10);">
        <div class="section-header">
            <h2 class="section-title">Trusted Brands</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:var(--space-3);">
            <?php
            $brands = [ 'Stanley', 'Bosch', 'Dulux', 'Alcolin', 'Makita', 'DeWalt', 'Cobra', 'Rust-Oleum' ];
            foreach ( $brands as $brand ) :
                $brand_url = add_query_arg( 's', urlencode( $brand ), get_permalink( wc_get_page_id( 'shop' ) ) );
            ?>
            <a href="<?php echo esc_url( $brand_url ); ?>"
               style="display:flex;align-items:center;justify-content:center;padding:var(--space-4);background:var(--clr-white);border:1.5px solid var(--clr-border);border-radius:var(--radius-md);font-family:var(--font-condensed);font-size:var(--font-size-sm);font-weight:700;color:var(--clr-text-muted);text-decoration:none;transition:all var(--transition-fast);letter-spacing:0.04em;"
               onmouseover="this.style.borderColor='var(--clr-orange)';this.style.color='var(--clr-orange)';"
               onmouseout="this.style.borderColor='var(--clr-border)';this.style.color='var(--clr-text-muted)';">
                <?php echo esc_html( $brand ); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ⑦ Trade Callout Banner -->
    <section style="margin-bottom:var(--space-10);">
        <div style="background:var(--clr-nav-bg);border-radius:var(--radius-md);padding:var(--space-10) var(--space-10);display:flex;align-items:center;justify-content:space-between;gap:var(--space-8);flex-wrap:wrap;border-left:4px solid var(--clr-orange);position:relative;overflow:hidden;">
            <div style="position:absolute;inset:0;background:repeating-linear-gradient(-45deg,transparent,transparent 18px,rgba(255,255,255,.012) 18px,rgba(255,255,255,.012) 19px);pointer-events:none;"></div>
            <div style="position:relative;z-index:1;">
                <p style="font-family:var(--font-condensed);font-size:var(--font-size-xs);font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--clr-orange);margin-bottom:var(--space-2);">Trade &amp; Professional</p>
                <h2 style="font-family:var(--font-condensed);font-size:clamp(1.5rem,3vw,2.25rem);font-weight:900;color:#fff;line-height:1.1;margin-bottom:var(--space-3);">
                    Supplying trade professionals<br>across South Africa.
                </h2>
                <p style="font-size:var(--font-size-sm);color:rgba(255,255,255,.60);max-width:420px;line-height:1.6;">
                    Volume pricing, dedicated support, and stock you can rely on. Contact us to discuss a trade account.
                </p>
            </div>
            <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;position:relative;z-index:1;">
                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>"
                   style="display:inline-flex;align-items:center;gap:var(--space-2);padding:var(--space-3) var(--space-6);background:var(--clr-orange);color:#fff;border-radius:var(--radius-sm);font-family:var(--font-condensed);font-size:var(--font-size-base);font-weight:700;letter-spacing:0.04em;text-decoration:none;">
                    Register Now
                </a>
                <?php
                $contact_page = get_page_by_path( 'contact-us' );
                if ( $contact_page ) : ?>
                <a href="<?php echo esc_url( get_permalink( $contact_page ) ); ?>"
                   style="display:inline-flex;align-items:center;gap:var(--space-2);padding:var(--space-3) var(--space-6);background:transparent;color:rgba(255,255,255,.75);border:1.5px solid rgba(255,255,255,.25);border-radius:var(--radius-sm);font-family:var(--font-condensed);font-size:var(--font-size-base);font-weight:700;letter-spacing:0.04em;text-decoration:none;">
                    Contact Us
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ⑧ Trust Signals -->
    <section>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--space-3);">
            <?php
            $trust = [
                [
                    'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
                    'title' => 'Secure Payments',
                    'desc'  => 'PayFast secured. SSL encrypted checkout on every order.',
                ],
                [
                    'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
                    'title' => 'Expert Advice',
                    'desc'  => 'Hardware professionals available in-store and online.',
                ],
                [
                    'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.65"/></svg>',
                    'title' => 'Easy Returns',
                    'desc'  => '30-day returns on unopened items. No hassle, no questions.',
                ],
                [
                    'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
                    'title' => 'Fast Delivery',
                    'desc'  => 'Nationwide delivery in 5–7 working days from dispatch.',
                ],
            ];
            foreach ( $trust as $t ) : ?>
            <div style="background:var(--clr-white);border:1.5px solid var(--clr-border);border-radius:var(--radius-md);padding:var(--space-5);display:flex;gap:var(--space-4);align-items:flex-start;border-left:3px solid var(--clr-orange);">
                <span style="color:var(--clr-orange);flex-shrink:0;margin-top:2px;"><?php echo $t['svg']; ?></span>
                <div>
                    <strong style="display:block;margin-bottom:4px;font-family:var(--font-condensed);font-size:var(--font-size-base);font-weight:700;letter-spacing:0.02em;">
                        <?php echo esc_html( $t['title'] ); ?>
                    </strong>
                    <span style="font-size:var(--font-size-sm);color:var(--clr-text-muted);line-height:1.5;">
                        <?php echo esc_html( $t['desc'] ); ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<?php get_footer(); ?>
