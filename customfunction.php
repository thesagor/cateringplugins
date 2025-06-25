<?php
/*
Plugin Name: Catering Booking Request
Description: Converts WooCommerce checkout into a booking request form by hiding payment methods and customizing checkout fields/buttons.
Version: 1.6
Author: Sagor Ahmed
*/



// Remove "Rechnung" and set email field placeholder
add_filter('woocommerce_checkout_fields', function ($fields) {
    $fields['billing']['billing_email']['label'] = 'E-Mail-Adresse';
    $fields['billing']['billing_email']['placeholder'] = 'E-Mail-Adresse';
    unset($fields['billing']['billing_state']); // Remove Bundesland
    return $fields;
});

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

/*// Add custom checkout fields (event date and optional note)
add_action('woocommerce_after_order_notes', function ($checkout) {
    echo '<div class="custom-booking-fields hide">';

   woocommerce_form_field('event_date', [
        'type'        => 'date',
        'class'       => ['form-row-first','eventdate'],
        'label'    => 'Eventdatum',
        'placeholder' => '',
        'required'    => true,
    ], $checkout->get_value('event_date'));




    woocommerce_form_field('custom_notes', [
        'type'        => 'textarea',
        'class'       => ['form-row-wide'],
        'placeholder' => 'Notiz (optional)',
        'required'    => false,
    ], $checkout->get_value('custom_notes'));

    echo '</div>';
});*/

add_action('woocommerce_after_order_notes', function () {
    echo '<div class="custom-booking-fields"><div class="form-row form-row-wide validate-required" id="event_date_field" data-priority="3">
        <span class="woocommerce-input-wrapper">
            <input type="date" class="input-text " name="event_date" id="event_date" placeholder=" " required />
            <label for="event_date" class="required_field">Eventdatum&nbsp;<span class="required" aria-hidden="true">*</span></label>
        </span>
    </div>';

    echo '<div class="form-row form-row-wide" id="custom_notes_field">
        <span class="woocommerce-input-wrapper">
            <textarea name="custom_notes" class="input-text" placeholder="Notiz (optional)"></textarea>
        </span>
    </div></div>';
});


// Save the custom fields to the order
add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    if (!empty($_POST['event_date'])) {
        update_post_meta($order_id, 'event_date', sanitize_text_field($_POST['event_date']));
    }
    if (!empty($_POST['custom_notes'])) {
        update_post_meta($order_id, 'custom_notes', sanitize_textarea_field($_POST['custom_notes']));
    }
});

// Display fields in admin order details
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
    echo '<p><strong>Datum:</strong> ' . get_post_meta($order->get_id(), 'event_date', true) . '</p>';
    echo '<p><strong>Notiz:</strong> ' . get_post_meta($order->get_id(), 'custom_notes', true) . '</p>';
});

// Show fields in email
add_filter('woocommerce_email_order_meta_fields', function ($fields, $sent_to_admin, $order) {
    $fields['datum'] = [
        'label' => 'Datum',
        'value' => get_post_meta($order->get_id(), 'event_date', true),
    ];
    $fields['notiz'] = [
        'label' => 'Notiz',
        'value' => get_post_meta($order->get_id(), 'custom_notes', true),
    ];
    return $fields;
}, 10, 3);

// JS adjustments for button labels and date picker behavior
add_action('wp_footer', function () {
    if (is_checkout()) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function () {
                const btn = document.querySelector("#place_order");
                if (btn) {
                    btn.value = "Anfrage senden";
                    btn.dataset.value = "Anfrage senden";
                }
                const dateField = document.querySelector("input[name=event_date]");
                if (dateField) {
                    dateField.addEventListener("click", () => dateField.showPicker?.());
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



// Load custom CSS
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
