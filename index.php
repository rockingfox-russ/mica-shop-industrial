<?php // index.php — fallback template
get_header();
if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        ?><article id="post-<?php the_ID(); ?>" <?php post_class( 'container' ); ?> style="padding:2rem 0;">
            <h1><?php the_title(); ?></h1>
            <div><?php the_content(); ?></div>
        </article><?php
    endwhile;
endif;
get_footer();
