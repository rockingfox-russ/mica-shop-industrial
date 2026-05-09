<?php
/**
 * page.php — Generic page template
 */
get_header();
while ( have_posts() ) : the_post(); ?>
<div class="container" style="padding-top:var(--space-8);padding-bottom:var(--space-12);">
    <?php mica_breadcrumbs(); ?>
    <article>
        <h1 style="font-size:var(--font-size-4xl);margin-bottom:var(--space-6);"><?php the_title(); ?></h1>
        <div class="entry-content" style="line-height:1.75;color:var(--clr-text-muted);">
            <?php the_content(); ?>
        </div>
    </article>
</div>
<?php endwhile;
get_footer();
