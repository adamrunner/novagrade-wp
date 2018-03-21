<?php
/*
Template Name: Cart
*/
get_header(); ?>

    <div id="webshop-cart">
        <div id="page-content">

        <?php do_action( 'foundationpress_before_content' ); ?>

        <section class="content">
            <?php if ( have_posts() ) : ?>
                <h1><?php echo get_the_title(); ?></h1>
                <?php the_content(); ?>
            <?php endif; ?>
        </section>

        <?php do_action( 'foundationpress_after_content' ); ?>

        </div>
    </div>

<?php get_footer(); ?>