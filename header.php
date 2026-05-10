<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <!-- <meta name="description" content="<?php echo esc_attr( get_bloginfo( 'description' ) ); ?>"> -->

    <?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="site" id="page">

    <!-- Utility Bar — B's warm paper mono style -->
    <div class="utility-bar-b">
        <div class="utility-bar-b-inner">
            <div class="utility-bar-b-left">
                <span>
                    <span class="utility-status-dot"></span>
                    <?php echo esc_html( get_theme_mod( 'mica_local_name', 'Mica' ) ); ?>
                    <?php if ( get_theme_mod( 'mica_store_hours' ) ) : ?>
                        · <?php echo esc_html( get_theme_mod( 'mica_store_hours', 'Open until 17:30' ) ); ?>
                    <?php endif; ?>
                </span>
                <?php if ( get_theme_mod( 'mica_store_phone' ) ) : ?>
                <span><?php echo esc_html( get_theme_mod( 'mica_store_phone' ) ); ?></span>
                <?php endif; ?>
            </div>
            <div class="utility-bar-b-right">
                <span>Trade counter</span>
                <span>Tool hire</span>
                <span>Workshops</span>
                <?php $find_store = get_page_by_path( 'find-a-store' ); ?>
                <a href="<?php echo esc_url( $find_store ? get_permalink( $find_store ) : '#' ); ?>" style="color:var(--clr-text-muted);text-decoration:none;">Find a store →</a>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="site-header" id="masthead">
        <div class="container">
            <div class="header-wrapper">
                <!-- Logo + Est. tagline -->
                <?php
                $custom_logo_id = get_theme_mod('custom_logo');
                if ($custom_logo_id) :
                    $logo_image = wp_get_attachment_image($custom_logo_id, 'full', false, array('class' => 'logo-img'));
                    ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" aria-label="Mica Online Home" style="display:flex;align-items:center;">
                        <?php echo $logo_image; ?>
                        <div class="logo-tagline">Est. 1988<br>Hardware co.</div>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" style="display:flex;align-items:center;">
                        <span class="logo-text">Mica<span class="logo-accent">Online</span></span>
                        <div class="logo-tagline">Est. 1988<br>Hardware co.</div>
                    </a>
                <?php endif; ?>

                <!-- Search Bar -->
                <div class="header-search">
                    <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <input type="hidden" name="post_type" value="product">

                        <div class="search-cat-wrap">
                            <select name="product_cat" id="search-category" aria-label="Category">
                                <option value="">Category</option>
                                <?php
                                $categories = get_terms( [ 'taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => true ] );
                                $current_cat = isset( $_GET['product_cat'] ) ? sanitize_text_field( $_GET['product_cat'] ) : '';
                                foreach ( $categories as $cat ) : ?>
                                    <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $current_cat, $cat->slug ); ?>>
                                        <?php echo esc_html( $cat->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php echo mica_icon( 'chevron', 'search-cat-chevron' ); ?>
                        </div>

                        <div class="search-divider"></div>

                        <!-- <?php echo mica_icon( 'search', 'search-icon' ); ?> -->
                        <input type="search"
                               name="s"
                               placeholder="Search products, SKU, barcode..."
                               value="<?php echo esc_attr( get_search_query() ); ?>"
                               autocomplete="off">

                        <span class="search-hint">Press / to search</span>
                        <button type="submit" class="header-search-btn" aria-label="Search">
                            <?php echo mica_icon( 'search' ); ?>
                        </button>
                    </form>
                </div>

                <!-- Header Actions -->
                <div class="header-actions">
                    <a class="action-icon-link " href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>">
                        <span class="action-icon">👤</span>
                    </a>
                        <?php if (is_user_logged_in()) : ?>
                        <a class="action-link " href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>">My Account</a>
                    <?php else : ?>
                        <a class="action-link " href="<?php echo esc_url(wp_login_url()); ?>">Sign in</a>
                        <a class="action-link " href="<?php echo esc_url(wp_registration_url()); ?>">Register</a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="action-link cart-link">
                        <span class="action-icon">🛒</span>
                        <span class="action-label">Cart</span>
                        <span class="cart-count"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>
                    </a>
                    <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Menu">☰</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation Bar -->
    <nav class="nav-bar">
        <div class="container">
            <div class="nav-wrapper">
                <button class="shop-dept-btn" id="shop-dept-btn">
                    <span class="btn-icon">☰</span>
                    <span class="btn-text">Shop by Department</span>
                    <span class="btn-arrow">▼</span>
                </button>
                <nav class="nav-links" aria-label="<?php esc_attr_e( 'Site navigation', 'micaonline' ); ?>">
                    <?php
                    if ( has_nav_menu( 'navbar' ) ) {
                        wp_nav_menu( [
                            'theme_location' => 'navbar',
                            'container'      => false,
                            'menu_class'     => 'nav-links-inner',
                            'depth'          => 1,
                            'item_spacing'   => 'discard',
                            'link_class'     => 'nav-link',
                        ] );
                    } else {
                        // Fallback until a menu is assigned in Appearance → Menus
                        $defaults = [
                            '/promotions'        => 'Promotions',
                            '/online-exclusives' => 'Online Exclusives',
                            '/inspiration'       => 'Inspiration',
                            '/find-a-store'      => 'Find a Store',
                            '/contact'           => 'Contact Us',
                        ];
                        foreach ( $defaults as $url => $label ) {
                            printf( '<a href="%s" class="nav-link">%s</a>', esc_url( home_url( $url ) ), esc_html( $label ) );
                        }
                    }
                    ?>
                </nav>
                <span class="nav-specials">● Specials end Sun</span>
            </div>
        </div>
    </nav>

    <!-- Department tree inlined — zero-latency JS access, no AJAX needed -->
    <?php $dept_tree = mica_get_dept_tree(); ?>
    <script id="mica-dept-tree" type="application/json"><?php echo wp_json_encode( $dept_tree ); ?></script>

    <!-- Multi-Level Department Sidebar -->
    <div class="dept-sidebar-overlay" id="dept-overlay"></div>
    <div class="dept-sidebar" id="dept-sidebar">
        <!-- Level 1 - Main Departments (server-rendered, always instant) -->
        <div class="sidebar-level level-1 active" id="level-1">
            <div class="sidebar-header">
                <h3>Shop by Department</h3>
                <button class="close-dept" id="close-dept">✕</button>
            </div>
            <div class="sidebar-content">
                <?php foreach ( $dept_tree as $dept ) :
                    $has_sub = ! empty( $dept['children'] );
                ?>
                <div class="sidebar-item"
                     data-cat-id="<?php echo esc_attr( $dept['id'] ); ?>"
                     data-cat-name="<?php echo esc_attr( $dept['name'] ); ?>"
                     data-cat-slug="<?php echo esc_attr( $dept['slug'] ?? '' ); ?>">
                    <a href="<?php echo esc_url( $dept['url'] ); ?>" class="sidebar-link">
                        <?php echo esc_html( $dept['name'] ); ?>
                    </a>
                    <?php if ( $has_sub ) : ?>
                    <button class="sidebar-next" data-level="2"
                            data-cat="<?php echo esc_attr( $dept['id'] ); ?>"
                            data-cat-name="<?php echo esc_attr( $dept['name'] ); ?>"
                            data-cat-slug="<?php echo esc_attr( $dept['slug'] ?? '' ); ?>">
                        <span class="next-icon">›</span>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sub-levels built by JS from inline data — no AJAX, instant -->
        <div id="dynamic-levels"></div>
    </div>

    <!-- Mobile Drawer -->
    <div class="mobile-drawer-overlay" id="mobile-overlay"></div>
    <div class="mobile-drawer" id="mobile-drawer">
        <div class="mobile-drawer-header">
            <span class="mobile-drawer-title">Menu</span>
            <button class="mobile-drawer-close" id="mobile-close">✕</button>
        </div>
        <div class="mobile-drawer-search">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" placeholder="Search products..." value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
                <input type="hidden" name="post_type" value="product">
            </form>
        </div>
        <div class="mobile-drawer-nav">
            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="mobile-nav-link">All Products</a>
            <?php foreach ( $dept_tree as $dept ) : ?>
                <a href="<?php echo esc_url( $dept['url'] ); ?>" class="mobile-nav-link"><?php echo esc_html( $dept['name'] ); ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <main class="site-main" id="main" tabindex="-1">