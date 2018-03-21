<?php
/*
Template Name: Product overview
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
                    <p class="hide-for-small"><?php echo $content; ?></p>
                <?php endif; ?>
                <p class="hide-for-small"><a href="#page" title="Read more">Read more</a></p>
            </section>
                <span class="effect"></span>
                <?php the_post_thumbnail(); ?>
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
                    <h2 class="hide-for-small"><?php echo $title; ?></h2>
                <?php endif; ?>
                <?php if( $subtitle ): ?>
                    <h3 class="hide-for-small"><?php echo $subtitle; ?></h3>
                <?php endif; ?>

            <?php endwhile; ?>
            </section>
        <?php endif; ?>
        
        <?php if( have_rows('products') ): ?>
           <section class="products">
               <ul>
            <?php while( have_rows('products') ): the_row();
                $image = get_sub_field('image');
                $title = get_sub_field('title');
                $content_list = get_sub_field('content_list');
                $link = get_sub_field('link');
            ?>
                <li>
                    <?php if( $image ): ?>
                        <div class="product_image">
                            <img src="<?php echo $image; ?>" alt="<?php echo $title; ?>" />
                            </div>
                    <?php endif; ?>
                    <div class="product_content">
                        <?php if( $title ): ?>
                            <h2><?php echo $title; ?></h2>
                        <?php endif; ?>

                        <?php if( have_rows('content_list') ): ?>
                           <ul>
                            <?php while( have_rows('content_list') ): the_row();
                                $list = get_sub_field('list');
                            ?>

                                <?php if( $list ): ?>
                                    <li><?php echo $list; ?></li>
                                <?php endif; ?>

                            <?php endwhile; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if( $link ): ?>
                            <a href="<?php echo $link; ?>" title="View more information" class="button">View more information</a>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endwhile; ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php do_action( 'foundationpress_after_content' ); ?>

        </div>
    </div>


<?php get_footer(); ?>
