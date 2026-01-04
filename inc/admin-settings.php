<?php
/**
 * Admin Settings for VJ Chat Order
 * 
 * @package VJ_Chat_Order
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitize hex color with smart fallback
 */
function vj_chat_sanitize_hex_color($color, $key = '', $default = '')
{
    // 3 or 6 hex digits
    if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
        return $color;
    }

    // Invalid input: try to revert to saved value
    if (!empty($key)) {
        $saved = get_option($key);
        if ($saved && preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $saved)) {
            return $saved;
        }
    }

    // Fallback to default
    return $default;
}

/**
 * Add settings menu under Settings
 */
function vj_chat_add_settings_menu()
{
    add_options_page(
        __('Chat Order Settings', 'vj-chat-order'),
        __('Chat Order', 'vj-chat-order'),
        'manage_options',
        'vj-chat-settings',
        'vj_chat_render_settings_page'
    );
}
add_action('admin_menu', 'vj_chat_add_settings_menu');

/**
 * Enqueue media uploader and admin styles on settings page
 */
function vj_chat_admin_enqueue_scripts($hook)
{
    if ($hook !== 'settings_page_vj-chat-settings') {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_style(
        'vj-chat-admin-style',
        VJ_CHAT_PLUGIN_URL . 'assets/css/admin-style.css',
        array(),
        VJ_CHAT_VERSION
    );

    wp_enqueue_script(
        'vj-chat-admin-script',
        VJ_CHAT_PLUGIN_URL . 'assets/js/admin-script.js',
        array('jquery'),
        VJ_CHAT_VERSION,
        true
    );

    wp_localize_script('vj-chat-admin-script', 'vjChatAdminData', array(
        'defaultIcon' => vj_chat_get_default_icon_url(),
        'uploaderTitle' => __('Select Chat Icon', 'vj-chat-order'),
        'uploaderButton' => __('Use This Icon', 'vj-chat-order'),
        'activeTab' => vj_chat_get_user_active_tab(),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('vj_chat_tab_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'vj_chat_admin_enqueue_scripts');

/**
 * Register Settings and Fields
 */
function vj_chat_register_settings_init()
{
    // Register Settings
    register_setting('vj_chat_settings_group', 'vj_chat_phone_number', array(
        'sanitize_callback' => 'vj_chat_sanitize_phone',
        'default' => '947000000000'
    ));

    register_setting('vj_chat_settings_group', 'vj_chat_button_text', array(
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'Order via WhatsApp'
    ));

    register_setting('vj_chat_settings_group', 'vj_chat_icon_url', array(
        'sanitize_callback' => 'esc_url_raw',
        'default' => ''
    ));

    register_setting('vj_chat_settings_group', 'vj_chat_intro_message', array(
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => 'Hello, I\'d like to place an order:'
    ));

    // Design Settings
    register_setting('vj_chat_settings_group', 'vj_chat_bg_color', array(
        'sanitize_callback' => function ($val) {
            return vj_chat_sanitize_hex_color($val, 'vj_chat_bg_color', '#25D366');
        },
        'default' => '#25D366'
    ));

    register_setting('vj_chat_settings_group', 'vj_chat_text_color', array(
        'sanitize_callback' => function ($val) {
            return vj_chat_sanitize_hex_color($val, 'vj_chat_text_color', '#ffffff');
        },
        'default' => '#ffffff'
    ));

    register_setting('vj_chat_settings_group', 'vj_chat_hover_color', array(
        'sanitize_callback' => function ($val) {
            return vj_chat_sanitize_hex_color($val, 'vj_chat_hover_color', '#1ebe5d');
        },
        'default' => '#1ebe5d'
    ));

    register_setting('vj_chat_settings_group', 'vj_chat_border_radius', array(
        'sanitize_callback' => 'absint',
        'default' => 8
    ));

    register_setting('vj_chat_settings_group', 'vj_chat_font_size', array(
        'sanitize_callback' => 'absint',
        'default' => 16
    ));

    register_setting('vj_chat_settings_group', 'vj_chat_margin_top', array(
        'sanitize_callback' => 'absint',
        'default' => 15
    ));

    register_setting('vj_chat_settings_group', 'vj_chat_margin_bottom', array(
        'sanitize_callback' => 'absint',
        'default' => 15
    ));

    register_setting('vj_chat_settings_group', 'vj_chat_padding_vertical', array(
        'sanitize_callback' => 'absint',
        'default' => 14
    ));

    register_setting('vj_chat_settings_group', 'vj_chat_padding_horizontal', array(
        'sanitize_callback' => 'absint',
        'default' => 24
    ));

    // ===== Message Customization Settings =====
    $message_settings = array(
        'vj_chat_label_product' => __('Product', 'vj-chat-order'),
        'vj_chat_icon_product' => '🛒',
        'vj_chat_label_quantity' => _x('Quantity', 'WhatsApp message label', 'vj-chat-order'),
        'vj_chat_icon_quantity' => '🔢',
        'vj_chat_label_price' => _x('Price', 'WhatsApp message label', 'vj-chat-order'),
        'vj_chat_icon_price' => '💰',
        'vj_chat_label_total' => _x('Total', 'WhatsApp message label', 'vj-chat-order'),
        'vj_chat_icon_total' => '💵',
        'vj_chat_label_link' => _x('Link', 'WhatsApp message label', 'vj-chat-order'),
        'vj_chat_icon_link' => '🔗'
    );

    foreach ($message_settings as $key => $default) {
        register_setting('vj_chat_settings_group', $key, array(
            'sanitize_callback' => 'sanitize_text_field', // Assuming icons and labels are text fields
            'default' => $default
        ));
    }

    // ===== General Section =====
    add_settings_section(
        'vj_chat_main_section',
        __('General Settings', 'vj-chat-order'),
        'vj_chat_section_callback',
        'vj-chat-settings'
    );

    // ===== Design Section =====
    add_settings_section(
        'vj_chat_design_section',
        __('Button Design', 'vj-chat-order'),
        'vj_chat_design_section_callback',
        'vj-chat-settings'
    );

    // ===== General Fields =====
    add_settings_field(
        'vj_chat_phone_number',
        __('WhatsApp Phone Number', 'vj-chat-order'),
        'vj_chat_phone_field_callback',
        'vj-chat-settings',
        'vj_chat_main_section'
    );

    add_settings_field(
        'vj_chat_button_text',
        __('Button Text', 'vj-chat-order'),
        'vj_chat_button_text_field_callback',
        'vj-chat-settings',
        'vj_chat_main_section'
    );

    add_settings_field(
        'vj_chat_icon_url',
        __('WhatsApp Icon URL', 'vj-chat-order'),
        'vj_chat_icon_url_field_callback',
        'vj-chat-settings',
        'vj_chat_main_section'
    );

    add_settings_field(
        'vj_chat_intro_message',
        __('Custom Intro Message', 'vj-chat-order'),
        'vj_chat_intro_message_field_callback',
        'vj-chat-settings',
        'vj_chat_main_section'
    );

    // ===== Design Fields =====
    add_settings_field(
        'vj_chat_bg_color',
        __('Background Color', 'vj-chat-order'),
        'vj_chat_bg_color_field_callback',
        'vj-chat-settings',
        'vj_chat_design_section'
    );

    add_settings_field(
        'vj_chat_text_color',
        __('Text Color', 'vj-chat-order'),
        'vj_chat_text_color_field_callback',
        'vj-chat-settings',
        'vj_chat_design_section'
    );

    add_settings_field(
        'vj_chat_hover_color',
        __('Hover Color', 'vj-chat-order'),
        'vj_chat_hover_color_field_callback',
        'vj-chat-settings',
        'vj_chat_design_section'
    );

    add_settings_field(
        'vj_chat_border_radius',
        __('Border Radius', 'vj-chat-order'),
        'vj_chat_border_radius_field_callback',
        'vj-chat-settings',
        'vj_chat_design_section'
    );

    add_settings_field(
        'vj_chat_font_size',
        __('Font Size', 'vj-chat-order'),
        'vj_chat_font_size_field_callback',
        'vj-chat-settings',
        'vj_chat_design_section'
    );

    add_settings_field(
        'vj_chat_margin',
        __('Margin (Top / Bottom)', 'vj-chat-order'),
        'vj_chat_margin_field_callback',
        'vj-chat-settings',
        'vj_chat_design_section'
    );

    add_settings_field(
        'vj_chat_padding',
        __('Padding (Vertical / Horizontal)', 'vj-chat-order'),
        'vj_chat_padding_field_callback',
        'vj-chat-settings',
        'vj_chat_design_section'
    );
}
add_action('admin_init', 'vj_chat_register_settings_init');

/**
 * Sanitize Phone Number
 */
function vj_chat_sanitize_phone($input)
{
    $sanitized = sanitize_text_field($input);
    if (!preg_match('/^[0-9]{10,15}$/', $sanitized)) {
        add_settings_error(
            'vj_chat_phone_number',
            'vj_chat_phone_error',
            __('Invalid phone number. Please enter 10-15 digits only.', 'vj-chat-order')
        );
        return get_option('vj_chat_phone_number');
    }
    return $sanitized;
}

/**
 * Section callbacks
 */
function vj_chat_section_callback()
{
    echo '<p>' . __('Configure the Chat Order button that appears on your WooCommerce product pages.', 'vj-chat-order') . '</p>';
}

function vj_chat_design_section_callback()
{
    echo '<p>' . __('Customize the appearance of your WhatsApp button.', 'vj-chat-order') . '</p>';
}

/**
 * General field callbacks
 */
function vj_chat_phone_field_callback()
{
    $value = get_option('vj_chat_phone_number', '947000000000');
    echo '<input type="text" name="vj_chat_phone_number" value="' . esc_attr($value) . '" class="regular-text" placeholder="947000000000">';
    echo '<p class="description">' . __('Enter the WhatsApp number with country code (no + or spaces). Example: 947000000000', 'vj-chat-order') . '</p>';
}

function vj_chat_button_text_field_callback()
{
    $value = get_option('vj_chat_button_text', 'Order via WhatsApp');
    echo '<input type="text" name="vj_chat_button_text" value="' . esc_attr($value) . '" class="regular-text" placeholder="' . esc_attr__('Order via WhatsApp', 'vj-chat-order') . '">';
    echo '<p class="description">' . __('Text displayed on the WhatsApp button.', 'vj-chat-order') . '</p>';
}

function vj_chat_icon_url_field_callback()
{
    $default_icon = vj_chat_get_default_icon_url();
    $saved_value = get_option('vj_chat_icon_url', '');
    $display_url = !empty($saved_value) ? $saved_value : $default_icon;
    ?>
    <div class="vj-chat-icon-upload-wrap">
        <input type="url" id="vj_chat_icon_url" name="vj_chat_icon_url" value="<?php echo esc_attr($saved_value); ?>"
            class="regular-text" placeholder="<?php esc_attr_e('Leave empty for default icon', 'vj-chat-order'); ?>">
        <button type="button" class="button vj-chat-upload-icon-btn"><?php _e('Upload Icon', 'vj-chat-order'); ?></button>
        <button type="button"
            class="button vj-chat-reset-icon-btn"><?php _e('Reset to Default', 'vj-chat-order'); ?></button>
    </div>
    <div class="vj-chat-icon-preview" style="margin-top: 10px;">
        <img src="<?php echo esc_url($display_url); ?>" alt="Icon Preview" loading="lazy"
            style="max-width: 40px; max-height: 40px; background: #25D366; padding: 8px; border-radius: 8px;">
    </div>
    <p class="description">
        <?php _e('Upload your own icon or leave empty to use the default local icon. SVG or PNG recommended.', 'vj-chat-order'); ?>
    </p>
    <?php
}

function vj_chat_intro_message_field_callback()
{
    $value = get_option('vj_chat_intro_message', 'Hello, I\'d like to place an order:');
    echo '<textarea name="vj_chat_intro_message" rows="3" class="large-text" placeholder="' . esc_attr__('Hello, I\'d like to place an order:', 'vj-chat-order') . '">' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">' . __('Opening message for the WhatsApp order. Product details will be added automatically.', 'vj-chat-order') . '</p>';
}

/**
 * Design field callbacks
 */
function vj_chat_bg_color_field_callback()
{
    $value = get_option('vj_chat_bg_color', '#25D366');
    echo '<input type="color" name="vj_chat_bg_color" value="' . esc_attr($value) . '" style="width: 60px; height: 40px; padding: 0; border: 1px solid #ccc; cursor: pointer;">';
    echo '<code style="margin-left: 10px;">' . esc_html($value) . '</code>';
    echo '<p class="description">' . __('Button background color. Default: WhatsApp Green (#25D366)', 'vj-chat-order') . '</p>';
}

function vj_chat_text_color_field_callback()
{
    $value = get_option('vj_chat_text_color', '#ffffff');
    echo '<input type="color" name="vj_chat_text_color" value="' . esc_attr($value) . '" style="width: 60px; height: 40px; padding: 0; border: 1px solid #ccc; cursor: pointer;">';
    echo '<code style="margin-left: 10px;">' . esc_html($value) . '</code>';
    echo '<p class="description">' . __('Button text and icon color. Default: White (#ffffff)', 'vj-chat-order') . '</p>';
}

function vj_chat_hover_color_field_callback()
{
    $value = get_option('vj_chat_hover_color', '#1ebe5d');
    echo '<input type="color" name="vj_chat_hover_color" value="' . esc_attr($value) . '" style="width: 60px; height: 40px; padding: 0; border: 1px solid #ccc; cursor: pointer;">';
    echo '<code style="margin-left: 10px;">' . esc_html($value) . '</code>';
    echo '<p class="description">' . __('Button color on hover. Default: Darker Green (#1ebe5d)', 'vj-chat-order') . '</p>';
}

function vj_chat_border_radius_field_callback()
{
    $value = get_option('vj_chat_border_radius', 8);
    echo '<input type="number" name="vj_chat_border_radius" value="' . esc_attr($value) . '" min="0" max="50" style="width: 80px;"> px';
    echo '<p class="description">' . __('Corner roundness. 0 = square, 25+ = pill shape. Default: 8px', 'vj-chat-order') . '</p>';
}

function vj_chat_font_size_field_callback()
{
    $value = get_option('vj_chat_font_size', 16);
    echo '<input type="number" name="vj_chat_font_size" value="' . esc_attr($value) . '" min="12" max="24" style="width: 80px;"> px';
    echo '<p class="description">' . __('Button text size. Default: 16px', 'vj-chat-order') . '</p>';
}

function vj_chat_margin_field_callback()
{
    $top = get_option('vj_chat_margin_top', 15);
    $bottom = get_option('vj_chat_margin_bottom', 15);
    echo '<input type="number" name="vj_chat_margin_top" value="' . esc_attr($top) . '" style="width: 70px;"> px (top) &nbsp;&nbsp;';
    echo '<input type="number" name="vj_chat_margin_bottom" value="' . esc_attr($bottom) . '" style="width: 70px;"> px (bottom)';
    echo '<p class="description">' . __('Space above and below the button. Default: 15px each', 'vj-chat-order') . '</p>';
}

function vj_chat_padding_field_callback()
{
    $vertical = get_option('vj_chat_padding_vertical', 14);
    $horizontal = get_option('vj_chat_padding_horizontal', 24);
    echo '<input type="number" name="vj_chat_padding_vertical" value="' . esc_attr($vertical) . '" min="5" max="30" style="width: 70px;"> px (vertical) &nbsp;&nbsp;';
    echo '<input type="number" name="vj_chat_padding_horizontal" value="' . esc_attr($horizontal) . '" min="10" max="50" style="width: 70px;"> px (horizontal)';
    echo '<p class="description">' . __('Inner spacing of the button. Default: 14px / 24px', 'vj-chat-order') . '</p>';
}



/**
 * Helper function to render field rows
 */
function vj_chat_render_field_row($label, $callback)
{
    ?>
    <tr>
        <th scope="row"><?php echo esc_html($label); ?></th>
        <td><?php call_user_func($callback); ?></td>
    </tr>
    <?php
}

/**
 * Helper to render message customization row (Label + Icon)
 */
function vj_chat_render_message_field_row($title, $label_key, $label_default, $icon_key, $icon_default)
{
    $label_val = get_option($label_key, $label_default);
    $icon_val = get_option($icon_key, $icon_default);
    ?>
    <tr>
        <th scope="row"><?php echo esc_html($title); ?></th>
        <td>
            <div style="display: flex; gap: 15px; align-items: center;">
                <div style="flex: 1;">
                    <label
                        style="display: block; font-size: 11px; color: #666; margin-bottom: 4px;"><?php esc_html_e('Label Text', 'vj-chat-order'); ?></label>
                    <input type="text" name="<?php echo esc_attr($label_key); ?>"
                        value="<?php echo esc_attr($label_val); ?>" class="regular-text" style="width: 100%;">
                </div>
                <div style="width: 80px;">
                    <label
                        style="display: block; font-size: 11px; color: #666; margin-bottom: 4px;"><?php esc_html_e('Icon', 'vj-chat-order'); ?></label>
                    <input type="text" name="<?php echo esc_attr($icon_key); ?>" value="<?php echo esc_attr($icon_val); ?>"
                        class="regular-text" style="width: 100%; text-align: center;">
                </div>
            </div>
        </td>
    </tr>
    <?php
}

/**
 * Render settings page
 */
function vj_chat_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if form was submitted
    if (isset($_POST['submit'])) {
        check_admin_referer('vj_chat_settings_group-options');
    }

    // Get current values for preview
    $bg_color = get_option('vj_chat_bg_color', '#25D366');
    $text_color = get_option('vj_chat_text_color', '#ffffff');
    $border_radius = get_option('vj_chat_border_radius', 8);
    $font_size = get_option('vj_chat_font_size', 16);
    $padding_v = get_option('vj_chat_padding_vertical', 14);
    $padding_h = get_option('vj_chat_padding_horizontal', 24);
    $button_text = get_option('vj_chat_button_text', 'Order via WhatsApp');
    $icon_url = vj_chat_get_icon_url();
    ?>
    <div class="wrap vj-chat-settings-wrap">
        <?php
        // Toast Notifications Logic
        $vj_chat_errors = get_settings_errors();
        if (!empty($vj_chat_errors)) {
            ?>
            <div class="vj-chat-toast-container">
                <?php
                $seen_codes = array();
                foreach ($vj_chat_errors as $error) {
                    if (in_array($error['code'], $seen_codes))
                        continue;
                    $seen_codes[] = $error['code'];

                    $type = $error['type'];
                    $message = $error['message'];
                    $icon = ($type === 'success' || $type === 'updated') ? '✅' : '⚠️';
                    $is_error = ($type === 'error') ? 'error' : '';
                    ?>
                    <div class="vj-chat-toast <?php echo esc_attr($is_error); ?>">
                        <div class="vj-chat-toast-icon"><?php echo $icon; ?></div>
                        <div class="vj-chat-toast-message"><?php echo esc_html($message); ?></div>
                        <button type="button" class="vj-chat-toast-dismiss">&times;</button>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>

        <!-- Main Content Area -->
        <div class="vj-chat-main-content">

            <!-- Page Header -->
            <div class="vj-chat-page-header">
                <h1><?php esc_html_e('VJ Chat Order', 'vj-chat-order'); ?></h1>
                <p><?php esc_html_e('Configure your WhatsApp order button for WooCommerce product pages', 'vj-chat-order'); ?>
                </p>
            </div>

            <form action="options.php" method="post">
                <?php settings_fields('vj_chat_settings_group'); ?>

                <!-- Tabs Navigation -->
                <div class="vj-chat-tabs-nav">
                    <button type="button" class="vj-chat-tab-btn active" data-tab="general">
                        <span class="tab-icon">📱</span> <?php esc_html_e('General', 'vj-chat-order'); ?>
                    </button>
                    <button type="button" class="vj-chat-tab-btn" data-tab="message">
                        <span class="tab-icon">💬</span> <?php esc_html_e('Message', 'vj-chat-order'); ?>
                    </button>
                    <button type="button" class="vj-chat-tab-btn" data-tab="design">
                        <span class="tab-icon">🎨</span> <?php esc_html_e('Design', 'vj-chat-order'); ?>
                    </button>
                </div>

                <!-- Tabs Content -->
                <div class="vj-chat-tabs-content">
                    <!-- General Tab -->
                    <div class="vj-chat-tab-panel active" id="tab-general">
                        <table class="form-table">
                            <?php
                            vj_chat_render_field_row(__('WhatsApp Phone Number', 'vj-chat-order'), 'vj_chat_phone_field_callback');
                            vj_chat_render_field_row(__('Button Text', 'vj-chat-order'), 'vj_chat_button_text_field_callback');
                            vj_chat_render_field_row(__('WhatsApp Icon', 'vj-chat-order'), 'vj_chat_icon_url_field_callback');
                            ?>
                        </table>
                    </div>

                    <!-- Message Tab -->
                    <div class="vj-chat-tab-panel" id="tab-message">
                        <table class="form-table">
                            <?php
                            vj_chat_render_field_row(__('Intro Message', 'vj-chat-order'), 'vj_chat_intro_message_field_callback');
                            ?>
                        </table>

                        <h3 class="vj-chat-section-title"
                            style="margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                            <?php esc_html_e('Message Labels & Icons', 'vj-chat-order'); ?>
                        </h3>

                        <div
                            style="background: #f0f6fc; border: 1px solid #e0e0e0; border-left: 4px solid #25D366; padding: 12px 16px; border-radius: 4px; margin-bottom: 24px;">
                            <p style="margin: 0; font-size: 13px; color: #1d2327;">
                                <strong>💡 <?php esc_html_e('Pro Tip:', 'vj-chat-order'); ?></strong>
                                <?php esc_html_e('Use emojis to make your message stand out!', 'vj-chat-order'); ?>
                            </p>
                            <p style="margin: 8px 0 0; font-size: 12px; color: #646970;">
                                • <strong>Windows:</strong> <?php esc_html_e('Press', 'vj-chat-order'); ?> <code
                                    style="background: #fff; padding: 2px 6px; border-radius: 3px; border: 1px solid #ccc;">Win</code>
                                + <code
                                    style="background: #fff; padding: 2px 6px; border-radius: 3px; border: 1px solid #ccc;">.</code>
                                <?php esc_html_e('to open the emoji picker.', 'vj-chat-order'); ?><br>
                                • <strong>Mac:</strong> <?php esc_html_e('Press', 'vj-chat-order'); ?> <code
                                    style="background: #fff; padding: 2px 6px; border-radius: 3px; border: 1px solid #ccc;">Cmd</code>
                                + <code
                                    style="background: #fff; padding: 2px 6px; border-radius: 3px; border: 1px solid #ccc;">Ctrl</code>
                                + <code
                                    style="background: #fff; padding: 2px 6px; border-radius: 3px; border: 1px solid #ccc;">Space</code><br>
                                • <strong>Web:</strong> <?php printf(
                                    /* translators: %s: Emojipedia URL */
                                    esc_html__('Visit %s to copy and paste emojis.', 'vj-chat-order'),
                                    '<a href="https://emojipedia.org/" target="_blank" style="text-decoration: none; color: #2271b1;">Emojipedia.org <span style="font-size: 10px;">↗</span></a>'
                                ); ?>
                            </p>
                        </div>

                        <p class="description" style="margin-bottom: 20px;">
                            <?php esc_html_e('Customize the text and icons used in the WhatsApp message.', 'vj-chat-order'); ?>
                        </p>

                        <table class="form-table">
                            <?php
                            vj_chat_render_message_field_row(__('Product', 'vj-chat-order'), 'vj_chat_label_product', 'Product', 'vj_chat_icon_product', '🛒');
                            vj_chat_render_message_field_row(__('Quantity', 'vj-chat-order'), 'vj_chat_label_quantity', 'Quantity', 'vj_chat_icon_quantity', '🔢');
                            vj_chat_render_message_field_row(__('Price', 'vj-chat-order'), 'vj_chat_label_price', 'Price', 'vj_chat_icon_price', '💰');
                            vj_chat_render_message_field_row(__('Total', 'vj-chat-order'), 'vj_chat_label_total', 'Total', 'vj_chat_icon_total', '💵');
                            vj_chat_render_message_field_row(__('Link', 'vj-chat-order'), 'vj_chat_label_link', 'Link', 'vj_chat_icon_link', '🔗');
                            ?>
                        </table>
                    </div>

                    <!-- Design Tab -->
                    <div class="vj-chat-tab-panel" id="tab-design">
                        <table class="form-table">
                            <?php
                            vj_chat_render_field_row(__('Background Color', 'vj-chat-order'), 'vj_chat_bg_color_field_callback');
                            vj_chat_render_field_row(__('Text Color', 'vj-chat-order'), 'vj_chat_text_color_field_callback');
                            vj_chat_render_field_row(__('Hover Color', 'vj-chat-order'), 'vj_chat_hover_color_field_callback');
                            vj_chat_render_field_row(__('Border Radius', 'vj-chat-order'), 'vj_chat_border_radius_field_callback');
                            vj_chat_render_field_row(__('Font Size', 'vj-chat-order'), 'vj_chat_font_size_field_callback');
                            vj_chat_render_field_row(__('Margin', 'vj-chat-order'), 'vj_chat_margin_field_callback');
                            vj_chat_render_field_row(__('Padding', 'vj-chat-order'), 'vj_chat_padding_field_callback');
                            ?>
                        </table>
                    </div>
                </div>

                <div style="margin-top: 24px;">
                    <?php submit_button(__('Save Settings', 'vj-chat-order'), 'primary', 'submit', false); ?>
                </div>
            </form>
        </div>

        <!-- Sidebar Preview -->
        <div class="vj-chat-sidebar">
            <div class="vj-chat-card">
                <div class="vj-chat-card-header">
                    <div class="vj-chat-card-icon preview">
                        <span style="color: #fff; font-size: 18px;">👁️</span>
                    </div>
                    <div>
                        <h2 class="vj-chat-card-title"><?php esc_html_e('Live Preview', 'vj-chat-order'); ?></h2>
                        <p class="vj-chat-card-description"><?php esc_html_e('Save to update', 'vj-chat-order'); ?></p>
                    </div>
                </div>

                <div class="vj-chat-preview-sections">
                    <!-- Full Button Preview -->
                    <div class="vj-chat-preview-section">
                        <div class="vj-chat-preview-label"><?php esc_html_e('Full Button', 'vj-chat-order'); ?></div>
                        <div class="vj-chat-preview-box">
                            <a href="#" onclick="return false;" class="vj-chat-preview-full-btn" style="
                                background-color: <?php echo esc_attr($bg_color); ?>;
                                color: <?php echo esc_attr($text_color); ?>;
                                padding: <?php echo esc_attr($padding_v); ?>px <?php echo esc_attr($padding_h); ?>px;
                                border-radius: <?php echo esc_attr($border_radius); ?>px;
                                font-size: <?php echo esc_attr($font_size); ?>px;
                            ">
                                <img src="<?php echo esc_url($icon_url); ?>" alt="WhatsApp"
                                    style="width: 20px; height: 20px; filter: brightness(0) invert(1);">
                                <?php echo esc_html($button_text); ?>
                            </a>
                        </div>
                    </div>

                    <!-- Compact Icon Preview -->
                    <div class="vj-chat-preview-section">
                        <div class="vj-chat-preview-label"><?php esc_html_e('Sticky Bar (Compact)', 'vj-chat-order'); ?>
                        </div>
                        <div class="vj-chat-preview-box sticky-preview">
                            <a href="#" onclick="return false;" class="vj-chat-preview-icon-btn" style="
                                background-color: <?php echo esc_attr($bg_color); ?>;
                            ">
                                <img src="<?php echo esc_url($icon_url); ?>" alt="WhatsApp">
                            </a>
                        </div>
                    </div>
                </div>

                <p class="description" style="margin-top: 16px; text-align: center; font-size: 11px;">
                    <?php _e('Full button shows on product page', 'vj-chat-order'); ?><br>
                    <?php _e('Compact icon shows in sticky bar', 'vj-chat-order'); ?>
                </p>
            </div>
        </div>
    </div>
    <?php
}
