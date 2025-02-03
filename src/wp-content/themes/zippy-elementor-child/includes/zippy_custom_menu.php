<?php 

function custom_menu_item($atts)
{   
    $menu_id = '';

    if (!empty($atts['id'])) {
        $menu_id = $atts['id'];
    } elseif (!empty($atts['name'])) {
        $menu_id = wp_get_nav_menu_object($atts['name'])->term_id ?? '';
    } elseif (!empty($atts['slug'])) {
        $menu_id = wp_get_nav_menu_object($atts['slug'])->term_id ?? '';
    }
    $menu_items = wp_get_nav_menu_items($menu_id);

    if (empty($menu_items)) {
        return '<p>No Data.</p>';
    }
    // var_dump($menu_items);
    $parents = [];
    $children = [];

    foreach ($menu_items as $item) {
        $parent_id = $item->menu_item_parent;
        if ($parent_id == 0) {
            $parents[] = $item;
        } else {
            $children[] = $item;
        }
    }
    $children = convert_child_items($children);
    $savedHtml = '';
    $output = '<div class="custom-dropdown-view"> <div class="custom-width row">';

    $output .= '<div class="column column-3 border-right-md">';
    foreach ($parents as $key => $parentItem) {
        $output .= '<p data-id="'.$parentItem->ID.'" data-link="'.$parentItem->url.'" class="custom-li-item has-submenu">'.$parentItem->title.' &#62; </p>';
    }
    $output .= '</div>';

    $output .= '<div class="column column-3 border-right-md">';
    foreach ($children as $key => $childItem) {
        $output .= '<div class="child-box child-of-'.$childItem->menu_item_parent.'" style="display: none;">';
        if (!empty($childItem->subItems)) {
            $output .= '<p  data-id="'.$childItem->ID.'" data-link="'.$childItem->url.'" class="custom-li-item has-submenu">'.$childItem->title.'  &#62; </pdata-id=>';
            $savedHtml .= '<div class="row child-box child-of-'.$childItem->ID.' child-2" style="display: none;">';
            $savedHtml .= render_custom_menu($childItem->subItems);
            $savedHtml .= '</div>';
        } else {
            $output .= '<p data-id="'.$childItem->ID.'" data-link="'.$childItem->url.'" class="custom-li-item child-1 redirect-item">'.$childItem->title.'</p>';
        }
        $output .= '</div>';
    }
    $output .= '</div>';

    $output .= '<div class="column column-6">';
    $output .= $savedHtml;
    $output .= '</div>';

    $output .= '</div></div>';
    return $output;
}   

function render_custom_menu($items) {
    $html = '';
    foreach ($items as $item) {
        $html .= '<div class="column column-6">';
        $html .= '<strong data-link="'.$item->url.'" class="">'.$item->title.'</strong>';
        if ($item->subItems) {
           $html .= loop_do_shortcode($item->subItems);
        }
        $html .= '</div>';
    }
    return $html;
}

function loop_do_shortcode($items) {
    $result = '';
    foreach ($items as $item) {
        $shortcode = get_shortcode($item->post_title);
        if ($shortcode) {
            $result .= '<div>'; 
            $result .= do_shortcode($shortcode);
            $result .= '</div>';
        }
    }
    return $result;
}

function get_shortcode($str) {
    if (strpos($str, 'custom_do_shortcode')  !== false)  {
        return str_replace('custom_do_shortcode', '', $str);
    }
    return null;
}

function convert_child_items ($arr) {
    $catchIds = [];
    foreach ($arr as $key => $item) {
        $current_id = $item->ID;
        $getSubs = getSubCategories($current_id, $arr);
        if (count($getSubs)) {
            $item->subItems = $getSubs;
            $catchIds = array_merge($catchIds, $getSubs);
        }
    }

    $results = array_filter($arr, function ($item) use ($catchIds) {
        $check = array_filter($catchIds, function ($checkItem) use ($item) { 
            return $item->ID === $checkItem->ID;
        });
        return !count($check);
    });

    return $results;
}

function getSubCategories ($checkId, $arr) {
    $results = array_filter($arr, function($item) use ($checkId){
        return $item->menu_item_parent == $checkId;
    });
    return $results;
}

add_shortcode('custom_menu_item', 'custom_menu_item');

function get_wp_menu ($atts) {
    
    $menu_id = '';
    if (!empty($atts['id'])) {
        $menu_id = $atts['id'];
    } elseif (!empty($atts['name'])) {
        $menu_id = wp_get_nav_menu_object($atts['name'])->term_id ?? '';
    } elseif (!empty($atts['slug'])) {
        $menu_id = wp_get_nav_menu_object($atts['slug'])->term_id ?? '';
    }

    if (empty($menu_id)) {
        return '<p>Menu not found.</p>';
    }

    $menu_items = wp_get_nav_menu_items($menu_id);

    if (empty($menu_items)) {
        return '<p>Menu has no item.</p>';
    }
    
    $output = '<div class="show-menu">';
    foreach ($menu_items as $item) {
        $output.= '<p data-link="'. esc_url($item->url) .'" role="button" class="custom-li-item redirect-item">'. esc_html($item->title).'</p>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('get_wp_menu', 'get_wp_menu');
