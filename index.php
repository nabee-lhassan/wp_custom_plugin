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
    
    // Player Information Section
    echo '<fieldset class="custom-field" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
            <legend><strong>Player Information</strong></legend>';
    
    // Player Group Fields
    echo '<div class="player-group">
            <label for="player_size">Size</label>
            <select id="player_size" name="player_size">
                <option value="small">Small</option>
                <option value="medium">Medium</option>
                <option value="large">Large</option>
                <option value="extra_large">Extra Large</option>
            </select>
          </div>';
    
    echo '<div class="player-group">
            <label for="player_name">Player Name</label>
            <input type="text" id="player_name" name="player_name" />
          </div>';

    echo '<div class="player-group">
            <label for="player_number">Player Number</label>
            <input type="text" id="player_number" name="player_number" />
          </div>';
    
    echo '</fieldset>'; // End of Player Information Section

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
    return $cart_item_data;
}

// Display custom fields in the cart
add_filter( 'woocommerce_get_item_data', 'display_custom_fields_in_cart', 10, 2 );
function display_custom_fields_in_cart( $item_data, $cart_item ) {
    if( isset( $cart_item['player_size'] ) ) {
        $item_data[] = array(
            'name' => 'Player Size',
            'value' => ucfirst($cart_item['player_size'])
        );
    }
    if( isset( $cart_item['player_name'] ) ) {
        $item_data[] = array(
            'name' => 'Player Name',
            'value' => $cart_item['player_name']
        );
    }
    if( isset( $cart_item['player_number'] ) ) {
        $item_data[] = array(
            'name' => 'Player Number',
            'value' => $cart_item['player_number']
        );
    }
    return $item_data;
}

// Display custom field values in the checkout
add_action( 'woocommerce_checkout_create_order_line_item', 'add_custom_fields_to_order', 10, 4 );
function add_custom_fields_to_order( $item, $cart_item_key, $values, $order ) {
    if( isset( $values['player_size'] ) ) {
        $item->add_meta_data( 'Player Size', ucfirst($values['player_size']) );
    }
    if( isset( $values['player_name'] ) ) {
        $item->add_meta_data( 'Player Name', $values['player_name'] );
    }
    if( isset( $values['player_number'] ) ) {
        $item->add_meta_data( 'Player Number', $values['player_number'] );
    }
}
