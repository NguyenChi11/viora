<?php
get_header();
?>

<main id="primary" class="site-main viora-portfolio-single">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php
            $post_id = get_the_ID();
            $image_1 = trim((string) get_post_meta($post_id, 'image_1', true));
            $image_2 = trim((string) get_post_meta($post_id, 'image_2', true));

            $portfolio_excerpt = has_excerpt()
                ? get_the_excerpt()
                : wp_trim_words(wp_strip_all_tags((string) get_the_content()), 28);
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class('viora-portfolio-single__article'); ?>>
                <header class="viora-portfolio-single__header">
                    <h1 class="viora-portfolio-single__title"><?php the_title(); ?></h1>

                    <?php if ($portfolio_excerpt !== '') : ?>
                        <p class="viora-portfolio-single__excerpt"><?php echo esc_html($portfolio_excerpt); ?></p>
                    <?php endif; ?>
                </header>

                <?php if (has_post_thumbnail()) : ?>
                    <figure class="viora-portfolio-single__featured">
                        <?php the_post_thumbnail('full', array('class' => 'viora-portfolio-single__featured-image')); ?>
                    </figure>
                <?php endif; ?>

                <div class="viora-portfolio-single__content">
                    <?php the_content(); ?>
                </div>

                <?php if ($image_1 !== '' || $image_2 !== '') : ?>
                    <section class="viora-portfolio-single__gallery" aria-label="Portfolio extra images">
                        <div class="viora-portfolio-single__gallery-grid">
                            <?php if ($image_1 !== '') : ?>
                                <figure class="viora-portfolio-single__gallery-item">
                                    <img src="<?php echo esc_url($image_1); ?>" alt="<?php echo esc_attr(get_the_title() . ' image 1'); ?>" loading="lazy">
                                </figure>
                            <?php endif; ?>

                            <?php if ($image_2 !== '') : ?>
                                <figure class="viora-portfolio-single__gallery-item">
                                    <img src="<?php echo esc_url($image_2); ?>" alt="<?php echo esc_attr(get_the_title() . ' image 2'); ?>" loading="lazy">
                                </figure>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </article>

            <section class="viora-portfolio-single__related" aria-label="Other portfolio posts">
                <h2 class="viora-portfolio-single__related-title"><?php esc_html_e('5 Portfolio Items', 'viora'); ?></h2>

                <?php
                $related_query = new WP_Query(array(
                    'post_type'           => 'portfolio',
                    'post_status'         => 'publish',
                    'posts_per_page'      => 5,
                    'ignore_sticky_posts' => true,
                ));
                ?>

                <?php if ($related_query->have_posts()) : ?>
                    <div class="viora-portfolio-single__related-grid">
                        <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                            <?php
                            $related_id = get_the_ID();
                            $related_image = has_post_thumbnail($related_id)
                                ? (string) get_the_post_thumbnail_url($related_id, 'large')
                                : '';

                            if ($related_image === '') {
                                $related_image = trim((string) get_post_meta($related_id, 'image_1', true));
                            }

                            if ($related_image === '') {
                                $related_image = trim((string) get_post_meta($related_id, 'image_2', true));
                            }

                            $related_excerpt = has_excerpt()
                                ? get_the_excerpt()
                                : wp_trim_words(wp_strip_all_tags((string) get_the_content()), 16);
                            ?>

                            <article <?php post_class('viora-portfolio-single__card'); ?>>
                                <a class="viora-portfolio-single__card-link" href="<?php the_permalink(); ?>">
                                    <?php if ($related_image !== '') : ?>
                                        <figure class="viora-portfolio-single__card-figure">
                                            <img src="<?php echo esc_url($related_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
                                        </figure>
                                    <?php endif; ?>

                                    <h3 class="viora-portfolio-single__card-title"><?php the_title(); ?></h3>

                                    <?php if ($related_excerpt !== '') : ?>
                                        <p class="viora-portfolio-single__card-excerpt"><?php echo esc_html($related_excerpt); ?></p>
                                    <?php endif; ?>
                                </a>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <p class="viora-portfolio-single__empty"><?php esc_html_e('No other portfolio items found.', 'viora'); ?></p>
                <?php endif; ?>
            </section>
        <?php endwhile; ?>
    <?php endif; ?>
</main>

<?php
get_footer();
