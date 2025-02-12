<?php
/**
 * Plugin Name: WooCommerce Custom Product Fields
 * Description: Adds custom fields to WooCommerce product pages and captures customer input.
 * Version: 2.1
 * Author: Nabeel Hassan
 * Text Domain: woocommerce-custom-fields
 * Domain Path: /languages
 */

// Add a menu in the WordPress dashboard sidebar
add_action('admin_menu', 'custom_fields_admin_menu');
function custom_fields_admin_menu() {
    add_menu_page(
        'Custom Fields Settings',
        'Custom Fields',
        'manage_options',
        'custom-fields-settings',
        'custom_fields_settings_page',
        'dashicons-admin-generic',
        26
    );
}

// Settings page for category selection
function custom_fields_settings_page() {
    echo '<div class="wrap"><h1>Custom Fields Settings</h1>';
    echo '<p>Select product categories where custom fields should appear.</p>';
    
    $categories = get_terms('product_cat', ['hide_empty' => false]);
    $selected_category = get_option('custom_fields_enabled_category');
    
    echo '<form method="post">';
    wp_nonce_field('custom_fields_save_action', 'custom_fields_nonce');
    echo '<label for="custom_fields_category">Select Category:</label>';
    echo '<select name="custom_fields_category" id="custom_fields_category">';
    echo '<option value="">Select a Category</option>';
    foreach ($categories as $category) {
        $selected = ($selected_category == $category->slug) ? 'selected' : '';
        echo '<option value="' . esc_attr($category->slug) . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
    }
    echo '</select>';
    submit_button('Save Settings');
    echo '</form>';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['custom_fields_nonce']) && wp_verify_nonce($_POST['custom_fields_nonce'], 'custom_fields_save_action')) {
        if (!empty($_POST['custom_fields_category'])) {
            update_option('custom_fields_enabled_category', sanitize_text_field($_POST['custom_fields_category']));
        } else {
            delete_option('custom_fields_enabled_category');
        }
    }
}

// Display extra fields on the product page only if the category matches
add_action('woocommerce_before_add_to_cart_button', 'add_custom_fields_to_product_page');
function add_custom_fields_to_product_page() {
    global $post;
    $product_cats = wp_get_post_terms($post->ID, 'product_cat', ['fields' => 'slugs']);
    $enabled_category = get_option('custom_fields_enabled_category');
    
    if (!in_array($enabled_category, $product_cats)) return;
    
    echo '<div class="custom-field">
            <label for="team_name">Team Name</label>
            <input type="text" id="team_name" name="custom_team_name" />
          </div>';
    
    echo '<div class="custom-field">
            <label for="num_players">Select Number of Players</label>
            <select id="num_players" name="custom_num_players">
                <option value="">Select</option>';
    for ($i = 1; $i <= 30; $i++) {
        echo '<option value="' . $i . '">' . $i . '</option>';
    }
    echo '</select></div>';
    
    echo '<div id="players_info"></div>';
    
    // New Fields
    echo '<div class="custom-field">
            <label for="player_number_option">Player Number Option</label>
            <select id="player_number_option" name="custom_player_number_option">
                <option value="Both Sides">Both Sides</option>
                <option value="Only Back Number">Only Back Number</option>
                <option value="Only Front Number">Only Front Number</option>
            </select>
          </div>';
    
    echo '<div class="custom-field">
            <label for="brand_logo">Brand Logo</label>
            <input type="file" id="brand_logo" name="custom_brand_logo" accept="image/*" />
            <img id="brand_logo_preview" style="max-width:100px; display:none;" />
          </div>';
    
    echo '<div class="custom-field">
            <label for="sponsor_option">Sponsor Option</label>
            <select id="sponsor_option" name="custom_sponsor_option">
                <option value="Sponsor Text">Sponsor Text</option>
                <option value="Sponsor Image">Sponsor Image</option>
            </select>
          </div>';
    
    echo '<div class="custom-field">
            <label for="special_requirements">Special Requirements and Additional Notes</label>
            <textarea id="special_requirements" name="custom_special_requirements"></textarea>
          </div>';
    
    echo '<script>
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

// Save custom fields data in the cart
add_filter('woocommerce_add_cart_item_data', 'save_custom_fields_to_cart', 10, 2);
function save_custom_fields_to_cart($cart_item_data, $product_id) {
    foreach ($_POST as $key => $value) {
        if (!empty($value)) {
            $cart_item_data[$key] = sanitize_text_field($value);
        }
    }
    return $cart_item_data;
}

// Display custom fields in the cart
add_filter('woocommerce_get_item_data', 'display_custom_fields_in_cart', 10, 2);
function display_custom_fields_in_cart($item_data, $cart_item) {
    foreach ($cart_item as $key => $value) {
        if (!empty($value) && strpos($key, 'custom_') === 0) {
            $item_data[] = ['name' => ucfirst(str_replace('_', ' ', $key)), 'value' => $value];
        }
    }
    return $item_data;
}

// Add custom fields to order
add_action('woocommerce_checkout_create_order_line_item', 'add_custom_fields_to_order', 10, 4);
function add_custom_fields_to_order($item, $cart_item_key, $values, $order) {
    foreach ($values as $key => $value) {
        if (!empty($value) && strpos($key, 'custom_') === 0) {
            $item->add_meta_data(ucfirst(str_replace('_', ' ', $key)), $value);
        }
    }
}
