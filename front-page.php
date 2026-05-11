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

    <!-- ① Hero — Direction A editorial structure × Direction B colour/font -->
    <section class="hero-editorial" style="margin-bottom:var(--space-8);">

        <!-- dateline -->
        <div class="hero-dateline">
            <span>Your neighbourhood hardware store.</span>
            <span><?php echo date_i18n( 'D j M' ); ?> · Specials end Sunday</span>
        </div>

        <div class="hero-editorial-grid">

            <!-- Left: main dark card -->
            <?php
            $hero_img_id  = get_theme_mod( 'mica_hero_image', '' );
            $hero_img_url = $hero_img_id ? wp_get_attachment_image_url( $hero_img_id, 'full' ) : '';
            $hero_bg_style = $hero_img_url
                ? 'style="background:var(--clr-navy);background-image:url(' . esc_url( $hero_img_url ) . ');background-size:cover;background-position:center;"'
                : '';
            ?>
            <div class="hero-main-card" <?php echo $hero_bg_style; ?>>
                <div class="hero-main-inner">
                    <div class="hero-main-top">
                        <span class="hero-chip-sale"><?php echo esc_html( get_theme_mod( 'mica_hero_sale_pct', 'Save up to 35%' ) ); ?></span>
                        <span class="hero-eyebrow-mono"><?php echo esc_html( get_theme_mod( 'mica_hero_eyebrow', 'Power Tools Week' ) ); ?></span>
                    </div>
                    <div class="hero-main-copy">
                        <h1 class="hero-headline">
                            <?php echo esc_html( get_theme_mod( 'mica_hero_headline', 'Built for the next' ) ); ?><br>
                            <em class="hero-headline-accent"><?php echo esc_html( get_theme_mod( 'mica_hero_accent', 'weekend job.' ) ); ?></em>
                        </h1>
                        <p class="hero-sub">
                            <?php echo esc_html( get_theme_mod( 'mica_hero_sub', 'Tools & kits from DeWalt, Bosch & Makita — handpicked by your local Mica team.' ) ); ?>
                        </p>
                        <div class="hero-ctas">
                            <a href="<?php echo esc_url( add_query_arg( 'on_sale', '1', get_permalink( wc_get_page_id( 'shop' ) ) ) ); ?>" class="hero-btn-primary">
                                <?php echo esc_html( get_theme_mod( 'mica_hero_cta1', 'Shop the deals' ) ); ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </a>
                            <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="hero-btn-ghost">
                                <?php echo esc_html( get_theme_mod( 'mica_hero_cta2', 'All products' ) ); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: two stacked feature cards -->
            <div class="hero-side-stack">

                <!-- Deal card -->
                <?php
                $deal_args = mica_build_query_args( [ 'on_sale' => true ] );
                $deal_args['posts_per_page'] = 1;
                $deal_args['orderby'] = 'meta_value_num';
                $deal_args['meta_key'] = '_sale_price';
                $deal_args['order'] = 'DESC';
                $deal_query = new WP_Query( $deal_args );
                $deal_product = null;
                if ( $deal_query->have_posts() ) {
                    $deal_query->the_post();
                    $deal_product = wc_get_product( get_the_ID() );
                }
                wp_reset_postdata();
                ?>
                <div class="hero-deal-card">
                    <span class="hero-chip-deal">Deal of the day</span>
                    <?php if ( $deal_product ) : ?>
                        <h3 class="hero-deal-title"><?php echo esc_html( $deal_product->get_name() ); ?></h3>
                        <div class="hero-deal-price">
                            <span class="hero-deal-current"><?php echo wc_price( $deal_product->get_sale_price() ); ?></span>
                            <span class="hero-deal-was"><?php echo wc_price( $deal_product->get_regular_price() ); ?></span>
                        </div>
                        <a href="<?php echo esc_url( get_permalink( $deal_product->get_id() ) ); ?>" class="hero-deal-link">
                            View deal →
                        </a>
                    <?php else : ?>
                        <h3 class="hero-deal-title">5L Premium Wall Paint, any colour</h3>
                        <div class="hero-deal-price">
                            <span class="hero-deal-current">R599</span>
                            <span class="hero-deal-was">R899</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Delivery info card (trade account hidden until available) -->
                <div class="hero-trade-card">
                    <span class="hero-chip-trade">Nationwide Delivery</span>
                    <h3 class="hero-trade-title">Delivered to your door in 5–7 working days. Shop from anywhere in South Africa.</h3>
                    <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="hero-trade-link">
                        Shop online →
                    </a>
                </div>

            </div><!-- .hero-side-stack -->
        </div><!-- .hero-editorial-grid -->

        <!-- Trust strip — delivery & returns only -->
        <div class="hero-trust-strip">
            <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg> Nationwide delivery 5–7 working days</span>
            <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.65"/></svg> 30-day no-fuss returns</span>
            <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Secure checkout with PayFast</span>
            <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg> Expert product advice</span>
        </div>
    </section>

    <!-- ③ Shop by Department — B's aisle tiles -->
    <?php if ( ! empty( $top_cats ) ) : ?>
    <section id="departments" class="aisle-section">
        <div class="section-head-b">
            <div>
                <div class="section-eyebrow">01 · Aisles</div>
                <h2 class="section-title-b">Browse the <em class="serif-italic">aisles.</em></h2>
                <p class="section-sub"><?php echo count( $top_cats ); ?> departments, <?php echo number_format( array_sum( array_column( $top_cats, 'count' ) ) ); ?>+ SKUs — all on one site.</p>
            </div>
            <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="section-link-b">
                See all departments <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
        <?php
        $aisle_swatches = [ '#E8590C','#0B2E6B','#C8102E','#1B5E20','#2E7D32','#E8B007','#1565C0','#5D4037' ];
        $aisle_sizes    = [
            'grid-column:span 3;grid-row:span 2',
            'grid-column:span 3;grid-row:span 1',
            'grid-column:span 2;grid-row:span 1',
            'grid-column:span 2;grid-row:span 2',
            'grid-column:span 2;grid-row:span 1',
            'grid-column:span 2;grid-row:span 1',
            'grid-column:span 2;grid-row:span 1',
            'grid-column:span 2;grid-row:span 1',
        ];
        ?>
        <div class="aisle-grid">
            <?php foreach ( $top_cats as $i => $cat ) :
                $swatch = $aisle_swatches[ $i % count( $aisle_swatches ) ];
                $size   = $aisle_sizes[ $i ] ?? 'grid-column:span 2;grid-row:span 1';
                $count  = mica_cat_product_count( $cat->term_id );
                $num    = str_pad( $i + 1, 2, '0', STR_PAD_LEFT );
                $big    = $i === 0;
            ?>
            <?php
            $cat_thumb_id  = get_term_meta( $cat->term_id, 'thumbnail_id', true );
            $cat_thumb_url = $cat_thumb_id ? wp_get_attachment_image_url( $cat_thumb_id, 'large' ) : '';
            $tile_bg = $cat_thumb_url
                ? 'background:' . esc_attr( $swatch ) . ';background-image:url(' . esc_url( $cat_thumb_url ) . ');background-size:cover;background-position:center;'
                : 'background:' . esc_attr( $swatch ) . ';';
            ?>
            <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
               class="aisle-tile"
               style="<?php echo $tile_bg; ?><?php echo $size; ?>">
                <div class="aisle-tile-top">
                    <span class="aisle-num">Aisle <?php echo $num; ?></span>
                    <span class="aisle-count"><?php echo number_format( $count ); ?> items →</span>
                </div>
                <h3 class="aisle-name<?php echo $big ? ' aisle-name-big' : ''; ?>"><?php echo esc_html( $cat->name ); ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ④ Weekly Specials / On Sale -->
    <?php if ( $sale_query->have_posts() ) : ?>
    <section class="b-section">
        <div class="section-head-b">
            <div>
                <div class="section-eyebrow">02 · This week</div>
                <h2 class="section-title-b">Just in <em class="serif-italic">on the deals.</em></h2>
            </div>
            <a href="<?php echo esc_url( add_query_arg( 'on_sale', '1', get_permalink( wc_get_page_id( 'shop' ) ) ) ); ?>" class="section-link-b">
                View all deals <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
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
    <section class="b-section">
        <div class="section-head-b">
            <div>
                <div class="section-eyebrow">03 · Best sellers</div>
                <h2 class="section-title-b">What's flying <em class="serif-italic">off the shelves.</em></h2>
            </div>
            <a href="<?php echo esc_url( add_query_arg( 'orderby', 'popularity', get_permalink( wc_get_page_id( 'shop' ) ) ) ); ?>" class="section-link-b">
                View all <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
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

    <!-- ⑥ Brand Strip — B's grid style -->
    <section class="brand-strip-b">
        <div class="brand-strip-inner">
            <div class="brand-strip-head">
                <div class="section-eyebrow">04 · Brands</div>
                <h2 class="brand-strip-title">Trusted names<br><em class="serif-italic">on every shelf.</em></h2>
                <p class="brand-strip-sub">DeWalt, Bosch, Makita, Plascon, Stanley and 60+ more — same warranties as buying direct.</p>
            </div>
            <div class="brand-grid">
                <?php
                // Load from product_tag terms — slugs from Customizer, fallback to top 12 by count
                $brand_slugs = array_filter( array_map( 'trim', explode( ',', get_theme_mod( 'mica_brand_tags', '' ) ) ) );
                if ( ! empty( $brand_slugs ) ) {
                    $brand_terms = array_filter( array_map( fn( $s ) => get_term_by( 'slug', $s, 'product_tag' ), $brand_slugs ) );
                } else {
                    $brand_terms = get_terms( [
                        'taxonomy'   => 'product_tag',
                        'number'     => 12,
                        'orderby'    => 'count',
                        'order'      => 'DESC',
                        'hide_empty' => true,
                    ] );
                }
                foreach ( $brand_terms as $brand_term ) :
                    if ( ! $brand_term instanceof WP_Term ) continue;
                    $logo_id  = (int) get_term_meta( $brand_term->term_id, 'mica_tag_logo', true );
                    $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
                    $link_url = add_query_arg( [ 'filter_tag' => [ $brand_term->slug ] ], get_permalink( wc_get_page_id( 'shop' ) ) );
                ?>
                <a href="<?php echo esc_url( $link_url ); ?>" class="brand-cell">
                    <?php if ( $logo_url ) : ?>
                        <img src="<?php echo esc_url( $logo_url ); ?>"
                             alt="<?php echo esc_attr( $brand_term->name ); ?>"
                             style="max-height:40px;max-width:110px;width:auto;object-fit:contain;filter:grayscale(1);opacity:.7;transition:opacity .15s,filter .15s;"
                             onmouseover="this.style.filter='none';this.style.opacity='1';"
                             onmouseout="this.style.filter='grayscale(1)';this.style.opacity='.7';">
                    <?php else : ?>
                        <?php echo esc_html( $brand_term->name ); ?>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php /* Local strip hidden — click & collect not yet available */ if ( false ) : ?>
    <!-- ⑦ Local Shop Strip — B's community callout -->
    <section class="local-strip-b">
        <div class="local-strip-visual">
            <div class="local-strip-hours">
                <span class="local-hours-label">Your nearest Mica</span>
                <span class="local-hours-name"><?php echo esc_html( get_theme_mod( 'mica_local_name', 'Find a store' ) ); ?></span>
                <span class="local-hours-times">
                    <?php echo esc_html( get_theme_mod( 'mica_local_hours_mon', 'Mon–Fri · 07:30–17:30' ) ); ?><br>
                    <?php echo esc_html( get_theme_mod( 'mica_local_hours_sat', 'Sat · 08:00–14:00 · Sun closed' ) ); ?>
                </span>
            </div>
        </div>
        <div class="local-strip-copy">
            <div class="section-eyebrow">05 · The shop</div>
            <h2 class="local-strip-title">
                <?php echo esc_html( get_theme_mod( 'mica_local_headline', "We're around the corner." ) ); ?>
                <em class="serif-italic"><?php echo esc_html( get_theme_mod( 'mica_local_accent', 'Always have been.' ) ); ?></em>
            </h2>
            <p class="local-strip-sub"><?php echo esc_html( get_theme_mod( 'mica_local_sub', 'Family-run stores across South Africa. We mix paint, cut keys, hire tools, and answer questions you\'d be embarrassed to Google.' ) ); ?></p>
            <div class="local-strip-ctas">
                <a href="<?php $contact_page = get_page_by_path('contact-us'); echo esc_url( $contact_page ? get_permalink($contact_page) : '#' ); ?>" class="local-btn-primary">
                    Contact us <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                <a href="<?php echo esc_url( get_permalink( wc_get_page_id('myaccount') ) ); ?>" class="local-btn-ghost">
                    Open a trade account
                </a>
            </div>
            <div class="local-trust-grid">
                <?php
                $local_trust = [
                    ['truck', 'Same-day in the bay'],
                    ['pin',   '30-min collect'],
                    ['wrench','Paint mixing'],
                    ['shield','30-day returns'],
                ];
                foreach ( $local_trust as $lt ) : ?>
                <div class="local-trust-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <?php
                        $icons = [
                            'truck'  => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
                            'pin'    => '<path d="M12 22s7-7 7-13a7 7 0 1 0-14 0c0 6 7 13 7 13z"/><circle cx="12" cy="9" r="2.5"/>',
                            'wrench' => '<path d="M14.7 6.3a4 4 0 0 1 5 5l-2.3-2.3-2 2 2.3 2.3a4 4 0 0 1-5-5L4.6 16.4a2 2 0 1 0 2.8 2.8L18 8.6"/>',
                            'shield' => '<path d="M12 3 4 6v6c0 5 3.5 8.5 8 9 4.5-.5 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>',
                        ];
                        echo $icons[ $lt[0] ] ?? '';
                        ?>
                    </svg>
                    <span><?php echo esc_html( $lt[1] ); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php endif; /* end local strip hidden */ ?>

</div>

<?php get_footer(); ?>
