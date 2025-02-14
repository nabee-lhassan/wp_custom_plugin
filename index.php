<?php
/**
 * Plugin Name: WooCommerce Custom Product Fields
 * Description: Adds custom fields to WooCommerce product pages and captures customer input.
 * Version: 2.3
 * Author: Nabeel Hassan
 * Text Domain: woocommerce-custom-fields
 */

// Display custom fields on product page
add_action('woocommerce_before_add_to_cart_button', 'add_custom_fields_to_product_page');
function add_custom_fields_to_product_page() {
    global $post;
    $product_cats = wp_get_post_terms($post->ID, 'product_cat', ['fields' => 'slugs']);
    $enabled_category = get_option('custom_fields_enabled_category');

    if (!in_array($enabled_category, $product_cats)) return;

    echo '<div class="custom-fields">
            <label for="customer_name">Customer Name:</label>
            <input type="text" id="customer_name" name="custom_customer_name" required />

            <label for="phone_number">Phone Number:</label>
            <input type="tel" id="phone_number" name="custom_phone_number" required />

            <label for="order_notes">Order Notes:</label>
            <textarea id="order_notes" name="custom_order_notes"></textarea>

            <label for="brand_logo">Brand Logo:</label>
            <input type="file" id="brand_logo" name="custom_brand_logo" accept="image/*" />
            <img id="brand_logo_preview" style="max-width:100px; display:none;" />
          </div>

          <!-- Elementor Popup Button -->
          <button type="button" id="elementor_popup_button" class="button alt" onclick="openElementorPopup()">Open Popup</button>

          <script>
          function openElementorPopup() {
              // Elementor Popup Trigger
              jQuery.magnificPopup.open({
                  items: {
                      src: "#elementor-popup-id",
                      type: "inline"
                  }
              });
          }

          document.addEventListener("DOMContentLoaded", function() {
              document.getElementById("brand_logo").addEventListener("change", function(event) {
                  var reader = new FileReader();
                  reader.onload = function(e) {
                      document.getElementById("brand_logo_preview").src = e.target.result;
                      document.getElementById("brand_logo_preview").style.display = "block";
                  };
                  reader.readAsDataURL(event.target.files[0]);
              });
          });
          </script>';
}

// Save custom fields in cart
add_filter('woocommerce_add_cart_item_data', 'save_custom_fields_to_cart', 10, 2);
function save_custom_fields_to_cart($cart_item_data, $product_id) {
    if (isset($_POST['custom_customer_name'])) {
        $cart_item_data['custom_customer_name'] = sanitize_text_field($_POST['custom_customer_name']);
    }
    if (isset($_POST['custom_phone_number'])) {
        $cart_item_data['custom_phone_number'] = sanitize_text_field($_POST['custom_phone_number']);
    }
    if (isset($_POST['custom_order_notes'])) {
        $cart_item_data['custom_order_notes'] = sanitize_textarea_field($_POST['custom_order_notes']);
    }
    if (!empty($_FILES['custom_brand_logo']['name'])) {
        $upload = wp_upload_bits($_FILES['custom_brand_logo']['name'], null, file_get_contents($_FILES['custom_brand_logo']['tmp_name']));
        if (!$upload['error']) {
            $cart_item_data['custom_brand_logo'] = $upload['url'];
        }
    }
    return $cart_item_data;
}

// Display custom fields in cart & checkout
add_filter('woocommerce_get_item_data', 'display_custom_fields_in_cart', 10, 2);
function display_custom_fields_in_cart($item_data, $cart_item) {
    if (isset($cart_item['custom_customer_name'])) {
        $item_data[] = [
            'name' => 'Customer Name',
            'value' => sanitize_text_field($cart_item['custom_customer_name'])
        ];
    }
    if (isset($cart_item['custom_phone_number'])) {
        $item_data[] = [
            'name' => 'Phone Number',
            'value' => sanitize_text_field($cart_item['custom_phone_number'])
        ];
    }
    if (isset($cart_item['custom_order_notes'])) {
        $item_data[] = [
            'name' => 'Order Notes',
            'value' => sanitize_textarea_field($cart_item['custom_order_notes'])
        ];
    }
    if (isset($cart_item['custom_brand_logo'])) {
        $item_data[] = [
            'name' => 'Brand Logo',
            'value' => '<img src="'.esc_url($cart_item['custom_brand_logo']).'" style="max-width:100px;" />'
        ];
    }
    return $item_data;
}

// Save custom fields in order meta
add_action('woocommerce_checkout_create_order_line_item', 'save_custom_fields_to_order_meta', 10, 4);
function save_custom_fields_to_order_meta($item, $cart_item_key, $values, $order) {
    if (isset($values['custom_customer_name'])) {
        $item->add_meta_data('Customer Name', $values['custom_customer_name'], true);
    }
    if (isset($values['custom_phone_number'])) {
        $item->add_meta_data('Phone Number', $values['custom_phone_number'], true);
    }
    if (isset($values['custom_order_notes'])) {
        $item->add_meta_data('Order Notes', $values['custom_order_notes'], true);
    }
    if (isset($values['custom_brand_logo'])) {
        $item->add_meta_data('Brand Logo', '<img src="'.esc_url($values['custom_brand_logo']).'" style="max-width:100px;" />', true);
    }
}
?>
