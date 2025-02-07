<?php
/**
 * Plugin Name: WooCommerce Custom Product Fields
 * Description: Adds custom fields to WooCommerce product pages and captures customer input.
 * Version: 1.2
 * Author: Your Name
 * Text Domain: woocommerce-custom-fields
 * Domain Path: /languages
 */

// Extra fields on the product page
add_action( 'woocommerce_before_add_to_cart_button', 'add_custom_fields_to_product_page' );
function add_custom_fields_to_product_page() {
    echo '<div class="custom-field">
            <label for="custom_message">Custom Message</label>
            <input type="text" id="custom_message" name="custom_message" />
          </div>';
    
    // Team Name Field
    echo '<div class="custom-field">
            <label for="team_name">Team Name</label>
            <input type="text" id="team_name" name="team_name" />
          </div>';
    
    // Dropdown to select number of players
    echo '<div class="custom-field">
            <label for="player_count">Number of Players</label>
            <select id="player_count" name="player_count" onchange="generatePlayerFields()">
                <option value="">Select</option>';
    for ($i = 1; $i <= 30; $i++) {
        echo '<option value="'.$i.'">'.$i.'</option>';
    }
    echo '</select></div>';
    
    // Player Information Section
    echo '<fieldset class="custom-field" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
            <legend><strong>Player Information</strong></legend>
            <div id="player_fields"></div>
          </fieldset>';
    
    // Logo Upload Field
    echo '<div class="custom-field">
            <label for="logo_upload">Logo (Upload)</label>
            <input type="file" id="logo_upload" name="logo_upload" />
          </div>';
    
    // Sponsor Option Field
    echo '<div class="custom-field">
            <label for="sponsor_option">Sponsor Option</label>
            <select id="sponsor_option" name="sponsor_option">
                <option value="sponsor_text">Sponsor Text</option>
                <option value="sponsor_image">Sponsor Image</option>
            </select>
          </div>';
    
    // JavaScript to dynamically generate player fields
    echo '<script>
            function generatePlayerFields() {
                var count = document.getElementById("player_count").value;
                var container = document.getElementById("player_fields");
                container.innerHTML = "";
                for (var i = 1; i <= count; i++) {
                    container.innerHTML += '<div class="player-group" style="display:flex; gap:10px; margin-bottom:5px;">'
                        + '<label>Player ' + i + ' Size</label>'
                        + '<select name="player_size_' + i + '">'
                        + '<option value="small">Small</option>'
                        + '<option value="medium">Medium</option>'
                        + '<option value="large">Large</option>'
                        + '<option value="extra_large">Extra Large</option>'
                        + '</select>'
                        + '<label>Player ' + i + ' Name</label>'
                        + '<input type="text" name="player_name_' + i + '" />'
                        + '<label>Player ' + i + ' Number</label>'
                        + '<input type="text" name="player_number_' + i + '" />'
                        + '</div>';
                }
            }
          </script>';
}

// Add custom field values to the cart
add_filter( 'woocommerce_add_cart_item_data', 'save_custom_fields_to_cart', 10, 2 );
function save_custom_fields_to_cart( $cart_item_data, $product_id ) {
    foreach ($_POST as $key => $value) {
        if (!empty($value)) {
            $cart_item_data[$key] = sanitize_text_field($value);
        }
    }
    return $cart_item_data;
}

// Display custom fields in the cart
add_filter( 'woocommerce_get_item_data', 'display_custom_fields_in_cart', 10, 2 );
function display_custom_fields_in_cart( $item_data, $cart_item ) {
    foreach ($cart_item as $key => $value) {
        if (!in_array($key, ['product_id', 'quantity']) && !empty($value)) {
            $item_data[] = array(
                'name' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value
            );
        }
    }
    return $item_data;
}

// Display custom field values in the checkout and order
add_action( 'woocommerce_checkout_create_order_line_item', 'add_custom_fields_to_order', 10, 4 );
function add_custom_fields_to_order( $item, $cart_item_key, $values, $order ) {
    foreach ($values as $key => $value) {
        if (!in_array($key, ['product_id', 'quantity']) && !empty($value)) {
            $item->add_meta_data( ucfirst(str_replace('_', ' ', $key)), $value );
        }
    }
}
