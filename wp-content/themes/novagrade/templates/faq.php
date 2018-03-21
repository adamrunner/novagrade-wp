<?php
/*
Template Name: FAQ
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
                <p><a href="#" title="Read more">Read more</a></p>
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
                    <h2><?php echo $title; ?></h2>
                <?php endif; ?>
                <?php if( $subtitle ): ?>
                    <h3><?php echo $subtitle; ?></h3>
                <?php endif; ?>

            <?php endwhile; ?>
            </section>
        <?php endif; ?>

        <?php if( have_rows('content') ): ?>
           <ul class="tabs" data-tab>
            <?php while( have_rows('content') ): the_row();
                $tab = get_sub_field('tab');
                $slug = get_sub_field('slug');
            ?>

                <?php if( $tab ): ?>
               <li class="tab-title"><a href="#panel<?php echo $slug; ?>"><?php echo $tab; ?></a></li>
                <?php endif; ?>

            <?php endwhile; ?>
            </ul>
        <?php endif; ?>

        <?php if( have_rows('content') ): ?>
           <div class="tabs-content">
            <?php while( have_rows('content') ): the_row();
                $tab = get_sub_field('tab');
                $slug = get_sub_field('slug');
            ?>
                <div class="content" id="panel<?php echo $slug; ?>">
                <?php if( $tab ): ?>
                    <h2><?php echo $tab; ?></h2>
                <?php endif; ?>

                <?php if( have_rows('questions') ): ?>
                   <dl class="accordion" data-accordion>
                    <?php while( have_rows('questions') ): the_row();
                        $question = get_sub_field('question');
                        $answer = get_sub_field('answer');
                        $slug = get_sub_field('slug');
                    ?>

                        <dd class="accordion-navigation">
                            <a href="#question<?php echo $slug; ?>"><?php echo $question; ?></a>
                            <div id="question<?php echo $slug; ?>" class="content">
                                <?php echo $answer; ?>
                            </div>
                        </dd>

                    <?php endwhile; ?>
                    </dl>
                <?php endif; ?>

                </div>
            <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <?php do_action( 'foundationpress_after_content' ); ?>

        </div>
    </div>

<script>
    $('div.tabs-content div:first-child').addClass('active');
    $('ul.tabs li:first-child').addClass('active');
</script>

<?php get_footer(); ?>