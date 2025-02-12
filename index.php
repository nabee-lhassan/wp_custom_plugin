<?php
/**
 * Plugin Name: WooCommerce Custom Product Fields
 * Description: Adds custom fields to WooCommerce product pages and captures customer input.
 * Version: 1.7
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
    echo '<form method="post">';
    echo '<ul>';
    foreach ($categories as $category) {
        $checked = get_option('custom_fields_enabled_' . $category->slug) ? 'checked' : '';
        echo '<li><input type="checkbox" name="custom_fields_enabled[' . esc_attr($category->slug) . ']" ' . $checked . '> ' . esc_html($category->name) . '</li>';
    }
    echo '</ul>';
    submit_button('Save Settings');
    echo '</form>';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['custom_fields_enabled'])) {
        foreach ($categories as $category) {
            $option_name = 'custom_fields_enabled_' . $category->slug;
            update_option($option_name, isset($_POST['custom_fields_enabled'][$category->slug]));
        }
    }
    echo '</div>';
}

// Display extra fields on the product page only if the category matches
add_action('woocommerce_before_add_to_cart_button', 'add_custom_fields_to_product_page');
function add_custom_fields_to_product_page() {
    global $post;
    $product_cats = wp_get_post_terms($post->ID, 'product_cat', ['fields' => 'slugs']);
    $show_fields = false;
    
    foreach ($product_cats as $cat) {
        if (get_option('custom_fields_enabled_' . $cat)) {
            $show_fields = true;
            break;
        }
    }
    
    if (!$show_fields) return;
    
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
    
    echo '<script>
    document.getElementById("num_players").addEventListener("change", function() {
        var count = this.value;
        var container = document.getElementById("players_info");
        container.innerHTML = "";
        for (var i = 1; i <= count; i++) {
            container.innerHTML += `<div class="player-field">
                <label>Player ` + i + ` Name</label>
                <input type="text" name="custom_player_` + i + `_name" />
                <label>Number</label>
                <input type="text" name="custom_player_` + i + `_number" />
                <label>Size</label>
                <input type="text" name="custom_player_` + i + `_size" />
            </div>`;
        }
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
