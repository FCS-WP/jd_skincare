<?php
//function remove basic field on checkout page
add_filter('woocommerce_checkout_fields', 'remove_billing_details');
function remove_billing_details($fields) {
    // Remove specific billing fields
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_postcode']);

    
    return $fields;
}

//function create new field on checkout page
add_filter('woocommerce_checkout_fields', 'add_multiple_custom_checkout_fields');
function add_multiple_custom_checkout_fields($fields) {
    $cart = WC()->cart;
     
    foreach ($cart->get_cart() as $cart_item){
    
    $fields['billing']['no_of_passengers'] = array(
        'type'        => 'hidden',
        'placeholder' => __('Enter no. of Passengers', 'woocommerce'),
        'class'       => array('form-row-wide'),
        'clear'       => true,
        'default'     => $cart_item['booking_information']['no_of_passengers']
    );
    
    $fields['billing']['no_of_baggage'] = array(
        'type'        => 'hidden',
        'placeholder' => __('Enter no. of Baggage', 'woocommerce'),
        'class'       => array('form-row-wide'),
        'clear'       => true,
        'default'     => $cart_item['booking_information']['no_of_baggage']
    );

    $fields['billing']['service_type'] = array(
        'type'        => 'hidden', 
        'class'       => array('form-row-wide hidden-field'),
        'clear'       => true,
        'default'     => $cart_item['booking_information']['service_type']
    );

    $fields['billing']['eta_time'] = array(
        'type'        => 'hidden', 
        'class'       => array('form-row-wide hidden-field'),
        'clear'       => true,
        'default'     => $cart_item['booking_trip']['eta_time']
    );

    $fields['billing']['flight_details'] = array(
        'type'        => 'hidden', 
        'class'       => array('form-row-wide hidden-field'),
        'clear'       => true,
        'default'     => $cart_item['booking_trip']['flight_details']
    );

    $fields['billing']['key_member'] = array(
        'type'        => 'hidden', 
        'class'       => array('form-row-wide hidden-field'),
        'clear'       => true,
        'default'     => $cart_item['booking_information']['key_member']
    );

    $fields['billing']['pick_up_date'] = array(
        'type'        => 'hidden',
        'class'       => array('form-row-wide hidden-field'),
        'clear'       => true,
        'default'     => $cart_item['booking_information']['pick_up_date']
    );

    $fields['billing']['pick_up_time'] = array(
        'type'        => 'hidden',
        'class'       => array('form-row-wide hidden-field'),
        'clear'       => true,
        'default'     => $cart_item['booking_information']['pick_up_time']
    );

    $fields['billing']['pick_up_location'] = array(
        'type'        => 'hidden',
        'class'       => array('form-row-wide hidden-field'),
        'clear'       => true,
        'default'     => $cart_item['booking_information']['pick_up_location']
    );

    $fields['billing']['drop_off_location'] = array(
        'type'        => 'hidden',
        'class'       => array('form-row-wide hidden-field'),
        'clear'       => true,
        'default'     => $cart_item['booking_information']['drop_off_location']
    );

    $fields['billing']['special_requests'] = array(
        'type'        => 'hidden',
        'class'       => array('form-row-wide hidden-field'),
        'clear'       => true,
        'default'     => $cart_item['booking_information']['special_requests']
    );
    }
    return $fields;
}
add_action('woocommerce_checkout_update_order_meta', 'save_multiple_custom_checkout_fields');

//function save data booking to database
function save_multiple_custom_checkout_fields($order_id) {

    if (!empty($_POST['no_of_passengers'])) {
        update_post_meta($order_id, 'no_of_passengers', sanitize_text_field($_POST['no_of_passengers']));
    }

    if (!empty($_POST['no_of_baggage'])) {
        update_post_meta($order_id, 'no_of_baggage', sanitize_text_field($_POST['no_of_baggage']));
    }

    if (!empty($_POST['service_type'])) {
        update_post_meta($order_id, 'service_type', sanitize_text_field($_POST['service_type']));
    }
    if (!empty($_POST['flight_details'])) {
        update_post_meta($order_id, 'flight_details', sanitize_text_field($_POST['flight_details']));
    }
    if (!empty($_POST['eta_time'])) {
        update_post_meta($order_id, 'eta_time', sanitize_text_field($_POST['eta_time']));
    }
    if (!empty($_POST['key_member'])) {
        update_post_meta($order_id, 'key_member', sanitize_text_field($_POST['key_member']));
    }
    if (!empty($_POST['pick_up_date'])) {
        update_post_meta($order_id, 'pick_up_date', sanitize_text_field($_POST['pick_up_date']));
    }
    if (!empty($_POST['pick_up_time'])) {
        update_post_meta($order_id, 'pick_up_time', sanitize_text_field($_POST['pick_up_time']));
    }
    if (!empty($_POST['pick_up_location'])) {
        update_post_meta($order_id, 'pick_up_location', sanitize_text_field($_POST['pick_up_location']));
    }
    if (!empty($_POST['drop_off_location'])) {
        update_post_meta($order_id, 'drop_off_location', sanitize_text_field($_POST['drop_off_location']));
    }
    if (!empty($_POST['special_requests'])) {
        update_post_meta($order_id, 'special_requests', sanitize_text_field($_POST['special_requests']));
    }
    

}
add_action('woocommerce_admin_order_data_after_billing_address', 'display_multiple_custom_checkout_fields_in_admin', 10, 1);

//function display information booking to order details page
function display_multiple_custom_checkout_fields_in_admin($order) {


    $service_type = get_post_meta($order->get_id(), 'service_type', true);
    if ($service_type) {
        echo '<p><strong>' . __('Service Type: ', 'woocommerce') . ':</strong> ' . esc_html($service_type) . '</p>';
    }

    $flight_details = get_post_meta($order->get_id(), 'flight_details', true);
    if ($flight_details) {
        echo '<p><strong>' . __('Flight Details: ', 'woocommerce') . ':</strong> ' . esc_html($flight_details) . '</p>';
    }

    $eta_time = get_post_meta($order->get_id(), 'eta_time', true);
    if ($eta_time) {
        echo '<p><strong>' . __('ETE/ETA Time: ', 'woocommerce') . ':</strong> ' . esc_html($eta_time) . '</p>';
    }


    $no_of_passengers = get_post_meta($order->get_id(), 'no_of_passengers', true);
    if ($no_of_passengers) {
        echo '<p><strong>' . __('No Of Passengers: ', 'woocommerce') . ':</strong> ' . esc_html($no_of_passengers) . '</p>';
    }

    $no_of_baggage = get_post_meta($order->get_id(), 'no_of_baggage', true);
    if ($no_of_baggage) {
        echo '<p><strong>' . __('No Of Baggage: ', 'woocommerce') . ':</strong> ' . esc_html($no_of_baggage) . '</p>';
    }

    $key_member = get_post_meta($order->get_id(), 'key_member', true);
    if ($key_member) {
        echo '<p><strong>' . __('Key Member: ', 'woocommerce') . ':</strong> ' . esc_html($key_member) . '</p>';
    }

    $pick_up_date = get_post_meta($order->get_id(), 'pick_up_date', true);
    if ($pick_up_date) {
        echo '<p><strong>' . __('Pick Up Date: ', 'woocommerce') . ':</strong> ' . esc_html($pick_up_date) . '</p>';
    }

    $pick_up_time = get_post_meta($order->get_id(), 'pick_up_time', true);
    if ($pick_up_time) {
        echo '<p><strong>' . __('Pick Up Time: ', 'woocommerce') . ':</strong> ' . esc_html($pick_up_time) . '</p>';
    }

    $pick_up_location = get_post_meta($order->get_id(), 'pick_up_location', true);
    if ($pick_up_location) {
        echo '<p><strong>' . __('Pick Up Location: ', 'woocommerce') . ':</strong> ' . esc_html($pick_up_location) . '</p>';
    }

    $drop_off_location = get_post_meta($order->get_id(), 'drop_off_location', true);
    if ($drop_off_location) {
        echo '<p><strong>' . __('Drop Off Location: ', 'woocommerce') . ':</strong> ' . esc_html($drop_off_location) . '</p>';
    }

    $special_requests = get_post_meta($order->get_id(), 'special_requests', true);
    if ($special_requests) {
        echo '<p><strong>' . __('Special Reuest: ', 'woocommerce') . ':</strong> ' . esc_html($special_requests) . '</p>';
    }

}

add_filter('woocommerce_available_payment_gateways', 'restrict_payment_methods_for_logged_in_users');


//function to divide member and guest
function restrict_payment_methods_for_logged_in_users($available_gateways) {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        // Loop through the available gateways
        foreach ($available_gateways as $gateway_id => $gateway) {
            // Allow only 'cod' (Cash on Delivery) for logged-in users
            if ($gateway_id !== 'cheque') {
                unset($available_gateways[$gateway_id]);
            }
        }
    }else{
        foreach ($available_gateways as $gateway_id => $gateway) {
            if ($gateway_id === 'cheque') {
                unset($available_gateways[$gateway_id]);
            }
        }
    }

    return $available_gateways;
}

function add_disposal_price_custom_field() {
    global $post;

    echo '<div class="options_group">';
    
    woocommerce_wp_text_input(
        array(
            'id'          => '_disposal_price',
            'label'       => __('Disposal Price', 'woocommerce'),
            'desc_tip'    => 'true',
            'description' => __('Enter the disposal price for this product.', 'woocommerce'),
            'type'        => 'text',
        )
    );

    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'add_disposal_price_custom_field');

function save_disposal_price_custom_field($post_id) {
    $disposal_price = isset($_POST['_disposal_price']) ? sanitize_text_field($_POST['_disposal_price']) : '';
    update_post_meta($post_id, '_disposal_price', $disposal_price);
}
add_action('woocommerce_process_product_meta', 'save_disposal_price_custom_field');


