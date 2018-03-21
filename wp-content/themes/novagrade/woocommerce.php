<?php get_header(); ?>

    <div id="webshop">

                <?php
                    $image = get_field('background_image');
                if( !empty($image) ): ?>
                    <div id="banner">
                        <span class="effect"></span>
                        <img src="<?php echo $image; ?>" alt="test" class="attachment-post-thumbnail wp-post-image" />
                    </div>
                <?php endif; ?>

        <div id="page-content">

        <?php do_action( 'foundationpress_before_content' ); ?>

        <section class="products-single">
            <?php if ( have_posts() ) : ?>
                <?php woocommerce_content(); ?>
            <?php endif; ?>
        </section>

        <?php do_action( 'foundationpress_after_content' ); ?>

        </div>
    </div>


<?php get_footer(); ?>
