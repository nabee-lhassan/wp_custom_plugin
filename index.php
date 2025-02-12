<?php
/**
 * Plugin Name: WooCommerce Custom Product Fields
 * Description: Adds custom fields to WooCommerce product pages and captures customer input.
 * Version: 1.5
 * Author: Nabeel Hassan
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
    echo '<div class="wrap"><h1>Custom Fields Settings</h1>';
    echo '<p>Configure custom fields for WooCommerce products.</p>';
    
    $categories = get_terms('product_cat', ['hide_empty' => false]);
    if (!empty($categories)) {
        echo '<ul>';
        foreach ($categories as $category) {
            echo '<li>' . esc_html($category->name) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No categories found.</p>';
    }
    echo '</div>';
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
    if (isset($_POST['custom_fields_category_enable'])) {
        update_post_meta($post_id, 'custom_fields_category_enable', sanitize_text_field($_POST['custom_fields_category_enable']));
    }
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
