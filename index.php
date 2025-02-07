<?php
/**
 * Plugin Name: WooCommerce Custom Product Fields
 * Description: Adds custom fields to WooCommerce product pages and captures customer input.
 * Version: 1.4
 * Author: Nabeel Hassa 
 * Text Domain: woocommerce-custom-fields
 * Domain Path: /languages
 */

// Add a menu in the WordPress dashboard
add_action('admin_menu', 'custom_fields_admin_menu');
function custom_fields_admin_menu() {
    add_menu_page(
        'Custom Fields',
        'Custom Fields',
        'manage_options',
        'custom-fields-settings',
        'custom_fields_settings_page',
        'dashicons-admin-generic',
        25
    );
}

function custom_fields_settings_page() {
    echo '<div class="wrap"><h1>Custom Fields Settings</h1></div>';
}

// Add a category selection field in product settings
add_action('woocommerce_product_options_general_product_data', 'add_category_selection_field');
function add_category_selection_field() {
    woocommerce_wp_select([
        'id' => 'custom_fields_category_enable',
        'label' => __('Enable Custom Fields for Category', 'woocommerce-custom-fields'),
        'options' => get_wc_categories()
    ]);
}

// Save the selected category
add_action('woocommerce_process_product_meta', 'save_category_selection_field');
function save_category_selection_field($post_id) {
    $category = isset($_POST['custom_fields_category_enable']) ? sanitize_text_field($_POST['custom_fields_category_enable']) : '';
    update_post_meta($post_id, 'custom_fields_category_enable', $category);
}

// Function to fetch WooCommerce categories
function get_wc_categories() {
    $categories = get_terms('product_cat', ['hide_empty' => false]);
    $options = ['' => __('Select Category', 'woocommerce-custom-fields')];
    foreach ($categories as $category) {
        $options[$category->slug] = $category->name;
    }
    return $options;
}

// Display extra fields on the product page only if the category matches
add_action('woocommerce_before_add_to_cart_button', 'add_custom_fields_to_product_page');
function add_custom_fields_to_product_page() {
    global $post;
    $enabled_category = get_post_meta($post->ID, 'custom_fields_category_enable', true);
    $product_cats = wp_get_post_terms($post->ID, 'product_cat', ['fields' => 'slugs']);
    
    if (!$enabled_category || !in_array($enabled_category, $product_cats)) {
        return;
    }
    
    echo '<div class="custom-field">
            <label for="custom_message">Custom Message</label>
            <input type="text" id="custom_message" name="custom_message" />
          </div>';
    
    echo '<div class="custom-field">
            <label for="team_name">Team Name</label>
            <input type="text" id="team_name" name="team_name" />
          </div>';
    
    echo '<div class="custom-field">
            <label for="player_count">Number of Players</label>
            <select id="player_count" name="player_count" onchange="generatePlayerFields()">
                <option value="">Select</option>';
    for ($i = 1; $i <= 30; $i++) {
        echo '<option value="'.$i.'">'.$i.'</option>';
    }
    echo '</select></div>';
    
    echo '<fieldset class="custom-field" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
            <legend><strong>Player Information</strong></legend>
            <div id="player_fields"></div>
          </fieldset>';
    
    echo '<script>
            function generatePlayerFields() {
                var count = document.getElementById("player_count").value;
                var container = document.getElementById("player_fields");
                container.innerHTML = "";
                for (var i = 1; i <= count; i++) {
                    container.innerHTML += `<div class="player-group" style="display:flex; gap:10px; margin-bottom:5px;">
                        <label>Player ${i} Size</label>
                        <select name="player_size_${i}">
                            <option value="small">Small</option>
                            <option value="medium">Medium</option>
                            <option value="large">Large</option>
                            <option value="extra_large">Extra Large</option>
                        </select>
                        <label>Player ${i} Name</label>
                        <input type="text" name="player_name_${i}" />
                        <label>Player ${i} Number</label>
                        <input type="text" name="player_number_${i}" />
                    </div>`;
                }
            }
          </script>';
}

// Add custom field values to the cart
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
    $excluded_keys = ['variation_id', 'key', 'data_hash', 'line_tax_data', 'line_subtotal', 'line_total', 'data'];
    foreach ($cart_item as $key => $value) {
        if (!in_array($key, $excluded_keys) && !empty($value)) {
            $item_data[] = [
                'name' => ucfirst(str_replace('_', ' ', $key)),
                'value' => is_array($value) ? implode(', ', $value) : $value
            ];
        }
    }
    return $item_data;
}

// Add custom fields to order
add_action('woocommerce_checkout_create_order_line_item', 'add_custom_fields_to_order', 10, 4);
function add_custom_fields_to_order($item, $cart_item_key, $values, $order) {
    $excluded_keys = ['variation_id', 'key', 'data_hash', 'line_tax_data', 'line_subtotal', 'line_total', 'data'];
    foreach ($values as $key => $value) {
        if (!in_array($key, $excluded_keys) && !empty($value)) {
            $item->add_meta_data(ucfirst(str_replace('_', ' ', $key)), $value);
        }
    }
}
