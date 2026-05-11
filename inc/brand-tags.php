<?php
/**
 * inc/brand-tags.php
 * Adds logo image support to WooCommerce product tags (used as brand filter).
 */

defined( 'ABSPATH' ) || exit;

// Register term meta
add_action( 'init', function () {
    register_term_meta( 'product_tag', 'mica_tag_logo', [
        'type'              => 'integer',
        'single'            => true,
        'sanitize_callback' => 'absint',
    ] );
} );

// Enqueue media uploader on taxonomy screens
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( in_array( $hook, [ 'edit-tags.php', 'term.php' ], true ) &&
         isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'product_tag' ) {
        wp_enqueue_media();
        wp_add_inline_script( 'jquery-core', '
            jQuery(function($){
                $(document).on("click", ".mica-tag-logo-btn", function(e){
                    e.preventDefault();
                    var btn    = $(this);
                    var input  = btn.siblings(".mica-tag-logo-id");
                    var preview = btn.siblings(".mica-tag-logo-preview");
                    var frame  = wp.media({ title:"Select Brand Logo", button:{text:"Use this image"}, multiple:false });
                    frame.on("select", function(){
                        var att = frame.state().get("selection").first().toJSON();
                        input.val(att.id);
                        preview.attr("src", att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url).show();
                        btn.text("Change logo");
                        btn.siblings(".mica-tag-logo-remove").show();
                    });
                    frame.open();
                });
                $(document).on("click", ".mica-tag-logo-remove", function(e){
                    e.preventDefault();
                    $(this).siblings(".mica-tag-logo-id").val("");
                    $(this).siblings(".mica-tag-logo-preview").hide().attr("src","");
                    $(this).siblings(".mica-tag-logo-btn").text("Add logo");
                    $(this).hide();
                });
            });
        ' );
    }
} );

// Edit form field (existing tag)
add_action( 'product_tag_edit_form_fields', function ( WP_Term $term ) {
    $logo_id  = (int) get_term_meta( $term->term_id, 'mica_tag_logo', true );
    $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
    ?>
    <tr class="form-field">
        <th scope="row"><label>Brand Logo</label></th>
        <td>
            <input type="hidden" name="mica_tag_logo" class="mica-tag-logo-id" value="<?php echo esc_attr( $logo_id ?: '' ); ?>">
            <img class="mica-tag-logo-preview" src="<?php echo esc_url( $logo_url ); ?>"
                 style="max-height:60px;max-width:160px;display:<?php echo $logo_url ? 'block' : 'none'; ?>;margin-bottom:8px;">
            <button type="button" class="button mica-tag-logo-btn"><?php echo $logo_url ? 'Change logo' : 'Add logo'; ?></button>
            <button type="button" class="button mica-tag-logo-remove" style="display:<?php echo $logo_url ? 'inline-block' : 'none'; ?>;margin-left:4px;">Remove</button>
            <p class="description">Displayed in the homepage brand strip. Recommended: transparent PNG, min 200px wide.</p>
        </td>
    </tr>
    <?php
} );

// Add form field (new tag)
add_action( 'product_tag_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label>Brand Logo</label>
        <input type="hidden" name="mica_tag_logo" class="mica-tag-logo-id" value="">
        <img class="mica-tag-logo-preview" src="" style="max-height:60px;display:none;margin-bottom:8px;">
        <button type="button" class="button mica-tag-logo-btn">Add logo</button>
        <button type="button" class="button mica-tag-logo-remove" style="display:none;margin-left:4px;">Remove</button>
        <p class="description">Displayed in the homepage brand strip.</p>
    </div>
    <?php
} );

// Save
add_action( 'edited_product_tag', 'mica_brand_tag_logo_save' );
add_action( 'created_product_tag', 'mica_brand_tag_logo_save' );
function mica_brand_tag_logo_save( int $term_id ): void {
    if ( ! isset( $_POST['mica_tag_logo'] ) ) return;
    $val = absint( $_POST['mica_tag_logo'] );
    if ( $val ) {
        update_term_meta( $term_id, 'mica_tag_logo', $val );
    } else {
        delete_term_meta( $term_id, 'mica_tag_logo' );
    }
}
