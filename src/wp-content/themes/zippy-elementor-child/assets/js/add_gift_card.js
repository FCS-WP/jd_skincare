$(document).ready(function ($) {
  if ($(".elementor-product-pw-gift-card form").length > 0) {
    const $form = $("form.cart");
    const $addToCart = $form.find("button.single_add_to_cart_button");
    const $applyAmount = $form.find("#zippy_new_amount_button");
    const $newAmount = $form.find("#zippy_new_amount");
    const $result = $("#zippy_new_amount_result");
    const $label = $("#gift-card-thumb .wp-caption-text");
    const $variation = $("#gift-card-amount");

    // Initially disable buttons
    $applyAmount.prop("disabled", true);
    $addToCart.prop("disabled", true);

    // Enable Apply button only if value > 0
    $newAmount.on("input", function () {
      const amount = parseFloat($(this).val());
      if (amount >= 0) $label.text(`$${amount}`);
      $applyAmount.prop("disabled", !(amount > 0));
    });

    $applyAmount.on("click", function (e) {
      e.preventDefault();

      const amount = parseFloat($newAmount.val());
      if (isNaN(amount) || amount <= 0) {
        showMessage("Please enter a valid amount.", "error");
        return;
      }

      if (amount > 5000) {
        showMessage("Amount must not exceed 5000.", "error");
        return;
      }

      // Disable Apply button and show loading text
      $applyAmount.prop("disabled", true).text("Applying...");

      $.post("/wp-admin/admin-ajax.php", {
        action: "zippy_add_gift_card_amount",
        product_id: $form.find('input[name="product_id"]').val(),
        amount: amount,
        security: zippy.nonces.zippy_add_amount,
      })
        .done(function (result) {
          if (result.success) {
            const prettyAmount = result.data.amount;
            $result
              .text(`✅ Amount applied: ${prettyAmount}`)
              .css("color", "green");

            $form
              .find('input[name="variation_id"]')
              .val(result.data.variation_id);
            $variation.val(prettyAmount);
            $addToCart.prop("disabled", false);
          } else {
            showMessage("Failed to apply gift card amount.", "error");
          }
        })
        .fail(function (_, __, errorThrown) {
          console.error("AJAX error:", errorThrown);
          showMessage("An error occurred while applying the amount.", "error");
        })
        .always(function () {
          $applyAmount.prop("disabled", false).text("Apply Amount");
        });
    });

    function showMessage(message, type = "info") {
      $result
        .text((type === "error" ? "⚠️ " : "") + message)
        .css("color", type === "error" ? "red" : "black");
    }
  }
});
