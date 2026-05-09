<?php
/**
 * template-parts/shop/active-filters.php
 * Renders the container for removable filter chips.
 * JS (renderActiveTags) populates the content; the empty div is enough for
 * JS-disabled users we still render a server-side fallback.
 *
 * @var array $active_filters
 */
defined( 'ABSPATH' ) || exit;

$f = wp_parse_args( $args['active_filters'] ?? [], [
    'min_price'        => '',
    'max_price'        => '',
    'on_sale'          => false,
    'in_stock'         => false,
    'out_of_stock'     => false,
    'tags'             => [],
    'attributes'       => [],
    'local_attributes' => [],
] );
?>
<div class="active-filters-bar" id="active-filters-bar">
<?php
// Server-side render for initial page load / no-JS fallback.
// JS overwrites this on first interaction.
$chips = [];

if ( $f['min_price'] !== '' || $f['max_price'] !== '' ) {
    $chips[] = [
        'label' => 'R' . ( $f['min_price'] ?: '0' ) . ' – R' . ( $f['max_price'] ?: '∞' ),
        'key'   => 'price',
    ];
}
if ( ! empty( $f['on_sale'] ) )      $chips[] = [ 'label' => __( 'On Promotion', 'micaonline' ), 'key' => 'on_sale' ];
if ( ! empty( $f['in_stock'] ) )     $chips[] = [ 'label' => __( 'In Stock', 'micaonline' ),     'key' => 'in_stock' ];
if ( ! empty( $f['out_of_stock'] ) ) $chips[] = [ 'label' => __( 'Out of Stock', 'micaonline' ), 'key' => 'out_of_stock' ];

foreach ( (array) $f['tags'] as $slug ) {
    $term = get_term_by( 'slug', $slug, 'product_tag' );
    $chips[] = [ 'label' => $term ? $term->name : $slug, 'key' => 'tag_' . $slug ];
}
foreach ( (array) $f['attributes'] as $tax => $vals ) {
    foreach ( (array) $vals as $v ) {
        $chips[] = [ 'label' => $v, 'key' => 'attr_' . $tax . '_' . $v ];
    }
}
foreach ( (array) $f['local_attributes'] as $tax => $vals ) {
    foreach ( (array) $vals as $v ) {
        $chips[] = [ 'label' => $v, 'key' => 'local_' . $tax . '_' . $v ];
    }
}

foreach ( $chips as $chip ) :
?>
    <span class="active-filter-tag" data-filter-key="<?php echo esc_attr( $chip['key'] ); ?>">
        <?php echo esc_html( $chip['label'] ); ?>
        <button type="button" aria-label="<?php esc_attr_e( 'Remove filter', 'micaonline' ); ?>">&times;</button>
    </span>
<?php endforeach; ?>
</div>
