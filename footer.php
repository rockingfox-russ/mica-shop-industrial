    </main><!-- #main -->

    <footer class="site-footer-b" role="contentinfo">
        <div class="footer-b-inner">

            <!-- B's bold headline + columns -->
            <div class="footer-b-grid">
                <div class="footer-b-brand">
                    <h2 class="footer-b-headline">
                        Your neighbourhood<br>hardware store.
                        <em class="serif-italic" style="color:var(--clr-yellow);"> Online too.</em>
                    </h2>
                    <p class="footer-b-tagline">
                        Family-run since 1988. Stocked locally, dispatched daily, with the same advice you'd get over the counter — only typed.
                    </p>
                    <div class="footer-payments">
                        <span class="payment-badge">PayFast</span>
                        <span class="payment-badge">SSL Secured</span>
                    </div>
                </div>

                <!-- Shop -->
                <div>
                    <h3 class="footer-b-heading">Shop</h3>
                    <ul class="footer-b-links">
                        <?php
                        $cats = mica_get_categories( 0 );
                        $shown = 0;
                        foreach ( $cats as $cat ) :
                            if ( $shown >= 7 ) break;
                        ?>
                        <li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
                        <?php $shown++; endforeach; ?>
                        <li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">All Products →</a></li>
                    </ul>
                </div>

                <!-- Help -->
                <div>
                    <h3 class="footer-b-heading">Help</h3>
                    <ul class="footer-b-links">
                        <li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>">My Account</a></li>
                        <li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'cart' ) ) ); ?>">Cart</a></li>
                        <li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'checkout' ) ) ); ?>">Checkout</a></li>
                        <li><a href="<?php echo esc_url( home_url('/returns') ); ?>">Returns Policy</a></li>
                        <li><a href="<?php echo esc_url( home_url('/click-collect') ); ?>">Click &amp; Collect</a></li>
                        <li><a href="<?php echo esc_url( home_url('/faq') ); ?>">FAQ</a></li>
                    </ul>
                </div>

                <!-- Visit -->
                <div>
                    <h3 class="footer-b-heading">Visit</h3>
                    <ul class="footer-b-links">
                        <li><a href="#">Find a store</a></li>
                        <li><a href="#">Tool hire</a></li>
                        <li><a href="#">Paint mixing</a></li>
                        <li><a href="#">Key cutting</a></li>
                        <li><a href="#">Workshops</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="footer-b-heading">Hello</h3>
                    <ul class="footer-b-links">
                        <?php $contact_page = get_page_by_path( 'contact-us' ); ?>
                        <?php if ( $contact_page ) : ?>
                        <li><a href="<?php echo esc_url( get_permalink( $contact_page ) ); ?>">Contact Us</a></li>
                        <?php endif; ?>
                        <?php if ( get_theme_mod( 'mica_store_email' ) ) : ?>
                        <li><a href="mailto:<?php echo esc_attr( get_theme_mod( 'mica_store_email' ) ); ?>"><?php echo esc_html( get_theme_mod( 'mica_store_email' ) ); ?></a></li>
                        <?php endif; ?>
                        <?php if ( get_theme_mod( 'mica_store_phone' ) ) : ?>
                        <li><a href="tel:<?php echo esc_attr( get_theme_mod( 'mica_store_phone' ) ); ?>"><?php echo esc_html( get_theme_mod( 'mica_store_phone' ) ); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="footer-b-bottom">
                <span>© <?php echo date( 'Y' ); ?> Mica Hardware Co. · Built for WooCommerce on Xneelo</span>
                <span>Let us show you how.</span>
            </div>
        </div>
    </footer>

</div><!-- #page .site -->

<?php wp_footer(); ?>
</body>
</html>
