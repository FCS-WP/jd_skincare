<?php 

function products_by_category_shortcode($atts) {
 
    $atts = shortcode_atts(array(
        'category' => '', 
        'limit' => 12,
        'contain' => 0,
        'paged' => 1, 
    ), $atts, 'products_by_category_shortcode');


    $category_slug = sanitize_text_field($atts['category']);
    $limit = intval($atts['limit']);
    $paged = intval($atts['paged']);

    if (empty($category_slug)) {
        return '<p>Missing Slug.</p>';
    }
    echo '<input id="ajax_url" type="hidden" value="'. admin_url('admin-ajax.php') .'" >';
    // Query products
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $limit,
        'paged' => $paged,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category_slug,
            ),
        ),
    );
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        return '<p>Empty.</p>';
    }

    ob_start();

    echo '<div class="zippy-products-container">';
    while ($query->have_posts()) {
        $query->the_post();
        global $product;
        $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
        $price = wc_get_price_to_display( $product );
    ?>
        <div class="zippy-product-item">
            <a href="<?php echo get_permalink(); ?>">
                <div class="product-thumbnail">
                    <img class="<?php if ($atts['contain'] != 0) echo 'img-contain' ?>" src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                </div>
                <div class="product-infos">
                    <h3 class="product-title"> <?php echo get_the_title() ?> </h3>
                    <?php  if ( $product->get_price() ) {
                        ?> 
                            <h3 class="product-price"> <?php echo wc_price($price) ?> </h3>
                        <?php
                    }
                    ?>
                </div>
            </a>
        </div>
        <?php 
    }
    echo '</div>';

    $total_pages = $query->max_num_pages;
    $current_page = $paged;
     
    if ($total_pages <= 1) {
        wp_reset_postdata();
        return ob_get_clean();
    }
    
    echo '<div class="zippy-pagination" data-category="' . esc_attr($category_slug) . '" data-limit="' . esc_attr($limit) . '">';
    if ($current_page != 1) {
        echo '<a href="#" class="prev-btn zippy-page-link" data-page="'. ($current_page - 1) .'">Previous</a>';
        echo '<a href="#" class="zippy-page-link" data-page="'. ($current_page - 1) .'">'. ($current_page - 1) .'</a>';
    }
    echo '<a href="#" class="zippy-page-link active" data-page="'. ($current_page) .'"> '. ($current_page) .' </a>';
    if ($current_page != $total_pages) {
        echo '<a href="#" class="zippy-page-link" data-page="'. ($current_page + 1) .'">'. ($current_page + 1) .'</a>';
        echo '<a href="#" class="next-btn zippy-page-link" data-page="'. ($current_page + 1) .'">Next</a>';
    }
    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('zippy_products_by_cat', 'products_by_category_shortcode');


function zippy_products_pagination() {
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 12;
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;

    if (empty($category)) {
        wp_send_json_error('Missing category');
        wp_die();
    }

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $limit,
        'paged' => $paged,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category,
            ),
        ),
    );

    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
            $price = wc_get_price_to_display($product);
        ?>
            <div class="zippy-product-item">
                <a href="<?php echo get_permalink(); ?>">
                    <div class="product-thumbnail">
                        <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                    </div>
                    <div class="product-infos">
                        <h3 class="product-title"><?php echo get_the_title(); ?></h3>
                        <?php if ($product->get_price()) { ?>
                            <h3 class="product-price"><?php echo wc_price($price); ?></h3>
                        <?php } ?>
                    </div>
                </a>
            </div>
        <?php
        }
    } else {
        echo '<p>Empty.</p>';
    }

    wp_reset_postdata();

    $content = ob_get_clean();

    $current_page = max(1, $paged);
    $total_pages = $query->max_num_pages;

    ob_start();

    echo '<div class="zippy-pagination" data-category="' . esc_attr($category) . '" data-limit="' . esc_attr($limit) . '">';
    if ($current_page != 1) {
        echo '<a href="#" class="prev-btn zippy-page-link" data-page="' . ($current_page - 1) . '">Previous</a>';
        echo '<a href="#" class="zippy-page-link" data-page="' . ($current_page - 1) . '">' . ($current_page - 1) . '</a>';
    }
    echo '<a href="#" class="zippy-page-link active" data-page="' . $current_page . '">' . $current_page . '</a>';
    if ($current_page != $total_pages) {
        echo '<a href="#" class="zippy-page-link" data-page="' . ($current_page + 1) . '">' . ($current_page + 1) . '</a>';
        echo '<a href="#" class="next-btn zippy-page-link" data-page="' . ($current_page + 1) . '">Next</a>';
    }
    echo '</div>';

    $pagination_content = ob_get_clean();
    
    wp_send_json_success(array(
        'products' => $content,
        'pagination' => $pagination_content,
    ));

    wp_die();
}

add_action('wp_ajax_zippy_products_pagination', 'zippy_products_pagination');
add_action('wp_ajax_nopriv_zippy_products_pagination', 'zippy_products_pagination');
