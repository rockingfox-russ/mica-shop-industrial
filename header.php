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

    <!-- Utility Bar -->
    <!-- <div class="utility-bar">
        <div class="container">
            <div class="utility-bar-inner">
                <div class="utility-left">
                    <span>🇿🇦 Free click & collect at all Mica stores</span>
                    <span class="separator">|</span>
                    <span>🚚 Free delivery on orders over R1000</span>
                </div>
                <div class="utility-right">
                    <?php if (is_user_logged_in()) : ?>
                        <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>">My Account</a>
                    <?php else : ?>
                        <a href="<?php echo esc_url(wp_login_url()); ?>">Sign in</a>
                        <a href="<?php echo esc_url(wp_registration_url()); ?>">Register</a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('cart'))); ?>">Cart</a>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Main Header -->
    <header class="site-header" id="masthead">
        <div class="container">
            <div class="header-wrapper">
                <!-- Logo -->
                <?php 
                $custom_logo_id = get_theme_mod('custom_logo');
                if ($custom_logo_id) : 
                    $logo_image = wp_get_attachment_image($custom_logo_id, 'full', false, array('class' => 'logo-img'));
                    ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" aria-label="Mica Online Home">
                        <?php echo $logo_image; ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo">
                        <span class="logo-text">Mica<span class="logo-accent">Online</span></span>
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