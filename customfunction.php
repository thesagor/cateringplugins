<?php
/*
Plugin Name: Catering Booking Request (No Payment)
Description: Converts WooCommerce checkout into a booking request form by hiding payment methods and customizing checkout fields/buttons.
Version: 1.5
Author: Sagor
*/

// Change checkout button to "Anfrage senden"
add_filter('woocommerce_order_button_text', function () {
    return 'Anfrage senden';
});

// Change cart "Proceed to Checkout" button to "Anfragen"
add_filter('woocommerce_get_checkout_url', function ($url) {
    return $url;
});
add_filter('woocommerce_proceed_to_checkout_text', function () {
    return 'Anfragen';
});

// Add custom checkout fields: Datum (date picker) and Uhrzeit (time picker) outside billing
add_action('woocommerce_after_order_notes', function ($checkout) {
    echo '<div class="custom-booking-fields"><h3>Event Informationen</h3>';

    woocommerce_form_field('event_date', [
        'type'     => 'date',
        'class'    => ['form-row-first'],
        'label'    => 'Datum',
        'required' => true,
    ], $checkout->get_value('event_date'));

    woocommerce_form_field('event_time', [
        'type'     => 'time',
        'class'    => ['form-row-last'],
        'label'    => 'Uhrzeit',
        'required' => true,
    ], $checkout->get_value('event_time'));

    echo '</div>';
});

// Save the custom fields to the order
add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    if (!empty($_POST['event_date'])) {
        update_post_meta($order_id, 'event_date', sanitize_text_field($_POST['event_date']));
    }
    if (!empty($_POST['event_time'])) {
        update_post_meta($order_id, 'event_time', sanitize_text_field($_POST['event_time']));
    }
});

// Display fields in admin order details
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
    echo '<p><strong>Datum:</strong> ' . get_post_meta($order->get_id(), 'event_date', true) . '</p>';
    echo '<p><strong>Uhrzeit:</strong> ' . get_post_meta($order->get_id(), 'event_time', true) . '</p>';
});

// Show fields in email
add_filter('woocommerce_email_order_meta_fields', function ($fields, $sent_to_admin, $order) {
    $fields['datum'] = [
        'label' => 'Datum',
        'value' => get_post_meta($order->get_id(), 'event_date', true),
    ];
    $fields['uhrzeit'] = [
        'label' => 'Uhrzeit',
        'value' => get_post_meta($order->get_id(), 'event_time', true),
    ];
    return $fields;
}, 10, 3);

add_action('woocommerce_after_order_notes', function ($checkout) {
    echo '<div class="custom-booking-fields-notes">';

    woocommerce_form_field('custom_notes', [
        'type'     => 'textarea',
        'class'    => ['form-row-wide'],
        'label'    => 'Notiz',
        'required' => false,
    ], $checkout->get_value('custom_notes'));

    echo '</div>';
});


add_action('wp_footer', function () {
    if (is_checkout()) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function () {
                const btn = document.querySelector("#place_order");
                if (btn) {
                    btn.value = "Anfrage senden";
                    btn.dataset.value = "Anfrage senden";
                }
            });
        </script>';
    }
});




add_action('wp_footer', function () {
    if (is_cart()) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function () {
                const cartBtn = document.querySelector(".checkout-button");
                if (cartBtn) {
                    cartBtn.textContent = "Anfragen";
                }
            });
        </script>';
    }
});


add_action('wp_footer', function () {
    if (is_cart() || is_checkout()) {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function () {
            function waitForProceedText() {
                const el = document.querySelector(".proceed-inner");
                if (el && el.textContent.includes("Jetzt zahlen")) {
                    el.textContent = "Anfragen";
                } else {
                    // Try again in 300ms
                    setTimeout(waitForProceedText, 300);
                }
            }

            waitForProceedText();
        });
        </script>';
    }
});



add_action('wp_enqueue_scripts', function () {
    if (is_checkout()) {
        wp_enqueue_style(
            'catering-booking-style',
            plugin_dir_url(__FILE__) . 'assets/css/custom-booking-style.css',
            [],
            '1.0'
        );
    }
});



