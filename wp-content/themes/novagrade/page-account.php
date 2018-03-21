<?php get_header(); ?>

<?php if( have_rows('banner') ): ?>
    <div id="banner">
        <?php while( have_rows('banner') ): the_row(); 
            $title = get_sub_field('title');
            $content = get_sub_field('content');
        ?>
        <section id="shop-account">
            <?php if( $title ): ?>
                <h1><?php echo $title; ?></h1>
                <?php if ( is_user_logged_in() ) { ?>
                    <ul id="menu">
                        <li><a href="<?php get_site_url(); ?>/account/" title="Overview">Overview</a></li>
                        <li><a href="<?php get_site_url(); ?>/account/edit-address/billing/" title="Edit Billing Address">Edit Billing Address</a></li>
                        <li><a href="<?php get_site_url(); ?>/account/edit-address/shipping/" title="Edit Shipping Address">Edit Shipping Address</a></li>
                    </ul>
                <?php } else { }; ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php the_content(); ?>
                <?php endwhile; ?>
            </section>
            <?php endif; ?>
        </section>
            <?php if ( is_user_logged_in() ) { ?>

            <?php } else { ?>
                <?php if ( has_post_thumbnail() ) : ?>
                    <span class="effect"></span>
                    <?php $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>
                    <img width="2000" height="1500" src="<?php echo $url; ?>" alt="digiscoping-elk-with-iphone" scale="0" class="banner-image" />
                <?php endif; ?>
           <?php }; ?>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
