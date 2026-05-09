    </main><!-- #main -->

    <footer class="site-footer" role="contentinfo">
        <div class="container">
            <div class="footer-grid">

                <!-- Brand -->
                <div class="footer-brand">
                        <?php if ( has_custom_logo() ) : ?>
                            <?php the_custom_logo(); ?>
                        <?php else : ?>
                            <span class="logo-text">
                                mica<span>online</span>
                            </span>
                        <?php endif; ?>
                    <p class="footer-tagline">
                        <?php echo esc_html( get_theme_mod( 'mica_utility_bar' ) ); ?>
                    </p>
                    <div class="footer-payments">
                        <span class="payment-badge">PayFast</span>
                        <!-- <span class="payment-badge">Yoco</span>
                        <span class="payment-badge">EFT</span>
                        <span class="payment-badge">Visa</span>
                        <span class="payment-badge">Mastercard</span> -->
                    </div>
                </div>

                <!-- Shop links -->
                <div>
                    <h3 class="footer-heading"><?php esc_html_e( 'Shop', 'micaonline' ); ?></h3>
                    <ul class="footer-links">
                        <?php
                        $cats = mica_get_categories( 0 );
                        $shown = 0;
                        foreach ( $cats as $cat ) :
                            if ( $shown >= 8 ) break;
                        ?>
                        <li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
                            <?php echo esc_html( $cat->name ); ?>
                        </a></li>
                        <?php $shown++; endforeach; ?>
                        <li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">
                            <?php esc_html_e( 'All Products →', 'micaonline' ); ?>
                        </a></li>
                    </ul>
                </div>

                <!-- Help links -->
                <div>
                    <h3 class="footer-heading"><?php esc_html_e( 'Help', 'micaonline' ); ?></h3>
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'footer-help',
                        'container'      => false,
                        'menu_class'     => 'footer-links',
                        'depth'          => 1,
                        'item_spacing'   => 'discard',
                        'fallback_cb'    => function () {
                            // Fallback until a menu is assigned in Appearance → Menus
                            echo '<ul class="footer-links">';
                            printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ), esc_html__( 'My Account', 'micaonline' ) );
                            printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( wc_get_page_id( 'cart' ) ) ),      esc_html__( 'Cart', 'micaonline' ) );
                            printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( wc_get_page_id( 'checkout' ) ) ),  esc_html__( 'Checkout', 'micaonline' ) );
                            echo '<li><a href="' . esc_url( home_url( '/returns' ) )  . '">' . esc_html__( 'Returns Policy', 'micaonline' )   . '</a></li>';
                            echo '<li><a href="' . esc_url( home_url( '/delivery' ) ) . '">' . esc_html__( 'Click &amp; Collect', 'micaonline' ) . '</a></li>';
                            echo '<li><a href="' . esc_url( home_url( '/faq' ) )      . '">' . esc_html__( 'FAQ', 'micaonline' )               . '</a></li>';
                            echo '</ul>';
                        },
                    ] );
                    ?>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="footer-heading"><?php esc_html_e( 'Contact', 'micaonline' ); ?></h3>
                    <ul class="footer-links">
                        <?php $contact_page = get_page_by_path( 'contact-us' ); ?>
                        <?php if ( $contact_page ) : ?>
                        <li><a href="<?php echo esc_url( get_permalink( $contact_page ) ); ?>"><?php esc_html_e( 'Contact Us', 'micaonline' ); ?></a></li>
                        <?php endif; ?>
                        <li><a href="mailto:<?php echo esc_html( get_theme_mod( 'mica_store_email' ) ); ?>"><?php echo esc_html( get_theme_mod( 'mica_store_email' ) ); ?></a></li>
                        <li><a href="tel:<?php echo esc_html( get_theme_mod( 'mica_store_phone' ) ); ?>"><?php echo esc_html( get_theme_mod( 'mica_store_phone' ) ); ?></a></li>
                        <li style="color:rgba(255,255,255,.5);font-size:.75rem;margin-top:.5rem;"><?php echo get_theme_mod( 'mica_store_hours' ); ?></li>
                    </ul>
                </div>

            </div>

            <div class="footer-bottom">
                <span>© <?php echo date( 'Y' ); ?> micaonline. All rights reserved.</span>
                <div class="flex gap-4">
                    <a href="/privacy-policy"><?php esc_html_e( 'Privacy Policy', 'micaonline' ); ?></a>
                    <a href="/terms"><?php esc_html_e( 'Terms', 'micaonline' ); ?></a>
                </div>
            </div>
        </div>
    </footer>

</div><!-- #page .site -->

<?php wp_footer(); ?>
</body>
</html>
