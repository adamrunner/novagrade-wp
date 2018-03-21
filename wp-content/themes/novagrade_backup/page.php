<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0
 */

get_header(); ?>

<?php if ( has_post_thumbnail() ) : ?>
    <?php if( have_rows('banner') ): ?>
        <div id="banner">
            <?php while( have_rows('banner') ): the_row(); 
                $title = get_sub_field('title');
                $content = get_sub_field('content');
            ?>
            <section>
                <?php if( $title ): ?>
                    <h1><?php echo $title; ?></h1>
                    <p><?php echo $content; ?></p>
                <?php endif; ?>
                <p><a href="#page" title="Read more">Read more</a></p>
            </section>
            <span class="effect"></span>
            <?php $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>
            <img width="2000" height="1500" src="<?php echo $url; ?>" alt="digiscoping-elk-with-iphone" scale="0" class="banner-image" />
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
    <div
    <?php if ( has_post_thumbnail() ) { ?>
        id="page"
    <?php } else { ?>
        id="page" class="page-nobanner"
    <?php } ?>>
        <div id="page-content">

        <?php do_action( 'foundationpress_before_content' ); ?>

        <?php if( have_rows('intro') ): ?>
           <section id="intro">
            <?php while( have_rows('intro') ): the_row();
                $title = get_sub_field('title');
                $subtitle = get_sub_field('subtitle');
            ?>

                <?php if( $title ): ?>
                    <h2><?php echo $title; ?></h2>
                <?php endif; ?>
                <?php if( $subtitle ): ?>
                    <h3><?php echo $subtitle; ?></h3>
                <?php endif; ?>

            <?php endwhile; ?>
            </section>
        <?php endif; ?>

        <?php if( get_field( 'content_1' ) ): ?>
           <section class="columns_1">
                <div>
                    <?php the_field('content_1'); ?>
                </div>
            </section>
        <?php endif; ?>
        
        <?php if( have_rows('content_2') ): ?>
           <section class="columns_2">
            <?php while( have_rows('content_2') ): the_row();
                $text_r = get_sub_field('text_r');
                $text_l = get_sub_field('text_l');
            ?>

                <?php if( $text_r ): ?>
                    <div><?php echo $text_r; ?></div>
                <?php endif; ?>
                <?php if( $text_l ): ?>
                    <div><?php echo $text_l; ?></div>
                <?php endif; ?>

            <?php endwhile; ?>
            </section>
        <?php endif; ?>

        <?php do_action( 'foundationpress_after_content' ); ?>

        </div>
    </div>

<?php get_footer(); ?>