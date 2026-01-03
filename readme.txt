=== WhatsApp Order Button for WooCommerce ===
Contributors: vjranga
Donate link: https://www.buymeacoffee.com/vjranga
Tags: woocommerce, whatsapp, order, button, ecommerce
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a customizable "Order via WhatsApp" button to your WooCommerce product pages, allowing customers to send order details directly to your WhatsApp number.

== Description ==

WhatsApp Order Button for WooCommerce adds a beautiful, customizable button to your product pages that lets customers instantly contact you via WhatsApp with pre-filled order details.

= Key Features =

* **One-Click Ordering:** Customers can order instantly without a complex checkout process
* **Fully Customizable Message:** Change every label (Product, Price, Quantity, Total, Link)
* **Custom Icons:** Use any emoji or text for message lines (🛒, 💰, 🔗, etc.)
* **Smart Data Capture:** Automatically captures product name, price, selected variations, and product URL
* **Live Preview:** Real-time preview in the admin dashboard
* **Modern Admin UI:** Clean tabbed interface with responsive sidebar preview
* **Mobile Optimized:** Fully responsive on all devices
* **Design Customization:** Customize colors, padding, border radius, and more

= Example Message =

When customers click the button, they'll send a message like this:

`
Hello, I'd like to place an order:

🛒 Product: Men's Classic T-Shirt
🔢 Quantity: 2
💰 Price: $25.00
💵 Total: $50.00
🔗 Link: https://yourstore.com/product/t-shirt
`

= Perfect For =

* WooCommerce stores looking to increase conversions
* Businesses that prefer direct communication with customers
* Stores selling custom or high-value products
* International stores using WhatsApp as primary communication

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 5.0 or higher
* PHP 7.4 or higher

== Installation ==

1. Upload the `whatsapp-order-button` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Settings > WhatsApp Order** to configure your number and customize the button
4. The button will automatically appear on all WooCommerce product pages

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, this plugin requires WooCommerce to be installed and activated.

= What format should I use for the phone number? =

Enter your WhatsApp number with country code, without the + sign or spaces. Example: 947000000000 (Sri Lanka number)

= Can I customize the button design? =

Yes! You can customize colors, size, padding, border radius, and more from the Design tab in settings.

= Can I change the message text and icons? =

Absolutely! Go to the Message tab to customize labels and add emojis or custom icons for each field.

= Does this work with variable products? =

Yes! The plugin automatically detects and includes selected variations (like size, color) in the WhatsApp message.

= Will this work with my theme? =

Yes, the plugin is designed to work with any WordPress theme. It includes special support for sticky add-to-cart bars (like Astra theme).

= Can I use custom icons? =

Yes, you can upload your own icon or use emojis. Use Win + . (Windows) or Cmd + Ctrl + Space (Mac) to insert emojis.

== Screenshots ==

1. Frontend button on product page
2. Admin sidebar live preview
3. General settings tab
4. Message customization tab
5. Design settings tab
6. Example generated WhatsApp message

== Changelog ==

= 1.3.1 =
* Performance: Optimized uninstall process with direct SQL deletion to prevent timeouts on large sites.

= 1.3.0 =
* Feature: Added helpful "Pro Tip" box for using emojis in message labels
* Enhancement: Added direct link to Emojipedia for finding icons
* Enhancement: UI improvements for labels section
* Improvement: Better user experience with emoji suggestions

= 1.2.0 =
* Fix: Solved double "Settings saved" notification issue
* Fix: Corrected settings link slug mismatch
* Enhancement: Added icon uploader validation and preview improvements
* Enhancement: Added frontend data validation for stability
* Enhancement: Optimized performance with lazy loading for admin images

= 1.0.0 =
* Initial release
* Basic WhatsApp order functionality
* Customizable button and message
* Design customization options
* Live preview feature

== Upgrade Notice ==

= 1.3.0 =
This version adds helpful emoji tips and improves the user interface. Safe to update.

= 1.2.0 =
Important bug fixes for admin notifications and improved stability. Recommended update.

== Privacy Policy ==

WhatsApp Order Button for WooCommerce does not collect, store, or transmit any user data. All order information is sent directly from the customer's device to WhatsApp.

== Support ==

For support, please visit the plugin's support forum on WordPress.org or contact us through our website.

== Credits ==

Developed by VJ Ranga
WhatsApp icon by Font Awesome (CC BY 4.0)
