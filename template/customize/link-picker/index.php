<div class="viora-link-popup">
    <p class="viora-banner-field"><label><?php echo esc_html__('URL', 'viora'); ?></label><input type="url" id="viora-link-url" class="regular-text"
            placeholder="https://..."></p>
    <p class="viora-banner-field"><label><?php echo esc_html__('Text', 'viora'); ?></label><input type="text" id="viora-link-text" class="regular-text"
            placeholder="<?php echo esc_attr__('Link text', 'viora'); ?>"></p>
    <p class="viora-banner-field"><label><input type="checkbox" id="viora-link-target"> <?php echo esc_html__('Open in new tab (_blank)', 'viora'); ?></label></p>
    <h4><?php echo esc_html__('Or link to existing content', 'viora'); ?></h4>
    <p class="viora-banner-field"><label><?php echo esc_html__('Search', 'viora'); ?></label><input type="text" id="viora-link-search"
            class="regular-text" placeholder="<?php echo esc_attr__('Enter keyword...', 'viora'); ?>"></p>
    <?php
    $__items = array();
    $__pages = get_pages(array('number' => 0));
    foreach ($__pages as $__p) {
        $__items[] = array('title' => get_the_title($__p), 'url' => get_permalink($__p), 'type' => 'page');
    }
    $__posts = get_posts(array('post_type' => 'post', 'posts_per_page' => -1));
    foreach ($__posts as $__po) {
        $__items[] = array('title' => get_the_title($__po), 'url' => get_permalink($__po), 'type' => 'post');
    }
    $__types = get_post_types(array('public' => true), 'names');
    if (is_array($__types) && in_array('blog', $__types, true)) {
        $__blogs = get_posts(array('post_type' => 'blog', 'posts_per_page' => -1));
        foreach ($__blogs as $__b) {
            $__items[] = array('title' => get_the_title($__b), 'url' => get_permalink($__b), 'type' => 'blog');
        }
    }
    if (is_array($__types) && in_array('project', $__types, true)) {
        $__projects = get_posts(array('post_type' => 'project', 'posts_per_page' => -1));
        foreach ($__projects as $__pr) {
            $__items[] = array('title' => get_the_title($__pr), 'url' => get_permalink($__pr), 'type' => 'project');
        }
    }
    if (is_array($__types) && in_array('material', $__types, true)) {
        $__materials = get_posts(array('post_type' => 'material', 'posts_per_page' => -1));
        foreach ($__materials as $__ma) {
            $__items[] = array('title' => get_the_title($__ma), 'url' => get_permalink($__ma), 'type' => 'material');
        }
    }
    $posts_page_id = (int) get_option('page_for_posts');
    if ($posts_page_id) {
        $__items[] = array('title' => get_the_title($posts_page_id), 'url' => get_permalink($posts_page_id), 'type' => 'blog');
    }
    $__initial_count = count($__items);
    ?>
    <div id="viora-link-results" class="results" data-initial-count="<?php echo esc_attr($__initial_count); ?>">
        <?php if (!empty($__items)) {
            foreach ($__items as $__it) {
                $t = isset($__it['title']) ? $__it['title'] : '';
                $u = isset($__it['url']) ? $__it['url'] : '';
                $ty = isset($__it['type']) ? $__it['type'] : '';
                echo '<div class="result"><div><div>' . esc_html($t) . ($ty ? '<span class="chip">' . esc_html(strtoupper($ty)) . '</span>' : '') . '</div><div class="meta">' . esc_html($u) . '</div></div><div><button type="button" class="button viora-link-pick" data-url="' . esc_url($u) . '" data-title="' . esc_attr($t) . '">' . esc_html__('Select', 'viora') . '</button></div></div>';
            }
        } else {
            echo '<p style="color:#888;margin:6px">' . esc_html__('No results found.', 'viora') . '</p>';
        } ?>
    </div>
    <div class="actions"><button type="button" class="button button-primary" id="viora-link-apply"><?php echo esc_html__('Apply', 'viora'); ?></button>
        <button type="button" class="button" id="viora-link-close"><?php echo esc_html__('Close', 'viora'); ?></button>
    </div>
</div>