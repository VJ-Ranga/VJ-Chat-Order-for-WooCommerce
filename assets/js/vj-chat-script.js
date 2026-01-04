/**
 * VJ Chat Order Script (Refactored to Vanilla JS)
 * 
 * Handles price calculation, variant detection, and WhatsApp message construction
 * Matches user's preferred logic structure.
 * 
 * @package VJ_Chat_Order
 */

document.addEventListener("DOMContentLoaded", function () {
    var whatsappButton = document.getElementById("vj-chat-order-btn");

    // Check if button exists and data is loaded
    if (!whatsappButton || typeof vjChatData === 'undefined') {
        return;
    }

    whatsappButton.addEventListener("click", function (event) {
        event.preventDefault();

        // 1. Get Quantity
        var qtyInput = document.querySelector('input.qty');
        var quantity = qtyInput ? parseInt(qtyInput.value) : 1;
        if (isNaN(quantity) || quantity < 1) quantity = 1;

        // 2. Get Price & Calculate Total
        var rawPriceText = "";
        var variationPriceElement = document.querySelector('.woocommerce-variation-price .price .amount');
        var simplePriceElement = document.querySelector('.summary .price .amount');

        if (variationPriceElement) {
            rawPriceText = variationPriceElement.innerText.trim();
        } else if (simplePriceElement) {
            rawPriceText = simplePriceElement.innerText.trim();
        }

        // Helper to turn currency string (e.g., "$ 700.00" or "Rs. 700.00") into a number
        // User's preferred regex: Remove everything except digits and dots
        var priceNumeric = parseFloat(rawPriceText.replace(/[^\d.]/g, ''));

        if (isNaN(priceNumeric)) {
            priceNumeric = 0;
        }

        var totalPrice = (priceNumeric * quantity).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        var currency = vjChatData.currencySymbol || '';

        // 3. Get Variants
        var variantText = "";
        var variationIdInput = document.querySelector('input.variation_id');

        if (variationIdInput && variationIdInput.value && variationIdInput.value !== "0") {
            var details = [];
            document.querySelectorAll(".variations select").forEach(select => {
                if (select.value) {
                    // Extract clean name from the 'name' attribute
                    var attrName = select.name.replace('attribute_pa_', '')
                        .replace('attribute_', '')
                        .replace(/-/g, ' ');

                    // Capitalize first letter
                    attrName = attrName.charAt(0).toUpperCase() + attrName.slice(1);

                    // Get selected option text
                    var attrVal = select.options[select.selectedIndex].text;
                    details.push(attrName + ": " + attrVal);
                }
            });

            if (details.length > 0) {
                variantText = "\n📌 *" + (vjChatData.labels.variant || 'Variant') + ":* " + details.join(", ");
            }
        }

        // 4. Construct Final Message
        var message = (vjChatData.introMessage || "Hello, I'd like to place an order:") + "\n\n";
        message += "🛒 *" + (vjChatData.labels.product || 'Product') + ":* " + (vjChatData.productName || 'Product') + "\n";
        message += "🔢 *" + (vjChatData.labels.quantity || 'Quantity') + ":* " + quantity + "\n";
        message += "💰 *" + (vjChatData.labels.price || 'Price') + ":* " + rawPriceText + variantText + "\n";
        message += (vjChatData.icons.total || '💵') + " *" + (vjChatData.labels.total || 'Total') + ":* " + currency + " " + totalPrice + "\n\n";
        message += "🔗 *" + (vjChatData.labels.link || 'Link') + ":* " + (vjChatData.productUrl || '') + "";

        // Open WhatsApp
        var phone = vjChatData.phoneNumber || '';
        window.open("https://api.whatsapp.com/send?phone=" + phone + "&text=" + encodeURIComponent(message), "_blank");
    });
});
