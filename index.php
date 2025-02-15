<?php
/**
 * Plugin Name: WooCommerce Custom Product Fields
 * Description: Adds custom fields to WooCommerce product pages and captures customer input.
 * Version: 2.3
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

// Display Size Chart button before variations
add_action('woocommerce_before_variations_form', 'add_size_chart_button_before_variants');
function add_size_chart_button_before_variants() {
    $popup_id = 5642; // Replace this with actual Elementor popup ID

    echo '<div class="size-chart-button">
            <div id="size_chart_button" style="background-color: black; color: white; padding: 10px 22px; border: none; cursor: pointer; width: fit-content" onclick="openSizeChartPopup()">Size Chart</div>
          </div>
          
          <script>
          function openSizeChartPopup() {
              if (typeof elementorProFrontend !== "undefined") {
                  elementorProFrontend.modules.popup.showPopup({ id: ' . $popup_id . ' });
              }
          }
          </script>';
}

// Display buttons and custom fields on the product page
add_action('woocommerce_before_add_to_cart_button', 'add_custom_fields_to_product_page');
function add_custom_fields_to_product_page() {
    global $post;
    $product_cats = wp_get_post_terms($post->ID, 'product_cat', ['fields' => 'slugs']);
    $enabled_category = get_option('custom_fields_enabled_category');
    
    if (!in_array($enabled_category, $product_cats)) return;

    echo '<div class="custom-field-buttons">
            <button type="button" id="standard_btn" class="custom-button active">Standard</button>
            <button type="button" id="bespoke_btn" class="custom-button">Bespoke</button>
          </div>';

    echo '<div id="custom_fields_wrapper">
            <div class="custom-field">
                <label for="team_name">Front - Team Name:</label>
                <input type="text" id="team_name" name="custom_team_name" />
            </div>
            
            <div class="custom-field">
                <label for="front_shorts_number">Front - Number (Shorts Number):</label>
                <input type="text" id="front_shorts_number" name="custom_front_shorts_number" />
            </div>
            
            <div class="custom-field">
                <label for="back_your_name">Back - Your Name:</label>
                <input type="text" id="back_your_name" name="custom_back_your_name" />
            </div>
            
            <div class="custom-field">
                <label for="back_number">Back - Number:</label>
                <input type="text" id="back_number" name="custom_back_number" />
            </div>
            
            <div class="custom-field">
                <label for="brand_logo">Brand Logo</label>
                <input type="file" id="brand_logo" name="custom_brand_logo" accept="image/*" />
                <img id="brand_logo_preview" style="max-width:100px; display:none;" />
            </div>
          </div>';
    
    echo '<div id="bespoke_fields" style="display:none;">
            <p>There will be a certain additional cost for Bespoke printing</p>
            <h3>Step 2: Please write your requirements</h3>
            <p>Bespoke service including:</p>
            <ol>
                <li>Change color, pattern or printing position</li>
                <li>Add sponsor or any custom text</li>
                <li>3. Replicate your design idea and make your dream jersey.</li>

            </ol>
            <a style="width:100%;text-align:center; padding: 10px 22px; background-color: black; color: white;" href="#"> Get a Quote </a>
          </div>';
    
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Brand Logo Preview
        document.getElementById("brand_logo").addEventListener("change", function(event) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById("brand_logo_preview").src = e.target.result;
                document.getElementById("brand_logo_preview").style.display = "block";
            };
            reader.readAsDataURL(event.target.files[0]);
        });

        // Get Elements Once
        let shopNowBtn = document.getElementById("shop-now");
        let standardBtn = document.getElementById("standard_btn");
        let bespokeBtn = document.getElementById("bespoke_btn");

        // Standard Button Click
        standardBtn.addEventListener("click", function() {
            shopNowBtn.style.pointerEvents = "auto"; // Enable button
            standardBtn.style.backgroundColor = "black";
            bespokeBtn.style.backgroundColor = "var( --e-global-color-secondary )";
            shopNowBtn.style.cursor = "pointer"; // Corrected cursor issue
            document.getElementById("custom_fields_wrapper").style.display = "flex";
            document.getElementById("bespoke_fields").style.display = "none";
        });

        // Bespoke Button Click
        bespokeBtn.addEventListener("click", function() {
            shopNowBtn.style.display = "none"; // Disable button
            shopNowBtn.style.cursor = "not-allowed"; // Corrected cursor issue
            standardBtn.style.backgroundColor = "var( --e-global-color-secondary )";
            bespokeBtn.style.backgroundColor = "black";
            document.getElementById("custom_fields_wrapper").style.display = "none";
            document.getElementById("bespoke_fields").style.display = "block";
        });
    });
</script>
';
}

// Save custom fields data in the cart
add_filter('woocommerce_add_cart_item_data', 'save_custom_fields_to_cart', 10, 2);
function save_custom_fields_to_cart($cart_item_data, $product_id) {
    foreach ($_POST as $key => $value) {
        if (!empty($value) && strpos($key, 'custom_') === 0) {
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
            $label = ucfirst(str_replace('_', ' ', substr($key, 7)));
            $item_data[] = ['name' => $label, 'value' => $value];
        }
    }

    return $item_data;
}
?>
