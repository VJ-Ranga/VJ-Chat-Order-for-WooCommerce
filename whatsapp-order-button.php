<?php
/**
 * Plugin Name: WhatsApp Order Button
 * Plugin URI: https://example.com/whatsapp-order-button
 * Description: Adds a WhatsApp order button to WooCommerce product pages with customizable settings.
 * Version: 1.0.0
 * Author: VJ Ranga
 * Author URI: https://example.com
 * Text Domain: whatsapp-order-button
 * Requires Plugins: woocommerce
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WOB_VERSION', '1.0.0');
define('WOB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOB_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Get default icon URL (local asset)
 */
function wob_get_default_icon_url()
{
    return WOB_PLUGIN_URL . 'assets/images/whatsapp-icon.svg';
}

/**
 * Include admin settings
 */
require_once WOB_PLUGIN_DIR . 'inc/admin-settings.php';

/**
 * Load plugin textdomain
 */
function wob_load_textdomain()
{
    load_plugin_textdomain('whatsapp-order-button', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'wob_load_textdomain');

/**
 * Check if WooCommerce is active
 */
function wob_check_woocommerce()
{
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo __('WhatsApp Order Button requires WooCommerce to be installed and activated.', 'whatsapp-order-button');
            echo '</p></div>';
        });
        // Deactivate this plugin
        deactivate_plugins(plugin_basename(__FILE__));
    }
}
add_action('admin_init', 'wob_check_woocommerce');

/**
 * Activation hook - set default options
 */
function wob_activate()
{
    $defaults = array(
        // General settings
        'wob_phone_number' => '947000000000',
        'wob_button_text' => __('Order via WhatsApp', 'whatsapp-order-button'),
        'wob_icon_url' => '', // Empty means use default local icon
        'wob_intro_message' => __('Hello, I\'d like to place an order:', 'whatsapp-order-button'),
        // Design settings
        'wob_bg_color' => '#25D366',
        'wob_text_color' => '#ffffff',
        'wob_hover_color' => '#1ebe5d',
        'wob_border_radius' => 8,
        'wob_font_size' => 16,
        'wob_margin_top' => 15,
        'wob_margin_bottom' => 15,
        'wob_padding_vertical' => 14,
        'wob_padding_horizontal' => 24,
    );

    foreach ($defaults as $option => $value) {
        if (get_option($option) === false) {
            add_option($option, $value);
        }
    }
}
register_activation_hook(__FILE__, 'wob_activate');

/**
 * Add Settings link to plugins page
 */
function wob_add_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=whatsapp-order-button">' . __('Settings', 'whatsapp-order-button') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wob_add_settings_link');

/**
 * Enqueue scripts and styles on product pages
 */
function wob_enqueue_assets()
{
    if (!is_product()) {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style(
        'wob-style',
        WOB_PLUGIN_URL . 'assets/css/style.css',
        array(),
        WOB_VERSION
    );

    // Enqueue JS
    wp_enqueue_script(
        'wob-script',
        WOB_PLUGIN_URL . 'assets/js/whatsapp-script.js',
        array('jquery'),
        WOB_VERSION,
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
    wp_localize_script('wob-script', 'wobData', array(
        'phoneNumber' => get_option('wob_phone_number', '947000000000'),
        'introMessage' => get_option('wob_intro_message', __('Hello, I\'d like to place an order:', 'whatsapp-order-button')),
        'productName' => $product_name,
        'productUrl' => $product_url,
        'currencySymbol' => $currency_symbol,
        'labels' => array(
            'product' => get_option('wob_label_product', __('Product', 'whatsapp-order-button')),
            'quantity' => get_option('wob_label_quantity', __('Quantity', 'whatsapp-order-button')),
            'price' => get_option('wob_label_price', __('Price', 'whatsapp-order-button')),
            'total' => get_option('wob_label_total', __('Total', 'whatsapp-order-button')),
            'link' => get_option('wob_label_link', __('Link', 'whatsapp-order-button')),
        ),
        'icons' => array(
            'product' => get_option('wob_icon_product', '🛒'),
            'quantity' => get_option('wob_icon_quantity', '🔢'),
            'price' => get_option('wob_icon_price', '💰'),
            'total' => get_option('wob_icon_total', '💵'),
            'link' => get_option('wob_icon_link', '🔗'),
        )
    ));

    // Add dynamic inline styles
    wob_add_dynamic_styles();
}
add_action('wp_enqueue_scripts', 'wob_enqueue_assets');

/**
 * Add dynamic inline styles from settings
 */
function wob_add_dynamic_styles()
{
    $bg_color = get_option('wob_bg_color', '#25D366');
    $text_color = get_option('wob_text_color', '#ffffff');
    $hover_color = get_option('wob_hover_color', '#1ebe5d');
    $border_radius = absint(get_option('wob_border_radius', 8));
    $font_size = absint(get_option('wob_font_size', 16));
    $margin_top = absint(get_option('wob_margin_top', 15));
    $margin_bottom = absint(get_option('wob_margin_bottom', 15));
    $padding_v = absint(get_option('wob_padding_vertical', 14));
    $padding_h = absint(get_option('wob_padding_horizontal', 24));

    $custom_css = "
        .whatsapp-button {
            background-color: {$bg_color} !important;
            color: {$text_color} !important;
            border-radius: {$border_radius}px !important;
            font-size: {$font_size}px !important;
            margin-top: {$margin_top}px !important;
            margin-bottom: {$margin_bottom}px !important;
            padding: {$padding_v}px {$padding_h}px !important;
        }
        .whatsapp-button:hover {
            background-color: {$hover_color} !important;
            color: {$text_color} !important;
        }
    ";

    wp_add_inline_style('wob-style', $custom_css);
}

/**
 * Get the icon URL (custom or default)
 */
function wob_get_icon_url()
{
    $custom_icon = get_option('wob_icon_url', '');
    return !empty($custom_icon) ? $custom_icon : wob_get_default_icon_url();
}

/**
 * Render WhatsApp button on product page
 */
function wob_render_button()
{
    if (!is_product()) {
        return;
    }

    $button_text = esc_html(get_option('wob_button_text', __('Order via WhatsApp', 'whatsapp-order-button')));
    $icon_url = esc_url(wob_get_icon_url());

    echo '<a href="#" id="whatsapp-order-btn" class="whatsapp-button">';
    echo '<img src="' . $icon_url . '" alt="WhatsApp" class="whatsapp-icon"> ';
    echo $button_text;
    echo '</a>';
}
add_action('woocommerce_after_add_to_cart_form', 'wob_render_button', 10);
