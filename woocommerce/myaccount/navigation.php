<?php
/**
 * woocommerce/myaccount/navigation.php
 */
defined( 'ABSPATH' ) || exit;
$current = WC()->query->get_current_endpoint();
?>
<nav class="account-nav" aria-label="<?php esc_attr_e( 'Account navigation', 'micaonline' ); ?>">
    <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) :
        $is_active = $endpoint === $current || ( 'dashboard' === $endpoint && '' === $current );
        $url       = wc_get_account_endpoint_url( $endpoint );

        $icons = [
            'dashboard'       => 'store',
            'orders'          => 'truck',
            'click-collect'   => 'store',
            'downloads'       => 'check',
            'edit-address'    => 'user',
            'edit-account'    => 'user',
            'customer-logout' => 'x',
        ];
        $icon = mica_icon( $icons[ $endpoint ] ?? 'check' );
    ?>
    <a href="<?php echo esc_url( $url ); ?>"
       class="account-nav-item <?php echo $is_active ? 'active' : ''; ?>"
       <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
        <?php echo $icon; ?>
        <?php echo esc_html( $label ); ?>
    </a>
    <?php endforeach; ?>
</nav>
