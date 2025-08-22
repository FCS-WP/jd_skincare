<?php

function enqueue_wc_cart_fragments()
{
  wp_enqueue_script('wc-cart-fragments');
}
add_action('wp_enqueue_scripts', 'enqueue_wc_cart_fragments');

add_action('woocommerce_before_add_to_cart_quantity', 'add_price_for_product_gift', 1, 10);

function add_price_for_product_gift()
{
  global $product;
  $type = $product->get_type();

  if ($type !== 'pw-gift-card') return;
?>
  <div class="zippy-custom-price" style="display:flex;width:100%;align-items: center;">
    <div class=" pwgc-field-container" style="width:250px;">
      <label for="zippy_new_amount" class="pwgc-label">Amount</label>
      <input type="number" min="1" max="999" name="zippy_new_amount" id="zippy_new_amount" required />
    </div>
    <div style="margin-left:20px">
      <button id="zippy_new_amount_button" disabled type="button" class="new_amount_button">Apply</button>
    </div>
    <div style="margin-left:20px">
      <div id="zippy_new_amount_result" class="new_amount_button"></div>
    </div>
  </div>



<?php
}


add_action('wp_ajax_zippy_add_gift_card_amount', 'zippy_add_gift_card_amount');
add_action('wp_ajax_nopriv_zippy_add_gift_card_amount', 'zippy_add_gift_card_amount');

function zippy_add_gift_card_amount()
{
  global $pw_gift_cards;

  check_ajax_referer('pw-gift-cards-add-gift-card-amount', 'security');

  $pw_gift_cards->set_current_currency_to_default();


  $product_id = absint($_POST['product_id']);
  $new_amount = wc_clean($_POST['amount']);
  $new_amount = $pw_gift_cards->sanitize_amount($new_amount);

  if ($product = new WC_Product_PW_Gift_Card($product_id)) {
    $result = $product->add_amount($new_amount);

    if (is_numeric($result)) {
      wp_send_json_success(array('amount' => $pw_gift_cards->pretty_price($new_amount), 'variation_id' => $result));
    } else {

      $variations = array_map('wc_get_product', $product->get_children());

      // Check for existing amount.
      foreach ($variations as $variation) {
        $variation_attributes = $variation->get_attributes();

        if (isset($variation_attributes[PWGC_DENOMINATION_ATTRIBUTE_SLUG])) {
          $variation_option = $variation_attributes[PWGC_DENOMINATION_ATTRIBUTE_SLUG];

          if ($pw_gift_cards->equal_prices($variation_option, $new_amount)) {
            $variation_id = $variation->get_id();
            wp_send_json_success(array('amount' => $pw_gift_cards->pretty_price($new_amount), 'variation_id' => $variation_id));
          }
        }
      }
      // wp_send_json_error(array('message' => $result));
    }
  } else {
    // translators: %s is the product id.
    wp_send_json_error(array('message' => sprintf(__('Could not locate product id %s', 'pw-woocommerce-gift-cards'), $product_id)));
  }
}



// Shortcode: [giftcard_validity]
function my_giftcard_validity_shortcode() {
    $start_date = date_i18n( get_option('date_format'), current_time('timestamp') );
    $end_date   = date_i18n( get_option('date_format'), strtotime('+1 year', current_time('timestamp')) );

    return sprintf(
        '<span class="giftcard-validity">
            Valid from <span class="valid-date">%s</span> to <span class="valid-date">%s</span>
        </span>',
        esc_html($start_date),
        esc_html($end_date)
    );
}
add_shortcode( 'giftcard_validity', 'my_giftcard_validity_shortcode' );


