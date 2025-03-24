<?php
/**
 * Plugin Name: WooCommerce Custom Product Fields
 * Plugin URI: https://ayshtech.com/
 * Description: Adds custom fields to WooCommerce product pages and captures customer input.
 * Version: 2.6
 * Author: Nabeel Hassan
 * Author URI: https://github.com/nabee-lhassan
 * Text Domain: woocommerce-custom-fields
 * Domain Path: /languages
 */

// Add Admin Menu
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

// Settings Page
function custom_fields_settings_page() {
    echo '<div class="wrap"><h1>Custom Fields Settings</h1>';
    echo '<p>Select product categories where custom fields should appear.</p>';
    $categories = get_terms('product_cat', ['hide_empty' => false]);
    $selected_category = get_option('custom_fields_enabled_category');
    echo '<form method="post">';
    wp_nonce_field('custom_fields_save_action', 'custom_fields_nonce');
    echo '<label>Select Category:</label><select name="custom_fields_category">';
    echo '<option value="">Select a Category</option>';
    foreach ($categories as $category) {
        $selected = ($selected_category == $category->slug) ? 'selected' : '';
        echo '<option value="' . esc_attr($category->slug) . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
    }
    echo '</select>';
    submit_button('Save Settings');
    echo '</form></div>';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['custom_fields_nonce']) && wp_verify_nonce($_POST['custom_fields_nonce'], 'custom_fields_save_action')) {
        if (!empty($_POST['custom_fields_category'])) {
            update_option('custom_fields_enabled_category', sanitize_text_field($_POST['custom_fields_category']));
        } else {
            delete_option('custom_fields_enabled_category');
        }
    }
}

// Add Size Chart and Buttons Before Variations
add_action('woocommerce_before_variations_form', 'add_size_chart_and_buttons');
function add_size_chart_and_buttons() {
    $popup_id = 5642; // Elementor Popup ID
    echo '<div style="margin-bottom: 20px;">
            <button onclick="openSizeChartPopup()" style="background:black; color:white; padding:10px 22px;">Size Chart</button>
          </div>
          <div class="custom-field-buttons">
            <button type="button" id="standard_btn" class="custom-button active">Standard</button>
            <button type="button" id="bespoke_btn" class="custom-button">Bespoke</button>
          </div>
          <script>
            function openSizeChartPopup() {
                if (typeof elementorProFrontend !== "undefined") {
                    elementorProFrontend.modules.popup.showPopup({ id: ' . $popup_id . ' });
                }
            }
          </script>';
}

// Show Custom Fields
add_action('woocommerce_before_add_to_cart_button', 'add_custom_fields_to_product_page');
function add_custom_fields_to_product_page() {
    global $post;
    $product_cats = wp_get_post_terms($post->ID, 'product_cat', ['fields' => 'slugs']);
    $enabled_category = get_option('custom_fields_enabled_category');
    if (!in_array($enabled_category, $product_cats)) return;

    echo '<div id="custom_fields_wrapper" >';

    echo '<div class="custom-field">
            <label style="font-weight:bold;">Front - Team Name</label>
            <input type="text" name="custom_team_name" style="padding:8px; border:1px solid #ccc; border-radius:5px;" />
          </div>';

    echo '<div class="custom-field">
            <label style="font-weight:bold;">Player Number</label>
            <input type="text" name="custom_front_shorts_number" style="padding:8px; border:1px solid #ccc; border-radius:5px;" />
          </div>';

    echo '<div class="custom-field">
            <label style="font-weight:bold;">Back - Your Name</label>
            <input type="text" name="custom_back_your_name" style="padding:8px; border:1px solid #ccc; border-radius:5px;" />
          </div>';

    echo '<div class="custom-field">
            <label style="font-weight:bold;">Logo Location</label>
            <select name="custom_brand_logo_position" style="padding:8px; border:1px solid #ccc; border-radius:5px;">
                <option value="LeftSleeve">Left Sleeve</option>
                <option value="RightSleeve">Right Sleeve</option>
                <option value="LeftChest">Left Chest</option>
                <option value="RightChest">Right Chest</option>
                <option value="both">Both</option>
                <option value="none">None</option>
            </select>
          </div>';

    echo '<div class="custom-field">
            <label style="font-weight:bold;">Brand Logo</label>
            <input type="file" name="custom_brand_logo" style="padding:8px; border:1px solid #ccc; border-radius:5px;" />
          </div>';

    echo '</div>';

    echo '<div id="bespoke_fields" style="display:none;">
            <p>There will be a certain additional cost for Bespoke printing</p>
            <h3>Step 2: Please write your requirements</h3>
            <p>Bespoke service including:</p>
            <ol>
                <li>Change color, pattern or printing position</li>
                <li>Add sponsor or any custom text</li>
                <li>Replicate your design idea and make your dream jersey.</li>
            </ol>
            <a style="width:100%;text-align:center; padding: 10px 22px; background-color: black; color: white;" href="https://ayshtech.com/logozfactory/get-a-quote/"> Get a Quote </a>
          </div>';

    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        let standardBtn = document.getElementById("standard_btn");
        let bespokeBtn = document.getElementById("bespoke_btn");

        standardBtn.addEventListener("click", function() {
            standardBtn.classList.add("active");
            bespokeBtn.classList.remove("active");
            if(document.querySelector("#shop-now")){document.querySelector("#shop-now").style.display = "flex";}
            document.querySelector(".custom_colorpicker_wrapper").style.display = "flex";
            document.querySelector(".variations").style.display = "flex";
            document.getElementById("custom_fields_wrapper").style.display = "flex";
            document.getElementById("bespoke_fields").style.display = "none";
        });

        bespokeBtn.addEventListener("click", function() {
            bespokeBtn.classList.add("active");
            standardBtn.classList.remove("active");
            if(document.querySelector("#shop-now")){document.querySelector("#shop-now").style.display = "none";}
            document.querySelector(".custom_colorpicker_wrapper").style.display = "none";
            document.querySelector(".variations").style.display = "none";
            document.getElementById("custom_fields_wrapper").style.display = "none";
            document.getElementById("bespoke_fields").style.display = "block";
        });
    });
    </script>';
}

// Save Custom Field Data to Cart
add_filter('woocommerce_add_cart_item_data', 'save_custom_fields_data', 10, 2);
function save_custom_fields_data($cart_item_data, $product_id) {
    if (isset($_POST['custom_team_name'])) {
        $cart_item_data['custom_team_name'] = sanitize_text_field($_POST['custom_team_name']);
    }
    if (isset($_POST['custom_front_shorts_number'])) {
        $cart_item_data['custom_front_shorts_number'] = sanitize_text_field($_POST['custom_front_shorts_number']);
    }
    if (isset($_POST['custom_back_your_name'])) {
        $cart_item_data['custom_back_your_name'] = sanitize_text_field($_POST['custom_back_your_name']);
    }
    if (isset($_POST['custom_brand_logo_position'])) {
        $cart_item_data['custom_brand_logo_position'] = sanitize_text_field($_POST['custom_brand_logo_position']);
    }

    // Handle image upload
    if (isset($_FILES['custom_brand_logo']) && !empty($_FILES['custom_brand_logo']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $uploadedfile = $_FILES['custom_brand_logo'];
        $upload_overrides = ['test_form' => false];
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        if ($movefile && !isset($movefile['error'])) {
            $cart_item_data['custom_brand_logo'] = $movefile['url'];
        }
    }   
    return $cart_item_data;
}

// Display Custom Data in Cart
add_filter('woocommerce_get_item_data', 'display_custom_fields_cart', 10, 2);
function display_custom_fields_cart($item_data, $cart_item) {
    if (isset($cart_item['custom_team_name'])) {
        $item_data[] = ['name' => 'Team Name', 'value' => $cart_item['custom_team_name']];
    }
    if (isset($cart_item['custom_front_shorts_number'])) {
        $item_data[] = ['name' => 'Player Number', 'value' => $cart_item['custom_front_shorts_number']];
    }
    if (isset($cart_item['custom_back_your_name'])) {
        $item_data[] = ['name' => 'Back Name', 'value' => $cart_item['custom_back_your_name']];
    }
    if (isset($cart_item['custom_brand_logo_position'])) {
        $item_data[] = ['name' => 'Logo Location', 'value' => $cart_item['custom_brand_logo_position']];
    }
    if (isset($cart_item['custom_brand_logo'])) {
        $item_data[] = ['name' => 'Brand Logo', 'value' => '<img src="' . esc_url($cart_item['custom_brand_logo']) . '" style="max-width:100px;">'];
    }
    return $item_data;
}

// Pass custom data to order meta
add_action('woocommerce_checkout_create_order_line_item', 'add_custom_data_to_order_items', 10, 4);
function add_custom_data_to_order_items($item, $cart_item_key, $values, $order) {
    if (isset($values['custom_team_name'])) {
        $item->add_meta_data('Team Name', $values['custom_team_name']);
    }
    if (isset($values['custom_front_shorts_number'])) {
        $item->add_meta_data('Player Number', $values['custom_front_shorts_number']);
    }
    if (isset($values['custom_back_your_name'])) {
        $item->add_meta_data('Back Name', $values['custom_back_your_name']);
    }
    if (isset($values['custom_brand_logo_position'])) {
        $item->add_meta_data('Logo Location', $values['custom_brand_logo_position']);
    }
    if (isset($values['custom_brand_logo'])) {
        $item->add_meta_data('Brand Logo', $values['custom_brand_logo']);
    }
}
?>
