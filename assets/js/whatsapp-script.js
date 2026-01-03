/**
 * WhatsApp Order Button Script
 * 
 * Handles price calculation, variant detection, and WhatsApp message construction
 * 
 * @package WhatsApp_Order_Button
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        var whatsappButton = $('#whatsapp-order-btn');

        if (!whatsappButton.length) {
            return;
        }

        whatsappButton.on('click', function (event) {
            event.preventDefault();

            // 1. Get Quantity
            var qtyInput = $('input.qty');
            var quantity = qtyInput.length ? parseInt(qtyInput.val()) : 1;
            if (isNaN(quantity) || quantity < 1) {
                quantity = 1;
            }

            // 2. Get Price & Calculate Total
            var rawPriceText = '';
            var variationPriceElement = $('.woocommerce-variation-price .price .amount');
            var simplePriceElement = $('.summary .price .amount');

            if (variationPriceElement.length) {
                rawPriceText = variationPriceElement.first().text().trim();
            } else if (simplePriceElement.length) {
                rawPriceText = simplePriceElement.first().text().trim();
            }

            // Parse price - remove currency symbols and formatting
            var priceNumeric = parseFloat(rawPriceText.replace(/[^\d.]/g, ''));
            if (isNaN(priceNumeric)) {
                priceNumeric = 0;
            }

            var totalPrice = (priceNumeric * quantity).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            var currency = wobData.currencySymbol || '';

            // 3. Get Variants
            var variantText = '';
            var variationIdInput = $('input.variation_id');

            if (variationIdInput.length && variationIdInput.val() && variationIdInput.val() !== '0') {
                var details = [];

                $('.variations select').each(function () {
                    var select = $(this);
                    if (select.val()) {
                        // Extract clean attribute name
                        var attrName = select.attr('name')
                            .replace('attribute_pa_', '')
                            .replace('attribute_', '')
                            .replace(/-/g, ' ');

                        // Capitalize first letter
                        attrName = attrName.charAt(0).toUpperCase() + attrName.slice(1);

                        // Get selected option text
                        var attrVal = select.find('option:selected').text();
                        details.push(attrName + ': ' + attrVal);
                    }
                });

                if (details.length > 0) {
                    variantText = '\n📌 *Variant:* ' + details.join(', ');
                }
            }

            // 4. Construct Final Message
            var introMessage = wobData.introMessage || 'Hello, I\'d like to place an order:';
            var productName = wobData.productName || 'Product';
            var productUrl = wobData.productUrl || '';
            var phoneNumber = wobData.phoneNumber || '';

            // Default fallbacks (if not set in PHP)
            var icons = wobData.icons || {
                product: '🛒', quantity: '🔢', price: '💰', total: '💵', link: '🔗'
            };
            var labels = wobData.labels || {
                product: 'Product', quantity: 'Quantity', price: 'Price', total: 'Total', link: 'Link'
            };

            var message = introMessage + '\n\n';
            message += icons.product + ' *' + labels.product + ':* ' + productName + '\n';
            message += icons.quantity + ' *' + labels.quantity + ':* ' + quantity + '\n';
            message += icons.price + ' *' + labels.price + ':* ' + rawPriceText + variantText + '\n';
            message += icons.total + ' *' + labels.total + ':* ' + currency + ' ' + totalPrice + '\n\n';
            message += icons.link + ' *' + labels.link + ':* ' + productUrl;

            // Open WhatsApp
            var whatsappUrl = 'https://api.whatsapp.com/send?phone=' + phoneNumber + '&text=' + encodeURIComponent(message);
            window.open(whatsappUrl, '_blank');
        });
    });

})(jQuery);
