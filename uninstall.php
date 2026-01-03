<?php
/**
 * Uninstall Plugin
 *
 * Fired when the plugin is deleted.
 *
 * @package WhatsApp_Order_Button
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Additional security check
if (!current_user_can('activate_plugins')) {
    exit;
}

$wob_options = array(
    'wob_phone_number',
    'wob_button_text',
    'wob_icon_url',
    'wob_intro_message',
    'wob_bg_color',
    'wob_text_color',
    'wob_hover_color',
    'wob_border_radius',
    'wob_font_size',
    'wob_margin_top',
    'wob_margin_bottom',
    'wob_padding_vertical',
    'wob_padding_horizontal',
    'wob_label_product',
    'wob_icon_product',
    'wob_label_quantity',
    'wob_icon_quantity',
    'wob_label_price',
    'wob_icon_price',
    'wob_label_total',
    'wob_icon_total',
    'wob_label_link',
    'wob_icon_link'
);

foreach ($wob_options as $option) {
    delete_option($option);
}
