<?php 

function epos_url ()
{   
    $epos_url = '';
    $booking_domain = get_field('booking_url', 'option');
    $store_id = urlencode(get_field('store_id', 'option'));

    if ($booking_domain) {
        $epos_url .= $booking_domain . '?';
    }

    if ($store_id) {
        $epos_url .= 'store_id='. urlencode($store_id) . '&';
    }

    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $epos_url .= $current_user->first_name ? 'first_name='.urlencode($current_user->first_name).'&' : '';
        $epos_url .= $current_user->last_name ? 'last_name='.urlencode($current_user->last_name).'&' : '';
        $epos_url .= $current_user->user_email ? 'email='.urlencode($current_user->user_email).'&' : '';
        $user_phone = get_user_meta(get_current_user_id(), 'billing_phone', true);
        $epos_url .= $user_phone ? 'phone_number='.$user_phone  : '';
    }
    return $epos_url;
}

add_shortcode('epos_url', 'epos_url');

function epos_link_tag ()
{   
    $epos_url = '';
    $booking_domain = get_field('booking_url', 'option');
    $store_id = urlencode(get_field('store_id', 'option'));

    if ($booking_domain) {
        $epos_url .= $booking_domain . '?';
    }

    if ($store_id) {
        $epos_url .= 'store_id='. urlencode($store_id) . '&';
    }

    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        // Display user information
        $epos_url .= $current_user->first_name ? 'first_name='.urlencode($current_user->first_name).'&' : '';
        $epos_url .= $current_user->last_name ? 'last_name='.urlencode($current_user->last_name).'&' : '';
        $epos_url .= $current_user->user_email ? 'email='.urlencode($current_user->user_email).'&' : '';
        $user_phone = get_user_meta(get_current_user_id(), 'billing_phone', true);
        $epos_url .= $user_phone ? 'phone_number='.$user_phone  : '';
    }
    return '<a href="'.$epos_url.'" class="elementor-item display-url" target="_blank" title="Make An Appointment">Make An Appointment</a>';
}

add_shortcode('epos_link_tag', 'epos_link_tag');