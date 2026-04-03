<div class="wrap viora-header-admin">
    <h1><?php echo esc_html__('Header', 'viora'); ?></h1>

    <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__('Header settings saved.', 'viora'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="viora_save_header">
        <?php wp_nonce_field('viora_header_save'); ?>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="header_logo"><?php echo esc_html__('Logo', 'viora'); ?></label>
                    </th>
                    <td>
                        <input type="hidden" id="header_logo" name="header_logo" value="<?php echo esc_attr($logo_id); ?>">
                        <button type="button" class="button" id="select_header_logo"><?php echo esc_html__('Choose Image', 'viora'); ?></button>
                        <button type="button" class="button" id="remove_header_logo"><?php echo esc_html__('Remove', 'viora'); ?></button>
                        <div id="header_logo_preview">
                            <?php if ($logo_url): ?>
                                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php esc_attr_e('Header logo preview', 'viora'); ?>">
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="viora_header_title"><?php echo esc_html__('Title', 'viora'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="viora_header_title" name="viora_header_title" class="regular-text" value="<?php echo esc_attr($text); ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="viora_header_description"><?php echo esc_html__('Description', 'viora'); ?></label>
                    </th>
                    <td>
                        <textarea id="viora_header_description" name="viora_header_description" class="large-text" rows="4"><?php echo esc_textarea($desc); ?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(__('Save Header', 'viora')); ?>
    </form>
</div>