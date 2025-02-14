<?php
/**
 * Plugin Name: WooCommerce Custom Product Fields
 * Description: Adds custom fields to WooCommerce product pages and captures customer input.
 * Version: 2.3
 * Author: Nabeel Hassan
 * Text Domain: woocommerce-custom-fields
 */

// Display custom fields on product page (for specific category)
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

          <button type="button" id="custom_add_button" class="button alt">Custom Add to Cart</button>
          
          <script>
          document.addEventListener("DOMContentLoaded", function() {
              document.getElementById("brand_logo").addEventListener("change", function(event) {
                  var reader = new FileReader();
                  reader.onload = function(e) {
                      document.getElementById("brand_logo_preview").src = e.target.result;
                      document.getElementById("brand_logo_preview").style.display = "block";
                  };
                  reader.readAsDataURL(event.target.files[0]);
              });

              // Custom button trigger for add to cart
              document.getElementById("custom_add_button").addEventListener("click", function() {
                  document.querySelector("form.cart").submit();
              });
          });
          </script>';
}

// Save custom fields to cart
add_filter('woocommerce_add_cart_item_data', 'save_custom_fields_to_cart', 10, 2);
function save_custom_fields_to_cart($cart_item_data, $product_id) {
    if (!empty($_POST['custom_customer_name'])) {
        $cart_item_data['custom_customer_name'] = sanitize_text_field($_POST['custom_customer_name']);
    }
    if (!empty($_POST['custom_phone_number'])) {
        $cart_item_data['custom_phone_number'] = sanitize_text_field($_POST['custom_phone_number']);
    }
    if (!empty($_POST['custom_order_notes'])) {
        $cart_item_data['custom_order_notes'] = sanitize_textarea_field($_POST['custom_order_notes']);
    }
    if (!empty($_FILES['custom_brand_logo']['name'])) {
        $upload = wp_handle_upload($_FILES['custom_brand_logo'], ['test_form' => false]);

        if (!isset($upload['error']) && isset($upload['url'])) {
            $cart_item_data['custom_brand_logo'] = esc_url($upload['url']);
            WC()->session->set('custom_brand_logo', esc_url($upload['url'])); 
        } else {
            wc_add_notice(__('Brand Logo upload failed. Please try again.', 'woocommerce'), 'error');
        }
    }
    return $cart_item_data;
}

// Display custom fields in the cart
add_filter('woocommerce_get_item_data', 'display_custom_fields_in_cart', 10, 2);
function display_custom_fields_in_cart($item_data, $cart_item) {
    if (!empty($cart_item['custom_customer_name'])) {
        $item_data[] = ['name' => 'Customer Name', 'value' => esc_html($cart_item['custom_customer_name'])];
    }
    if (!empty($cart_item['custom_phone_number'])) {
        $item_data[] = ['name' => 'Phone Number', 'value' => esc_html($cart_item['custom_phone_number'])];
    }
    if (!empty($cart_item['custom_order_notes'])) {
        $item_data[] = ['name' => 'Order Notes', 'value' => esc_html($cart_item['custom_order_notes'])];
    }
    if (!empty($cart_item['custom_brand_logo'])) {
        $item_data[] = [
            'name'  => 'Brand Logo',
            'value' => '<img src="' . esc_url($cart_item['custom_brand_logo']) . '" style="max-width:50px;" />'
        ];
    }
    return $item_data;
}

// Save custom fields in order meta
add_action('woocommerce_checkout_create_order_line_item', 'add_custom_fields_to_order', 10, 4);
function add_custom_fields_to_order($item, $cart_item_key, $values, $order) {
    if (!empty($values['custom_customer_name'])) {
        $item->add_meta_data('Customer Name', esc_html($values['custom_customer_name']));
    }
    if (!empty($values['custom_phone_number'])) {
        $item->add_meta_data('Phone Number', esc_html($values['custom_phone_number']));
    }
    if (!empty($values['custom_order_notes'])) {
        $item->add_meta_data('Order Notes', esc_html($values['custom_order_notes']));
    }
    if (!empty($values['custom_brand_logo'])) {
        $item->add_meta_data('Brand Logo', '<img src="' . esc_url($values['custom_brand_logo']) . '" style="max-width:50px;" />');
    }
}
