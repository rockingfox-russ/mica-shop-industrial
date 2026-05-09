<?php
/**
 * woocommerce/myaccount/my-account.php
 */
defined( 'ABSPATH' ) || exit;

// do_action( 'woocommerce_account_navigation' );
?>
    <?php mica_breadcrumbs(); ?>
    <div class="account-layout">
        <?php do_action( 'woocommerce_account_navigation' ); ?>
        <div class="account-content">
            <?php
                do_action( 'woocommerce_account_content' );
            ?>
        </div>
    </div>
