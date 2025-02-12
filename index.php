<?php
/**
 * Plugin Name: Custom Player Fields
 * Description: Add custom fields for players in selected categories.
 * Version: 1.1
 * Author: Nabeel Hassan
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add menu in dashboard
function custom_player_fields_menu() {
    add_menu_page('Custom Player Fields', 'Custom Player Fields', 'manage_options', 'custom-player-fields', 'custom_player_fields_page');
}
add_action('admin_menu', 'custom_player_fields_menu');

// Register settings
function custom_player_fields_register_settings() {
    register_setting('custom_player_fields_group', 'custom_player_selected_category');
}
add_action('admin_init', 'custom_player_fields_register_settings');

// Render settings page
function custom_player_fields_page() {
    ?>
    <div class="wrap">
        <h2>Custom Player Fields Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('custom_player_fields_group');
            do_settings_sections('custom_player_fields_group');
            $selected_category = get_option('custom_player_selected_category', '');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="custom_player_selected_category">Select Category</label></th>
                    <td>
                        <select name="custom_player_selected_category" id="custom_player_selected_category">
                            <?php
                            $categories = get_categories(array('hide_empty' => false));
                            foreach ($categories as $category) {
                                echo '<option value="' . $category->term_id . '" ' . selected($selected_category, $category->term_id, false) . '>' . $category->name . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Add custom fields to selected category
function add_custom_player_fields() {
    global $post;
    $selected_category = get_option('custom_player_selected_category', '');
    if (!has_term($selected_category, 'category', $post)) {
        return;
    }
    ?>
    <div class="custom-player-fields">
        <h3>Player Information</h3>
        <label for="player_number_option">Player Number Option:</label>
        <select name="player_number_option" id="player_number_option">
            <option value="both_sides">Both Sides</option>
            <option value="only_back">Only Back Number</option>
            <option value="only_front">Only Front Number</option>
        </select>
        
        <label for="brand_logo">Brand Logo:</label>
        <input type="file" name="brand_logo" id="brand_logo">
        <img id="brand_logo_preview" style="max-width: 200px; display: none;" />

        <label for="sponsor_option">Sponsor Option:</label>
        <select name="sponsor_option" id="sponsor_option">
            <option value="sponsor_text">Sponsor Text</option>
            <option value="sponsor_image">Sponsor Image</option>
        </select>
        
        <label for="special_requirements">Special Requirements & Additional Notes:</label>
        <textarea name="special_requirements" id="special_requirements"></textarea>
    </div>
    <script>
        document.getElementById('brand_logo').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('brand_logo_preview').src = e.target.result;
                    document.getElementById('brand_logo_preview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
    <?php
}
add_action('woocommerce_before_add_to_cart_button', 'add_custom_player_fields');

// Save custom fields data to order meta
function save_custom_player_fields_to_order($cart_item_data, $product_id) {
    if (isset($_POST['player_number_option'])) {
        $cart_item_data['player_number_option'] = sanitize_text_field($_POST['player_number_option']);
    }
    if (!empty($_FILES['brand_logo']['name'])) {
        $upload = wp_upload_bits($_FILES['brand_logo']['name'], null, file_get_contents($_FILES['brand_logo']['tmp_name']));
        if (!$upload['error']) {
            $cart_item_data['brand_logo'] = $upload['url'];
        }
    }
    if (isset($_POST['sponsor_option'])) {
        $cart_item_data['sponsor_option'] = sanitize_text_field($_POST['sponsor_option']);
    }
    if (isset($_POST['special_requirements'])) {
        $cart_item_data['special_requirements'] = sanitize_textarea_field($_POST['special_requirements']);
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'save_custom_player_fields_to_order', 10, 2);

// Display custom fields data in cart & checkout
function display_custom_fields_in_cart($item_data, $cart_item) {
    if (isset($cart_item['player_number_option'])) {
        $item_data[] = array('name' => 'Player Number Option', 'value' => $cart_item['player_number_option']);
    }
    if (isset($cart_item['brand_logo'])) {
        $item_data[] = array('name' => 'Brand Logo', 'value' => '<img src="' . esc_url($cart_item['brand_logo']) . '" style="max-width: 50px;" />');
    }
    if (isset($cart_item['sponsor_option'])) {
        $item_data[] = array('name' => 'Sponsor Option', 'value' => $cart_item['sponsor_option']);
    }
    if (isset($cart_item['special_requirements'])) {
        $item_data[] = array('name' => 'Special Requirements', 'value' => $cart_item['special_requirements']);
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_custom_fields_in_cart', 10, 2);

// Save custom fields data to order meta
function save_custom_fields_to_order_meta($item, $cart_item_key, $values, $order) {
    if (isset($values['player_number_option'])) {
        $item->add_meta_data('Player Number Option', $values['player_number_option']);
    }
    if (isset($values['brand_logo'])) {
        $item->add_meta_data('Brand Logo', '<img src="' . esc_url($values['brand_logo']) . '" style="max-width: 50px;" />', true);
    }
    if (isset($values['sponsor_option'])) {
        $item->add_meta_data('Sponsor Option', $values['sponsor_option']);
    }
    if (isset($values['special_requirements'])) {
        $item->add_meta_data('Special Requirements', $values['special_requirements']);
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'save_custom_fields_to_order_meta', 10, 4);
?>
