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

<?php if( have_rows('banner') ): ?>
    <div id="banner">
        <?php while( have_rows('banner') ): the_row(); 
            $title = get_sub_field('title');
        ?>
        <section>
            <?php if( $title ): ?>
                <h1><?php echo $title; ?></h1>
            <?php endif; ?>
            <?php if( have_rows('button') ): ?>
                <?php while( have_rows('button') ): the_row(); 
                    $name = get_sub_field('name');
                    $link = get_sub_field('link');
                ?>
                    <?php if( $name ): ?>
                        <a href="<?php echo $link; ?>" title="<?php echo $name; ?>"><?php echo $name; ?></a>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </section>
            <?php if ( has_post_thumbnail() ) : ?>
                <span class="effect"></span>
                <?php $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>
                <img width="2000" height="1500" src="<?php echo $url; ?>" alt="digiscoping-elk-with-iphone" scale="0" class="banner-image" />
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
<?php endif; ?>



<div id="page">
    <div id="page-content">
        <?php if( have_rows('intro') ): ?>
           <section id="intro">
            <?php while( have_rows('intro') ): the_row();
                $title = get_sub_field('title');
                $subtitle = get_sub_field('subtitle');
            ?>
                <div class="item">
                    <?php if( $title ): ?>
                        <h2><?php echo $title; ?></h2>
                    <?php endif; ?>
                    <?php if( $subtitle ): ?>
                        <h3><?php echo $subtitle; ?></h3>
                    <?php endif; ?>

                    <?php if( have_rows('content') ): ?>
                        <div class="content">
                            <?php while( have_rows('content') ): the_row();
                                $text_l = get_sub_field('text_l');
                                $text_r = get_sub_field('text_r');
                            ?>

                                <?php if( $text_l ): ?>
                                    <div><?php echo $text_l; ?></div>
                                <?php endif; ?>
                                <?php if( $text_r ): ?>
                                    <div><?php echo $text_r; ?></div>
                                <?php endif; ?>

                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
            </section>
        <?php endif; ?>

        <?php if( get_field( "html_block" ) ): ?>
            <div id="html">
                <?php the_field('html_block'); ?>
            </div>
        <?php endif; ?>

        <?php if( have_rows('product_gallery') ): ?>
           <section id="product_gallery">
            <?php while( have_rows('product_gallery') ): the_row();
                $title = get_sub_field('title');
                $subtitle = get_sub_field('subtitle');
            ?>

                <?php if( $title ): ?>
                    <h2><?php echo $title; ?></h2>
                <?php endif; ?>
                <?php if( $subtitle ): ?>
                    <h3><?php echo $subtitle; ?></h3>
                <?php endif; ?>

                <?php if( have_rows('list') ): ?>
                    <ul>
                        <?php while( have_rows('list') ): the_row();
                            $image = get_sub_field('image');
                            $title = get_sub_field('title');
                            $link = get_sub_field('link');
                        ?>
                            <li>
                                <?php if( $link ): ?>
                                <a href="<?php echo $link; ?>" title="<?php echo $title; ?>">
                                    <?php if( $image ): ?>
                                        <img src="<?php echo $image; ?>" alt="<?php echo $title; ?>" />
                                    <?php endif; ?>
                                    <?php if( $title ): ?>
                                        <h2><?php echo $title; ?></h2>
                                    <?php endif; ?>
                                </a>
                                <?php endif; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php endif; ?>

            <?php endwhile; ?>
            </section>
        <?php endif; ?>

    </div>
</div>


<?php get_footer(); ?>