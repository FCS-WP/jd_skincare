<?php

function products_by_category_shortcode($atts)
{

    $atts = shortcode_atts(array(
        'category' => '',
        'limit' => 12,
        'contain' => 0,

    ), $atts, 'products_by_category_shortcode');


    $category_slug = sanitize_text_field($atts['category']);
    $limit = intval($atts['limit']);
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $price_filter = isset($_GET['min_price']) || isset($_GET['max_price']);
    $stock_filter = isset($_GET['filter_stock_status']) ? $_GET['filter_stock_status'] : '';
    
    if (empty($category_slug)) {
        return '<p>Missing Slug.</p>';
    }
    echo '<input id="ajax_url" type="hidden" value="' . admin_url('admin-ajax.php') . '" >';

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
        'meta_key' => '_price', 
        'orderby' => 'meta_value_num', 
        'order' => 'ASC',
    );
    

    // Filters

    if ($price_filter) {
        $min_price = !empty($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
        $max_price = !empty($_GET['max_price']) ? floatval($_GET['max_price']) : PHP_INT_MAX;

        $args['meta_query'][] = array(
            'key' => '_price',
            'value' => array($min_price, $max_price),
            'compare' => 'BETWEEN',
            'type' => 'NUMERIC',
        );
    }

    if ($stock_filter) {
        $args['meta_query'][] =  array(
            'key' => '_stock_status',
            'value' => $stock_filter,
            'compare' => '=',
        );
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No products found according to the request.</p>';
    }

    ob_start();

    echo '<div class="zippy-products-container">';
    
    while ($query->have_posts()) {
        $query->the_post();
        global $product;
        $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
        $price = wc_get_price_to_display($product);
?>
        <div class="zippy-product-item">
            <a href="<?php echo get_permalink(); ?>">
                <div class="product-thumbnail">
                    
                    <img class="<?php if ($atts['contain'] != 0) echo 'img-contain' ?>" src="<?php echo !empty($thumbnail_url) ? esc_url($thumbnail_url) : esc_url(wc_placeholder_img_src()) ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                </div>
                <div class="product-infos">
                    <h3 class="product-title"> <?php echo get_the_title() ?> </h3>
                    <?php if ($product->get_price()) {
                    ?>
                        <h3 class="product-price"> <?php echo wc_price($price) ?> </h3>
                    <?php
                    }
                    ?>
                </div>
                <div class="box-add-to-cart">
                    <?php
                    if ($product && $product->is_purchasable()) {
                    ?>
                        <a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
                            data-quantity="1"
                            class="button add_to_cart_button ajax_add_to_cart"
                            data-product_id="<?php echo $product->get_id(); ?>"
                            data-product_sku="<?php echo $product->get_sku(); ?>"
                            aria-label="<?php echo esc_attr($product->add_to_cart_description()); ?>"
                            rel="nofollow">

                            <?php
                            echo esc_html($product->add_to_cart_text());
                            ?>
                        </a>
                    <?php
                    } else {
                        return '<p>' . __('Product not available', 'your-textdomain') . '</p>';
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
    // Normal Pagination
    $pagination = paginate_links(array(
        'total' => $query->max_num_pages,
        'current' => $paged,
        'format' => '?paged=%#%',
        'prev_text' => __('« Previous'),
        'next_text' => __('Next »'),
    ));
    if ($pagination) {
        echo '<div class="zippy-pagination">' . $pagination . '</div>';
    }
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('zippy_products_by_cat', 'products_by_category_shortcode');

function zippy_custom_filter()
{ 
    ?> 
        <div class="zippy-sort-and-filters">
            <div class="custom-dropdown filter-box">
                <div class="custom-filter">
                    <button class="filter-button dropdown-btn" role="button" for="Filter">Filter</button>
                </div>
                <div class="dropdown-area" style="display: none;">
                    <form class="filter-form" action="" method="GET">
                        <div class="filter-item">
                            <h6>Filter by price :</h6>
                            <div class="filter-value">
                                <label class="input-label" for="min_price">Min Price:</label>
                                <input type="number" name="min_price" id="min_price" placeholder="Min Price" value="<?php echo(!empty($_GET['min_price']) ? floatval( $_GET['min_price']) : '') ?>"/>
                                <label class="input-label" for="max_price">Max Price:</label>
                                <input type="number" name="max_price" id="max_price" placeholder="Max Price" value="<?php echo(!empty($_GET['max_price']) ? floatval( $_GET['max_price']) : '') ?>"/>
                            </div>
                        </div>
                        <div class="filter-item">
                            <h6>Filter by stock</h6>
                            <div class="filter-value">
                                <label class="input-label" for="is_in_stock">Select Type:</label>
                                <select name="is_in_stock" id="is_in_stock">
                                    <option value="" <?php echo((!empty($_GET['filter_stock_status']) && $_GET['filter_stock_status'] == '') ? 'selected' : '') ?>>All</option>
                                    <option value="instock"<?php echo((!empty($_GET['filter_stock_status']) && $_GET['filter_stock_status'] == 'instock') ? 'selected' : '') ?>>In Stock</option>
                                    <option value="outofstock"<?php echo((!empty($_GET['filter_stock_status']) && $_GET['filter_stock_status'] == 'outofstock') ? 'selected' : '') ?>>Out Of Stock</option>
                                </select>
                            </div>
                        </div>
                        <div class="filter-button">
                            <button id="submit-filter">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php
}

add_shortcode('zippy_custom_filter', 'zippy_custom_filter');


function zippy_products_pagination()
{
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
                        <img src="<?php echo !empty($thumbnail_url) ? esc_url($thumbnail_url) : esc_url(wc_placeholder_img_src()) ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                    </div>
                    <div class="product-infos">
                        <h3 class="product-title"><?php echo get_the_title(); ?></h3>
                        <?php if ($product->get_price()) { ?>
                            <h3 class="product-price"><?php echo wc_price($price); ?></h3>
                        <?php } ?>
                    </div>
                    <div class="box-add-to-cart">
                        <?php
                        if ($product && $product->is_purchasable()) {
                        ?>
                            <a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
                                data-quantity="1"
                                class="button add_to_cart_button ajax_add_to_cart"
                                data-product_id="<?php echo $product->get_id(); ?>"
                                data-product_sku="<?php echo $product->get_sku(); ?>"
                                aria-label="<?php echo esc_attr($product->add_to_cart_description()); ?>"
                                rel="nofollow">

                                <?php
                                echo esc_html($product->add_to_cart_text());
                                ?>
                            </a>
                        <?php
                        } else {
                            return '<p>' . __('Product not available', 'your-textdomain') . '</p>';
                        }
                        ?>
                    </div>
                </a>
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

function get_services_by_slug($atts)
{

    $atts = shortcode_atts(array(
        'category' => '',
        'limit' => 12,
        'contain' => 0,
        'exclude-current' => 'false',
        'paged' => 1,
    ), $atts, 'get_services_by_slug');


    $category_slug = sanitize_text_field($atts['category']);
    $limit = intval($atts['limit']);
    $paged = intval($atts['paged']);
    $exclude_current_post = $atts['exclude-current'];

    if (empty($category_slug)) {
        return '<p>Missing Slug.</p>';
    }
    echo '<input id="ajax_url" type="hidden" value="' . admin_url('admin-ajax.php') . '" >';
    // Query products

    $args = array(
        'post_type' => 'services',
        'posts_per_page' => $limit,
        'paged' => $paged,
        'tax_query' => array(
            array(
                'taxonomy' => 'categories_services',
                'field' => 'slug',
                'terms' => $category_slug,
            ),
        ),
    );
    
    if ($exclude_current_post === 'true' && is_singular()) {
        $args['post__not_in'] = array(get_the_ID());
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>Empty.</p>';
    }

    ob_start();

    echo '<div class="zippy-products-container">';
    while ($query->have_posts()) {
        $query->the_post();
        $post = get_post();
        $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
        $treatment_time = get_field('treatment_time') ?? '';
        $icon_url = THEME_URL . '-child' . '/assets/icons/';
        ?>
        <div class="zippy-product-item">
            <a href="<?php echo get_permalink(); ?>">
                <div class="product-thumbnail">
                    <img class="thumbnail-image <?php if ($atts['contain'] != 0) echo 'img-contain' ?>" src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                    <?php if (!empty($treatment_time)) : ?>
                        <span class="treatment-time"> <img class="thumbnail-icon" src="<?php echo $icon_url . 'clock-stand-svgrepo-com.svg' ?>" alt=""> <?php echo $treatment_time ?></span>
                    <?php endif ?>
                </div>
                <div class="product-infos">
                    <h3 class="product-title"> <?php echo get_the_title() ?> </h3>
                    <?php if (!empty($post->post_excerpt)) : ?>
                        <p class="product-desc"> <?php echo esc_html($post->post_excerpt) ?> </p>
                    <?php endif ?>
                </div>
            </a>
        </div>
<?php
    }
    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
}
// [get_services_by_slug category='...' limit='...']
add_shortcode('get_services_by_slug', 'get_services_by_slug');
