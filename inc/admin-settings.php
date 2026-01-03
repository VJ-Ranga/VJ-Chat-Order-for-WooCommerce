<?php
/**
 * Admin Settings for WhatsApp Order Button
 * 
 * @package WhatsApp_Order_Button
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add settings menu under Settings
 */
function wob_add_settings_menu()
{
    add_options_page(
        'WhatsApp Order Settings',  // Page title
        'WhatsApp Order',           // Menu title
        'manage_options',           // Capability
        'wob-settings',             // Menu slug
        'wob_render_settings_page'  // Callback function
    );
}
add_action('admin_menu', 'wob_add_settings_menu');

/**
 * Enqueue media uploader and admin styles on settings page
 */
function wob_admin_enqueue_scripts($hook)
{
    if ($hook !== 'settings_page_wob-settings') {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_style(
        'wob-admin-style',
        WOB_PLUGIN_URL . 'assets/css/admin-style.css',
        array(),
        WOB_VERSION
    );
}
add_action('admin_enqueue_scripts', 'wob_admin_enqueue_scripts');

/**
 * Register settings
 */
function wob_register_settings()
{
    // Register Settings Page
    add_options_page(
        __('WhatsApp Order Settings', 'whatsapp-order-button'),
        __('WhatsApp Order', 'whatsapp-order-button'),
        'manage_options',
        'wob-settings',
        'wob_render_settings_page'
    );
}
add_action('admin_menu', 'wob_register_settings');

/**
 * Register Settings and Fields
 */
function wob_register_settings_init()
{
    // Register Settings
    register_setting('wob_settings_group', 'wob_phone_number', array(
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '947000000000'
    ));

    register_setting('wob_settings_group', 'wob_button_text', array(
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'Order via WhatsApp'
    ));

    register_setting('wob_settings_group', 'wob_icon_url', array(
        'sanitize_callback' => 'esc_url_raw',
        'default' => 'https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg'
    ));

    register_setting('wob_settings_group', 'wob_intro_message', array(
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => 'Hello, I\'d like to place an order:'
    ));

    // Design Settings
    register_setting('wob_settings_group', 'wob_bg_color', array(
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '#25D366'
    ));

    register_setting('wob_settings_group', 'wob_text_color', array(
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '#ffffff'
    ));

    register_setting('wob_settings_group', 'wob_hover_color', array(
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '#1ebe5d'
    ));

    register_setting('wob_settings_group', 'wob_border_radius', array(
        'sanitize_callback' => 'absint',
        'default' => 8
    ));

    register_setting('wob_settings_group', 'wob_font_size', array(
        'sanitize_callback' => 'absint',
        'default' => 16
    ));

    register_setting('wob_settings_group', 'wob_margin_top', array(
        'sanitize_callback' => 'absint',
        'default' => 15
    ));

    register_setting('wob_settings_group', 'wob_margin_bottom', array(
        'sanitize_callback' => 'absint',
        'default' => 15
    ));

    register_setting('wob_settings_group', 'wob_padding_vertical', array(
        'sanitize_callback' => 'absint',
        'default' => 14
    ));

    register_setting('wob_settings_group', 'wob_padding_horizontal', array(
        'sanitize_callback' => 'absint',
        'default' => 24
    ));

    // ===== Message Customization Settings =====
    $message_settings = array(
        'wob_label_product' => __('Product', 'whatsapp-order-button'),
        'wob_icon_product' => '🛒',
        'wob_label_quantity' => __('Quantity', 'whatsapp-order-button'),
        'wob_icon_quantity' => '🔢',
        'wob_label_price' => __('Price', 'whatsapp-order-button'),
        'wob_icon_price' => '💰',
        'wob_label_total' => __('Total', 'whatsapp-order-button'),
        'wob_icon_total' => '💵',
        'wob_label_link' => __('Link', 'whatsapp-order-button'),
        'wob_icon_link' => '🔗'
    );

    foreach ($message_settings as $key => $default) {
        register_setting('wob_settings_group', $key, array(
            'sanitize_callback' => 'sanitize_text_field',
            'default' => $default
        ));
    }

    // ===== General Section =====
    add_settings_section(
        'wob_main_section',
        __('General Settings', 'whatsapp-order-button'),
        'wob_section_callback',
        'wob-settings'
    );

    // ===== Design Section =====
    add_settings_section(
        'wob_design_section',
        __('Button Design', 'whatsapp-order-button'),
        'wob_design_section_callback',
        'wob-settings'
    );

    // ===== General Fields =====
    add_settings_field(
        'wob_phone_number',
        __('WhatsApp Phone Number', 'whatsapp-order-button'),
        'wob_phone_field_callback',
        'wob-settings',
        'wob_main_section'
    );

    add_settings_field(
        'wob_button_text',
        __('Button Text', 'whatsapp-order-button'),
        'wob_button_text_field_callback',
        'wob-settings',
        'wob_main_section'
    );

    add_settings_field(
        'wob_icon_url',
        __('WhatsApp Icon URL', 'whatsapp-order-button'),
        'wob_icon_url_field_callback',
        'wob-settings',
        'wob_main_section'
    );

    add_settings_field(
        'wob_intro_message',
        __('Custom Intro Message', 'whatsapp-order-button'),
        'wob_intro_message_field_callback',
        'wob-settings',
        'wob_main_section'
    );

    // ===== Design Fields =====
    add_settings_field(
        'wob_bg_color',
        __('Background Color', 'whatsapp-order-button'),
        'wob_bg_color_field_callback',
        'wob-settings',
        'wob_design_section'
    );

    add_settings_field(
        'wob_text_color',
        __('Text Color', 'whatsapp-order-button'),
        'wob_text_color_field_callback',
        'wob-settings',
        'wob_design_section'
    );

    add_settings_field(
        'wob_hover_color',
        __('Hover Color', 'whatsapp-order-button'),
        'wob_hover_color_field_callback',
        'wob-settings',
        'wob_design_section'
    );

    add_settings_field(
        'wob_border_radius',
        __('Border Radius', 'whatsapp-order-button'),
        'wob_border_radius_field_callback',
        'wob-settings',
        'wob_design_section'
    );

    add_settings_field(
        'wob_font_size',
        __('Font Size', 'whatsapp-order-button'),
        'wob_font_size_field_callback',
        'wob-settings',
        'wob_design_section'
    );

    add_settings_field(
        'wob_margin',
        __('Margin (Top / Bottom)', 'whatsapp-order-button'),
        'wob_margin_field_callback',
        'wob-settings',
        'wob_design_section'
    );

    add_settings_field(
        'wob_padding',
        __('Padding (Vertical / Horizontal)', 'whatsapp-order-button'),
        'wob_padding_field_callback',
        'wob-settings',
        'wob_design_section'
    );
}
add_action('admin_init', 'wob_register_settings');

/**
 * Section callbacks
 */
function wob_section_callback()
{
    echo '<p>' . __('Configure the WhatsApp order button that appears on your WooCommerce product pages.', 'whatsapp-order-button') . '</p>';
}

function wob_design_section_callback()
{
    echo '<p>' . __('Customize the appearance of your WhatsApp button.', 'whatsapp-order-button') . '</p>';
}

/**
 * General field callbacks
 */
function wob_phone_field_callback()
{
    $value = get_option('wob_phone_number', '947000000000');
    echo '<input type="text" name="wob_phone_number" value="' . esc_attr($value) . '" class="regular-text" placeholder="947000000000">';
    echo '<p class="description">' . __('Enter the WhatsApp number with country code (no + or spaces). Example: 947000000000', 'whatsapp-order-button') . '</p>';
}

function wob_button_text_field_callback()
{
    $value = get_option('wob_button_text', 'Order via WhatsApp');
    echo '<input type="text" name="wob_button_text" value="' . esc_attr($value) . '" class="regular-text" placeholder="' . esc_attr__('Order via WhatsApp', 'whatsapp-order-button') . '">';
    echo '<p class="description">' . __('Text displayed on the WhatsApp button.', 'whatsapp-order-button') . '</p>';
}

function wob_icon_url_field_callback()
{
    $default_icon = wob_get_default_icon_url();
    $saved_value = get_option('wob_icon_url', '');
    $display_url = !empty($saved_value) ? $saved_value : $default_icon;
    ?>
    <div class="wob-icon-upload-wrap">
        <input type="url" id="wob_icon_url" name="wob_icon_url" value="<?php echo esc_attr($saved_value); ?>"
            class="regular-text"
            placeholder="<?php esc_attr_e('Leave empty for default icon', 'whatsapp-order-button'); ?>">
        <button type="button"
            class="button wob-upload-icon-btn"><?php _e('Upload Icon', 'whatsapp-order-button'); ?></button>
        <button type="button"
            class="button wob-reset-icon-btn"><?php _e('Reset to Default', 'whatsapp-order-button'); ?></button>
    </div>
    <div class="wob-icon-preview" style="margin-top: 10px;">
        <img src="<?php echo esc_url($display_url); ?>" alt="Icon Preview"
            style="max-width: 40px; max-height: 40px; background: #25D366; padding: 8px; border-radius: 8px;">
    </div>
    <p class="description">
        <?php _e('Upload your own icon or leave empty to use the default local icon. SVG or PNG recommended.', 'whatsapp-order-button'); ?>
    </p>

    <script>
        jQuery(document).ready(function ($) {
            var defaultIcon = '<?php echo esc_js($default_icon); ?>';

            // Media uploader
            $('.wob-upload-icon-btn').on('click', function (e) {
                e.preventDefault();

                var mediaUploader = wp.media({
                    title: '<?php echo esc_js(__('Select WhatsApp Icon', 'whatsapp-order-button')); ?>',
                    button: { text: '<?php echo esc_js(__('Use This Icon', 'whatsapp-order-button')); ?>' },
                    multiple: false,
                    library: { type: ['image'] }
                });
                // ... (rest of script remains same, assume standard) ...
            });

            // ... (rest of script) ...
        });
    </script>
    <?php
}

function wob_intro_message_field_callback()
{
    $value = get_option('wob_intro_message', 'Hello, I\'d like to place an order:');
    echo '<textarea name="wob_intro_message" rows="3" class="large-text" placeholder="' . esc_attr__('Hello, I\'d like to place an order:', 'whatsapp-order-button') . '">' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">' . __('Opening message for the WhatsApp order. Product details will be added automatically.', 'whatsapp-order-button') . '</p>';
}

/**
 * Design field callbacks
 */
function wob_bg_color_field_callback()
{
    $value = get_option('wob_bg_color', '#25D366');
    echo '<input type="color" name="wob_bg_color" value="' . esc_attr($value) . '" style="width: 60px; height: 40px; padding: 0; border: 1px solid #ccc; cursor: pointer;">';
    echo '<code style="margin-left: 10px;">' . esc_html($value) . '</code>';
    echo '<p class="description">' . __('Button background color. Default: WhatsApp Green (#25D366)', 'whatsapp-order-button') . '</p>';
}

function wob_text_color_field_callback()
{
    $value = get_option('wob_text_color', '#ffffff');
    echo '<input type="color" name="wob_text_color" value="' . esc_attr($value) . '" style="width: 60px; height: 40px; padding: 0; border: 1px solid #ccc; cursor: pointer;">';
    echo '<code style="margin-left: 10px;">' . esc_html($value) . '</code>';
    echo '<p class="description">' . __('Button text and icon color. Default: White (#ffffff)', 'whatsapp-order-button') . '</p>';
}

function wob_hover_color_field_callback()
{
    $value = get_option('wob_hover_color', '#1ebe5d');
    echo '<input type="color" name="wob_hover_color" value="' . esc_attr($value) . '" style="width: 60px; height: 40px; padding: 0; border: 1px solid #ccc; cursor: pointer;">';
    echo '<code style="margin-left: 10px;">' . esc_html($value) . '</code>';
    echo '<p class="description">' . __('Button color on hover. Default: Darker Green (#1ebe5d)', 'whatsapp-order-button') . '</p>';
}

function wob_border_radius_field_callback()
{
    $value = get_option('wob_border_radius', 8);
    echo '<input type="number" name="wob_border_radius" value="' . esc_attr($value) . '" min="0" max="50" style="width: 80px;"> px';
    echo '<p class="description">' . __('Corner roundness. 0 = square, 25+ = pill shape. Default: 8px', 'whatsapp-order-button') . '</p>';
}

function wob_font_size_field_callback()
{
    $value = get_option('wob_font_size', 16);
    echo '<input type="number" name="wob_font_size" value="' . esc_attr($value) . '" min="12" max="24" style="width: 80px;"> px';
    echo '<p class="description">' . __('Button text size. Default: 16px', 'whatsapp-order-button') . '</p>';
}

function wob_margin_field_callback()
{
    $top = get_option('wob_margin_top', 15);
    $bottom = get_option('wob_margin_bottom', 15);
    echo '<input type="number" name="wob_margin_top" value="' . esc_attr($top) . '" min="0" max="100" style="width: 70px;"> px (top) &nbsp;&nbsp;';
    echo '<input type="number" name="wob_margin_bottom" value="' . esc_attr($bottom) . '" min="0" max="100" style="width: 70px;"> px (bottom)';
    echo '<p class="description">' . __('Space above and below the button. Default: 15px each', 'whatsapp-order-button') . '</p>';
}

function wob_padding_field_callback()
{
    $vertical = get_option('wob_padding_vertical', 14);
    $horizontal = get_option('wob_padding_horizontal', 24);
    echo '<input type="number" name="wob_padding_vertical" value="' . esc_attr($vertical) . '" min="5" max="30" style="width: 70px;"> px (vertical) &nbsp;&nbsp;';
    echo '<input type="number" name="wob_padding_horizontal" value="' . esc_attr($horizontal) . '" min="10" max="50" style="width: 70px;"> px (horizontal)';
    echo '<p class="description">' . __('Inner spacing of the button. Default: 14px / 24px', 'whatsapp-order-button') . '</p>';
}



/**
 * Helper function to render field rows
 */
function wob_render_field_row($label, $callback)
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
function wob_render_message_field_row($title, $label_key, $label_default, $icon_key, $icon_default)
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
                        style="display: block; font-size: 11px; color: #666; margin-bottom: 4px;"><?php esc_html_e('Label Text', 'whatsapp-order-button'); ?></label>
                    <input type="text" name="<?php echo esc_attr($label_key); ?>"
                        value="<?php echo esc_attr($label_val); ?>" class="regular-text" style="width: 100%;">
                </div>
                <div style="width: 80px;">
                    <label
                        style="display: block; font-size: 11px; color: #666; margin-bottom: 4px;"><?php esc_html_e('Icon', 'whatsapp-order-button'); ?></label>
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
function wob_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get current values for preview
    $bg_color = get_option('wob_bg_color', '#25D366');
    $text_color = get_option('wob_text_color', '#ffffff');
    $border_radius = get_option('wob_border_radius', 8);
    $font_size = get_option('wob_font_size', 16);
    $padding_v = get_option('wob_padding_vertical', 14);
    $padding_h = get_option('wob_padding_horizontal', 24);
    $button_text = get_option('wob_button_text', 'Order via WhatsApp');
    $icon_url = wob_get_icon_url();
    ?>
    <div class="wrap wob-settings-wrap">
        <?php
        // Toast Notifications Logic
        $wob_errors = get_settings_errors();
        if (!empty($wob_errors)) {
            ?>
            <div class="wob-toast-container">
                <?php
                $seen_codes = array();
                foreach ($wob_errors as $error) {
                    if (in_array($error['code'], $seen_codes))
                        continue;
                    $seen_codes[] = $error['code'];

                    $type = $error['type'];
                    $message = $error['message'];
                    $icon = ($type === 'success' || $type === 'updated') ? '✅' : '⚠️';
                    $is_error = ($type === 'error') ? 'error' : '';
                    ?>
                    <div class="wob-toast <?php echo esc_attr($is_error); ?>">
                        <div class="wob-toast-icon"><?php echo $icon; ?></div>
                        <div class="wob-toast-message"><?php echo esc_html($message); ?></div>
                        <button type="button" class="wob-toast-dismiss">&times;</button>
                    </div>
                    <?php
                }
                ?>
            </div>
            <script>
                jQuery(document).ready(function ($) {
                    // Show Toasts with Animation
                    setTimeout(function () {
                        $('.wob-toast').addClass('show');
                    }, 100);

                    // Auto Dismiss after 5 seconds
                    setTimeout(function () {
                        $('.wob-toast').removeClass('show');
                        setTimeout(function () {
                            $('.wob-toast-container').remove();
                        }, 300);
                    }, 5000);

                    // Manual Dismiss
                    $('.wob-toast-dismiss').on('click', function () {
                        $(this).closest('.wob-toast').removeClass('show');
                    });
                });
            </script>
            <?php
        }
        ?>

        <!-- Main Content Area -->
        <div class="wob-main-content">

            <!-- Page Header -->
            <div class="wob-page-header">
                <h1><?php esc_html_e('WhatsApp Order Button', 'whatsapp-order-button'); ?></h1>
                <p><?php esc_html_e('Configure your WhatsApp order button for WooCommerce product pages', 'whatsapp-order-button'); ?>
                </p>
            </div>

            <form action="options.php" method="post">
                <?php settings_fields('wob_settings_group'); ?>

                <!-- Tabs Navigation -->
                <div class="wob-tabs-nav">
                    <button type="button" class="wob-tab-btn active" data-tab="general">
                        <span class="tab-icon">📱</span> <?php esc_html_e('General', 'whatsapp-order-button'); ?>
                    </button>
                    <button type="button" class="wob-tab-btn" data-tab="message">
                        <span class="tab-icon">💬</span> <?php esc_html_e('Message', 'whatsapp-order-button'); ?>
                    </button>
                    <button type="button" class="wob-tab-btn" data-tab="design">
                        <span class="tab-icon">🎨</span> <?php esc_html_e('Design', 'whatsapp-order-button'); ?>
                    </button>
                </div>

                <!-- Tabs Content -->
                <div class="wob-tabs-content">
                    <!-- General Tab -->
                    <div class="wob-tab-panel active" id="tab-general">
                        <table class="form-table">
                            <?php
                            wob_render_field_row(__('WhatsApp Phone Number', 'whatsapp-order-button'), 'wob_phone_field_callback');
                            wob_render_field_row(__('Button Text', 'whatsapp-order-button'), 'wob_button_text_field_callback');
                            wob_render_field_row(__('WhatsApp Icon', 'whatsapp-order-button'), 'wob_icon_url_field_callback');
                            ?>
                        </table>
                    </div>

                    <!-- Message Tab -->
                    <div class="wob-tab-panel" id="tab-message">
                        <table class="form-table">
                            <?php
                            wob_render_field_row(__('Intro Message', 'whatsapp-order-button'), 'wob_intro_message_field_callback');
                            ?>
                        </table>

                        <h3 class="wob-section-title"
                            style="margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                            <?php esc_html_e('Message Labels & Icons', 'whatsapp-order-button'); ?></h3>
                        <p class="description" style="margin-bottom: 20px;">
                            <?php esc_html_e('Customize the text and icons used in the WhatsApp message.', 'whatsapp-order-button'); ?>
                        </p>

                        <table class="form-table">
                            <?php
                            wob_render_message_field_row(__('Product', 'whatsapp-order-button'), 'wob_label_product', 'Product', 'wob_icon_product', '🛒');
                            wob_render_message_field_row(__('Quantity', 'whatsapp-order-button'), 'wob_label_quantity', 'Quantity', 'wob_icon_quantity', '🔢');
                            wob_render_message_field_row(__('Price', 'whatsapp-order-button'), 'wob_label_price', 'Price', 'wob_icon_price', '💰');
                            wob_render_message_field_row(__('Total', 'whatsapp-order-button'), 'wob_label_total', 'Total', 'wob_icon_total', '💵');
                            wob_render_message_field_row(__('Link', 'whatsapp-order-button'), 'wob_label_link', 'Link', 'wob_icon_link', '🔗');
                            ?>
                        </table>
                    </div>

                    <!-- Design Tab -->
                    <div class="wob-tab-panel" id="tab-design">
                        <table class="form-table">
                            <?php
                            wob_render_field_row(__('Background Color', 'whatsapp-order-button'), 'wob_bg_color_field_callback');
                            wob_render_field_row(__('Text Color', 'whatsapp-order-button'), 'wob_text_color_field_callback');
                            wob_render_field_row(__('Hover Color', 'whatsapp-order-button'), 'wob_hover_color_field_callback');
                            wob_render_field_row(__('Border Radius', 'whatsapp-order-button'), 'wob_border_radius_field_callback');
                            wob_render_field_row(__('Font Size', 'whatsapp-order-button'), 'wob_font_size_field_callback');
                            wob_render_field_row(__('Margin', 'whatsapp-order-button'), 'wob_margin_field_callback');
                            wob_render_field_row(__('Padding', 'whatsapp-order-button'), 'wob_padding_field_callback');
                            ?>
                        </table>
                    </div>
                </div>

                <div style="margin-top: 24px;">
                    <?php submit_button(__('Save Settings', 'whatsapp-order-button'), 'primary', 'submit', false); ?>
                </div>
            </form>
        </div>

        <!-- Sidebar Preview -->
        <div class="wob-sidebar">
            <div class="wob-card">
                <div class="wob-card-header">
                    <div class="wob-card-icon preview">
                        <span style="color: #fff; font-size: 18px;">👁️</span>
                    </div>
                    <div>
                        <h2 class="wob-card-title"><?php esc_html_e('Live Preview', 'whatsapp-order-button'); ?></h2>
                        <p class="wob-card-description"><?php esc_html_e('Save to update', 'whatsapp-order-button'); ?></p>
                    </div>
                </div>

                <div class="wob-preview-sections">
                    <!-- Full Button Preview -->
                    <div class="wob-preview-section">
                        <div class="wob-preview-label"><?php esc_html_e('Full Button', 'whatsapp-order-button'); ?></div>
                        <div class="wob-preview-box">
                            <a href="#" onclick="return false;" class="wob-preview-full-btn" style="
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
                    <div class="wob-preview-section">
                        <div class="wob-preview-label"><?php esc_html_e('Sticky Bar (Compact)', 'whatsapp-order-button'); ?>
                        </div>
                        <div class="wob-preview-box sticky-preview">
                            <a href="#" onclick="return false;" class="wob-preview-icon-btn" style="
                                background-color: <?php echo esc_attr($bg_color); ?>;
                            ">
                                <img src="<?php echo esc_url($icon_url); ?>" alt="WhatsApp">
                            </a>
                        </div>
                    </div>
                </div>

                <p class="description" style="margin-top: 16px; text-align: center; font-size: 11px;">
                    <?php _e('Full button shows on product page', 'whatsapp-order-button'); ?><br>
                    <?php _e('Compact icon shows in sticky bar', 'whatsapp-order-button'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Tab Switching Script -->
    <script>
        jQuery(document).ready(function ($) {
            $('.wob-tab-btn').on('click', function () {
                var tabId = $(this).data('tab');

                // Update button states
                $('.wob-tab-btn').removeClass('active');
                $(this).addClass('active');

                // Update panel visibility
                $('.wob-tab-panel').removeClass('active');
                $('#tab-' + tabId).addClass('active');
            });
        });
    </script>
    <?php
}
