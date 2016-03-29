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
           <ul id="faq-menu" class="tabs" data-tabs="">
            <?php while( have_rows('content') ): the_row();
                $tab = get_sub_field('tab');
                $slug = get_sub_field('slug');
            ?>

                <?php if( $tab ): ?>
                    <li class="tabs-title">
                        <a href="#panel<?php echo $slug; ?>">
                            <?php echo $tab; ?>
                        </a>
                    </li>
                <?php endif; ?>

            <?php endwhile; ?>
            </ul>
        <?php endif; ?>

        <?php if( have_rows('content') ): ?>
           <div class="tabs-content" data-tabs-content="example-tabs">
            <?php while( have_rows('content') ): the_row();
                $tab = get_sub_field('tab');
                $slug = get_sub_field('slug');
            ?>
                <div class="tabs-panel" id="panel<?php echo $slug; ?>">
                <?php if( $tab ): ?>
                    <h2><?php echo $tab; ?></h2>
                <?php endif; ?>

                <?php if( have_rows('questions') ): ?>
                    <ul class="accordion" data-accordion role="tablist">
                    <?php while( have_rows('questions') ): the_row();
                        $question = get_sub_field('question');
                        $answer = get_sub_field('answer');
                        $slug = get_sub_field('slug');
                    ?>

                        <li class="accordion-item">
                            <a href="#question<?php echo $slug; ?>" role="tab" class="accordion-title" id="question<?php echo $slug; ?>-heading" aria-controls="question<?php echo $slug; ?>">
                                <?php echo $question; ?>
                            </a>
                            <div id="question<?php echo $slug; ?>" class="accordion-content" role="tabpanel" data-tab-content aria-labelledby="question<?php echo $slug; ?>-heading">
                                <?php echo $answer; ?>
                            </div>
                        </li>

                    <?php endwhile; ?>
                    </ul>
                <?php endif; ?>

                </div>
            <?php endwhile; ?>
            </div>
        <?php endif; ?>

        </div>
    </div>

<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script>
    $('ul#faq-menu li:first-child').addClass('is-active');
    $('div.tabs-content div.tabs-panel:first-child').addClass('is-active');
</script>

<?php get_footer(); ?>