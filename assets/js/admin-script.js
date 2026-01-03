jQuery(document).ready(function ($) {

    // ==========================================
    // Tab Switching Logic
    // ==========================================
    // Check for saved tab (Server-Side)
    var activeTab = wobAdminData.activeTab || 'general';

    // Initialize: Set the saved tab as active
    function initializeTabs() {
        $('.wob-tab-btn').removeClass('active');
        $('.wob-tab-panel').removeClass('active');

        $('.wob-tab-btn[data-tab="' + activeTab + '"]').addClass('active');
        $('#tab-' + activeTab).addClass('active');
    }

    // Initialize on page load
    initializeTabs();

    // Handle tab clicks
    $('.wob-tab-btn').on('click', function (e) {
        e.preventDefault();

        var tabId = $(this).data('tab');

        // Validate tab exists
        if (!$('#tab-' + tabId).length) {
            console.warn('Tab not found: ' + tabId);
            return;
        }

        // Update UI immediately (no server delay)
        activeTab = tabId;
        initializeTabs();

        // Save to server (fire and forget)
        $.post(wobAdminData.ajaxUrl, {
            action: 'wob_save_active_tab',
            tab: tabId,
            nonce: wobAdminData.nonce
        }).fail(function () {
            console.error('Failed to save tab preference');
        });
    });

    // ==========================================
    // Media Uploader Logic
    // ==========================================
    var defaultIcon = wobAdminData.defaultIcon;
    var mediaUploader;

    $('.wob-upload-icon-btn').on('click', function (e) {
        e.preventDefault();

        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Extend the wp.media object
        mediaUploader = wp.media({
            title: wobAdminData.uploaderTitle,
            button: {
                text: wobAdminData.uploaderButton
            },
            multiple: false
        });

        // When a file is selected, grab the URL and set it as the text field's value
        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#wob_icon_url').val(attachment.url);
            $('.wob-icon-preview img').attr('src', attachment.url);
        });

        // Open the uploader dialog
        mediaUploader.open();
    });

    // Reset to Default Icon
    $('.wob-reset-icon-btn').on('click', function (e) {
        e.preventDefault();
        $('#wob_icon_url').val('');
        $('.wob-icon-preview img').attr('src', defaultIcon);
    });

    // Valid URL Manual Entry Update
    $('#wob_icon_url').on('change input', function () {
        var url = $(this).val();
        if (url.length > 0) {
            $('.wob-icon-preview img').attr('src', url);
        } else {
            $('.wob-icon-preview img').attr('src', defaultIcon);
        }
    });

    // ==========================================
    // Toast Notifications
    // ==========================================

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
