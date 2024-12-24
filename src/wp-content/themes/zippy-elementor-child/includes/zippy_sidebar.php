<?php

function custom_theme_sidebar() {
    register_sidebar(array(
        'name'          => __('Custom Sidebar', 'custom-theme'),
        'id'            => 'main-sidebar',
        'description'   => __('Custom sidebar.', 'custom-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'custom_theme_sidebar');