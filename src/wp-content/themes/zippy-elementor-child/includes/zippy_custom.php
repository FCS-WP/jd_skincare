<?php
add_action('wp_enqueue_scripts', 'shin_scripts');

function shin_scripts()
{
    $version = time();

    wp_enqueue_style('main-style-css', THEME_URL . '-child' . '/assets/dist/css/main.min.css', array(), $version, 'all');

    wp_enqueue_script('main-scripts-js', THEME_URL . '-child' . '/assets/dist/js/main.min.js', array('jquery'), $version, true);

    // Load Thirt-party
	wp_enqueue_style('vanilla-celendar-css', THEME_URL . '-child' . '/assets/lib/vanilla-calendar.min.css', array(), $version, 'all');
	wp_enqueue_script('vanilla-scripts-js', THEME_URL . '-child' . '/assets/lib/vanilla-calendar.min.js', array('jquery'), $version, true);
}
