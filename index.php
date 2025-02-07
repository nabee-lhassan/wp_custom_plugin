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
    
    echo '<div class="custom-field">
            <label for="team_name">Team Name</label>
            <input type="text" id="team_name" name="team_name" />
          </div>';
    
    echo '<fieldset class="custom-field" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
            <legend><strong>Player Information</strong></legend>';
    
    echo '<div class="player-group" style="display: flex; gap: 10px;">';
    
    // Size Field
    echo '<div class="custom-field">
            <label for="player_size">Size</label>
            <select id="player_size" name="player_size">
                <option value="small">Small</option>
                <option value="medium">Medium</option>
                <option value="large">Large</option>
                <option value="extra_large">Extra Large</option>
            </select>
          </div>';
    
    // Player Name Field
    echo '<div class="custom-field">
            <label for="player_name">Player Name</label>
            <input type="text" id="player_name" name="player_name" />
          </div>';
    
    // Player Number Field
    echo '<div class="custom-field">
            <label for="player_number">Player Number</label>
            <input type="text" id="player_number" name="player_number" />
          </div>';
    
    echo '</div>'; // End of Player Group
    
    echo '</fieldset>'; // End of Player Information Section

    echo '<div class="custom-field">
            <label for="logo_upload">Logo (Upload)</label>
            <input type="file" id="logo_upload" name="logo_upload" />
          </div>';
    
    echo '<div class="custom-field">
            <label for="sponsor_option">Sponsor Option</label>
            <select id="sponsor_option" name="sponsor_option">
                <option value="sponsor_text">Sponsor Text</option>
                <option value="sponsor_image">Sponsor Image</option>
            </select>
          </div>';
}

// Add custom field values to the cart
add_filter( 'woocommerce_add_cart_item_data', 'save_custom_fields_to_cart', 10, 2 );
function save_custom_fields_to_cart( $cart_item_data, $product_id ) {
    if( isset( $_POST['custom_message'] ) ) {
        $cart_item_data['custom_message'] = sanitize_text_field( $_POST['custom_message'] );
    }
    if( isset( $_POST['team_name'] ) ) {
        $cart_item_data['team_name'] = sanitize_text_field( $_POST['team_name'] );
    }
    if( isset( $_POST['player_size'] ) ) {
        $cart_item_data['player_size'] = sanitize_text_field( $_POST['player_size'] );
    }
    if( isset( $_POST['player_name'] ) ) {
        $cart_item_data['player_name'] = sanitize_text_field( $_POST['player_name'] );
    }
    if( isset( $_POST['player_number'] ) ) {
        $cart_item_data['player_number'] = sanitize_text_field( $_POST['player_number'] );
    }
    if( isset( $_FILES['logo_upload'] ) ) {
        $cart_item_data['logo_upload'] = $_FILES['logo_upload'];
    }
    if( isset( $_POST['sponsor_option'] ) ) {
        $cart_item_data['sponsor_option'] = sanitize_text_field( $_POST['sponsor_option'] );
    }
    return $cart_item_data;
}
