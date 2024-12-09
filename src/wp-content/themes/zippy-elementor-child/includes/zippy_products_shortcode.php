<?php 

function products_by_category_shortcode($atts) {
 
    $atts = shortcode_atts(array(
        'category' => '', 
        'contain' => 0,
    ), $atts, 'products_by_category_shortcode');


    $category_slug = sanitize_text_field($atts['category']);
    // $limit = intval($atts['limit']);

    if (empty($category_slug)) {
        return '<p>Missing Slug.</p>';
    }

    // Query products
    $args = array(
        'post_type' => 'product',
        // 'posts_per_page' => $limit,
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
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('zippy_products_by_cat', 'products_by_category_shortcode');


function get_products_by_archive($atts) {
    $atts = shortcode_atts(array(
        'category' => '', 
        'limit'    => 12, 
    ), $atts, 'archive_products');

    if (is_tax('product_cat')) {
        $category_slug = get_queried_object()->slug;
    } else {
        $category_slug = sanitize_text_field($atts['category']);
    }

    if (empty($category_slug)) {
        return '<p>No category specified or found.</p>';
    }

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => intval($atts['limit']),
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category_slug,
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
                    <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
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
    echo custom_pagination($query);
    wp_reset_postdata();
    return ob_get_clean();
}


function custom_pagination($query) {
    $big = 999999999;
    $pagination = paginate_links(array(
        'base'         => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format'       => '?paged=%#%',
        'current'      => max(1, get_query_var('paged')),
        'total'        => $query->max_num_pages,
        'prev_text'    => '&laquo; Previous',
        'next_text'    => 'Next &raquo;',
        'type'          => 'list',
    ));

    if ($pagination) {
        return '<div class="pagination">' . $pagination . '</div>';
    }
    return '';
}

add_shortcode('get_products_by_archive', 'get_products_by_archive');