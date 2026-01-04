<?php
/**
 * Plugin Name: VJ Chat Order for WooCommerce
 * Plugin URI: https://github.com/VJ-Ranga/VJ-Chat-Order-for-WooCommerce
 * Description: Adds a customizable "Order via Chat App" button to WooCommerce product pages, allowing customers to send order details directly to your WhatsApp number.
 * Version: 1.4.0
 * Author: VJ Ranga
 * Author URI: https://vjranga.com/
 * Text Domain: vj-chat-order
 * Domain Path: /languages
 * Requires at least: 5.8
 * Tested up to: 6.7
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 5.0
 * WC tested up to: 9.5
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('VJ_CHAT_VERSION', '1.4.0');
define('VJ_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VJ_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Get user's last active tab
 */
function vj_chat_get_user_active_tab()
{
    $user_id = get_current_user_id();

    // Not logged in? Use default
    if (!$user_id) {
        return 'general';
    }

    // Get saved preference
    $active_tab = get_user_meta($user_id, 'vj_chat_active_tab', true);

    // Validate it's a real tab
    $valid_tabs = array('general', 'message', 'design');
    if (!empty($active_tab) && in_array($active_tab, $valid_tabs)) {
        return $active_tab;
    }

    return 'general';
}

/**
 * Save user's active tab via AJAX
 */
function vj_chat_save_active_tab()
{
    // Security check
    check_ajax_referer('vj_chat_tab_nonce', 'nonce');

    // Permission check
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
    }

    // Get and validate tab
    $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'general';

    // Only allow valid tabs
    $valid_tabs = array('general', 'message', 'design');
    if (!in_array($tab, $valid_tabs)) {
        wp_send_json_error('Invalid tab');
    }

    // Save to user meta
    $user_id = get_current_user_id();
    update_user_meta($user_id, 'vj_chat_active_tab', $tab);

    wp_send_json_success(array('message' => 'Tab preference saved'));
}
add_action('wp_ajax_vj_chat_save_active_tab', 'vj_chat_save_active_tab');

/**
 * Get default icon URL (local asset)
 */
function vj_chat_get_default_icon_url()
{
    return VJ_CHAT_PLUGIN_URL . 'assets/images/whatsapp-icon.svg';
}

/**
 * Include admin settings
 */
require_once VJ_CHAT_PLUGIN_DIR . 'inc/admin-settings.php';

/**
 * Load plugin textdomain
 */
function vj_chat_load_textdomain()
{
    load_plugin_textdomain('vj-chat-order', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'vj_chat_load_textdomain');

/**
 * Check if WooCommerce is active
 */
function vj_chat_check_woocommerce()
{
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo __('VJ Chat Order requires WooCommerce to be installed and activated.', 'vj-chat-order');
            echo '</p></div>';
        });
    }
}
add_action('admin_init', 'vj_chat_check_woocommerce');

/**
 * Declare HPOS Compatibility
 */
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/**
 * Activation hook - set default options
 */
function vj_chat_activate()
{
    $defaults = array(
        // General settings
        'vj_chat_phone_number' => '947000000000',
        'vj_chat_button_text' => __('Order via WhatsApp', 'vj-chat-order'),
        'vj_chat_icon_url' => '', // Empty means use default local icon
        'vj_chat_intro_message' => __('Hello, I\'d like to place an order:', 'vj-chat-order'),
        // Design settings
        'vj_chat_bg_color' => '#25D366',
        'vj_chat_text_color' => '#ffffff',
        'vj_chat_hover_color' => '#1ebe5d',
        'vj_chat_border_radius' => 8,
        'vj_chat_font_size' => 16,
        'vj_chat_margin_top' => 15,
        'vj_chat_margin_bottom' => 15,
        'vj_chat_padding_vertical' => 14,
        'vj_chat_padding_horizontal' => 24,
    );

    foreach ($defaults as $option => $value) {
        if (get_option($option) === false) {
            add_option($option, $value);
        }
    }
}
register_activation_hook(__FILE__, 'vj_chat_activate');

/**
 * Add Settings link to plugins page
 */
function vj_chat_add_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=vj-chat-settings">' . __('Settings', 'vj-chat-order') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'vj_chat_add_settings_link');

/**
 * Enqueue scripts and styles on product pages
 */
function vj_chat_enqueue_assets()
{
    if (!function_exists('is_product') || !is_product()) {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style(
        'vj-chat-style',
        VJ_CHAT_PLUGIN_URL . 'assets/css/style.css',
        array(),
        VJ_CHAT_VERSION
    );

    // Enqueue JS
    wp_enqueue_script(
        'vj-chat-script',
        VJ_CHAT_PLUGIN_URL . 'assets/js/vj-chat-script.js', // Updated filename
        array('jquery'),
        VJ_CHAT_VERSION,
        true
    );

    // Get current product data safely
    global $post;
    $product = wc_get_product($post->ID);

    $product_name = '';
    $product_url = '';
    $currency_symbol = html_entity_decode(get_woocommerce_currency_symbol());

    if ($product && is_a($product, 'WC_Product')) {
        $product_name = html_entity_decode(strip_tags($product->get_name()));
        $product_url = esc_url(get_permalink($product->get_id()));
    }

    // Localize script with settings and product data
    wp_localize_script('vj-chat-script', 'vjChatData', array( // Renamed wobData to vjChatData
        'phoneNumber' => get_option('vj_chat_phone_number', '947000000000'),
        'introMessage' => get_option('vj_chat_intro_message', __('Hello, I\'d like to place an order:', 'vj-chat-order')),
        'productName' => $product_name,
        'productUrl' => esc_url_raw($product_url),
        'currencySymbol' => $currency_symbol,
        'priceDecimalSeparator' => wc_get_price_decimal_separator(),
        'priceThousandSeparator' => wc_get_price_thousand_separator(),
        'labels' => array(
            'product' => get_option('vj_chat_label_product', _x('Product', 'WhatsApp message label', 'vj-chat-order')),
            'quantity' => get_option('vj_chat_label_quantity', _x('Quantity', 'WhatsApp message label', 'vj-chat-order')),
            'price' => get_option('vj_chat_label_price', _x('Price', 'WhatsApp message label', 'vj-chat-order')),
            'total' => get_option('vj_chat_label_total', _x('Total', 'WhatsApp message label', 'vj-chat-order')),
            'link' => get_option('vj_chat_label_link', _x('Link', 'WhatsApp message label', 'vj-chat-order')),
        ),
        'icons' => array(
            'product' => get_option('vj_chat_icon_product', '🛒'),
            'quantity' => get_option('vj_chat_icon_quantity', '🔢'),
            'price' => get_option('vj_chat_icon_price', '💰'),
            'total' => get_option('vj_chat_icon_total', '💵'),
            'link' => get_option('vj_chat_icon_link', '🔗'),
        )
    ));

    // Add dynamic inline styles
    vj_chat_add_dynamic_styles();
}

/**
 * Add dynamic inline styles from settings
 */
function vj_chat_add_dynamic_styles()
{
    $bg_color = get_option('vj_chat_bg_color', '#25D366');
    $text_color = get_option('vj_chat_text_color', '#ffffff');
    $hover_color = get_option('vj_chat_hover_color', '#1ebe5d');
    $border_radius = absint(get_option('vj_chat_border_radius', 8));
    $font_size = absint(get_option('vj_chat_font_size', 16));
    $margin_top = intval(get_option('vj_chat_margin_top', 15));
    $margin_bottom = intval(get_option('vj_chat_margin_bottom', 15));
    $padding_v = absint(get_option('vj_chat_padding_vertical', 14));
    $padding_h = absint(get_option('vj_chat_padding_horizontal', 24));

    $custom_css = "
        .vj-chat-button {
            background-color: " . esc_attr($bg_color) . " !important;
            color: " . esc_attr($text_color) . " !important;
            border-radius: " . esc_attr($border_radius) . "px !important;
            font-size: " . esc_attr($font_size) . "px !important;
            margin-top: " . esc_attr($margin_top) . "px !important;
            margin-bottom: " . esc_attr($margin_bottom) . "px !important;
            padding: " . esc_attr($padding_v) . "px " . esc_attr($padding_h) . "px !important;
        }
        .vj-chat-button:hover {
            background-color: " . esc_attr($hover_color) . " !important;
            color: " . esc_attr($text_color) . " !important;
        }
    ";

    wp_add_inline_style('vj-chat-style', $custom_css);
}

/**
 * Get the icon URL (custom or default)
 */
function vj_chat_get_icon_url()
{
    $custom_icon = get_option('vj_chat_icon_url', '');
    return !empty($custom_icon) ? $custom_icon : vj_chat_get_default_icon_url();
}

/**
 * Render WhatsApp button on product page
 */
function vj_chat_render_button()
{
    if (!function_exists('is_product') || !is_product()) {
        return;
    }

    $button_text = esc_html(get_option('vj_chat_button_text', __('Order via WhatsApp', 'vj-chat-order')));
    $icon_url = esc_url(vj_chat_get_icon_url());

    echo '<a href="#" id="vj-chat-order-btn" class="vj-chat-button">';
    echo '<img src="' . $icon_url . '" alt="WhatsApp" class="vj-chat-icon"> ';
    echo $button_text;
    echo '</a>';
}

/**
 * Initialize plugin hooks if WooCommerce is active
 */
function vj_chat_init()
{
    if (!class_exists('WooCommerce')) {
        return;
    }

    // Frontend hooks
    add_action('wp_enqueue_scripts', 'vj_chat_enqueue_assets');
    add_action('woocommerce_after_add_to_cart_form', 'vj_chat_render_button', 10);
}
add_action('plugins_loaded', 'vj_chat_init');
