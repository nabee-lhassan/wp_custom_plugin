<?php
/**
 * Plugin Name: WooCommerce Custom Product Fields
 * Description: Adds custom fields to WooCommerce product pages and captures customer input.
 * Version: 1.2
 * Author: Nabeel Hassan
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

    // Player Number Option Field
    echo '<div class="custom-field">
            <label for="player_number_option">Player Number Option</label>
            <select id="player_number_option" name="player_number_option">
                <option value="both_sides">Both Sides</option>
                <option value="only_back_number">Only Back Number</option>
                <option value="only_front_number">Only Front Number</option>
            </select>
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
    if( isset( $_POST['player_number_option'] ) ) {
        $cart_item_data['player_number_option'] = sanitize_text_field( $_POST['player_number_option'] );
    }
    if( isset( $_FILES['logo_upload'] ) ) {
        $cart_item_data['logo_upload'] = $_FILES['logo_upload'];
    }
    if( isset( $_POST['sponsor_option'] ) ) {
        $cart_item_data['sponsor_option'] = sanitize_text_field( $_POST['sponsor_option'] );
    }
    return $cart_item_data;
}

// Display custom fields in the cart
add_filter( 'woocommerce_get_item_data', 'display_custom_fields_in_cart', 10, 2 );
function display_custom_fields_in_cart( $item_data, $cart_item ) {
    if( isset( $cart_item['custom_message'] ) ) {
        $item_data[] = array(
            'name' => 'Custom Message',
            'value' => $cart_item['custom_message']
        );
    }
    if( isset( $cart_item['team_name'] ) ) {
        $item_data[] = array(
            'name' => 'Team Name',
            'value' => $cart_item['team_name']
        );
    }
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
    if( isset( $cart_item['player_number_option'] ) ) {
        $item_data[] = array(
            'name' => 'Player Number Option',
            'value' => ucfirst(str_replace('_', ' ', $cart_item['player_number_option']))
        );
    }
    if( isset( $cart_item['logo_upload'] ) ) {
        $item_data[] = array(
            'name' => 'Logo',
            'value' => 'Uploaded File'
        );
    }
    if( isset( $cart_item['sponsor_option'] ) ) {
        $item_data[] = array(
            'name' => 'Sponsor Option',
            'value' => ucfirst(str_replace('_', ' ', $cart_item['sponsor_option']))
        );
    }
    return $item_data;
}

// Display custom field values in the checkout
add_action( 'woocommerce_checkout_create_order_line_item', 'add_custom_fields_to_order', 10, 4 );
function add_custom_fields_to_order( $item, $cart_item_key, $values, $order ) {
    if( isset( $values['custom_message'] ) ) {
        $item->add_meta_data( 'Custom Message', $values['custom_message'] );
    }
    if( isset( $values['team_name'] ) ) {
        $item->add_meta_data( 'Team Name', $values['team_name'] );
    }
    if( isset( $values['player_size'] ) ) {
        $item->add_meta_data( 'Player Size', ucfirst($values['player_size']) );
    }
    if( isset( $values['player_name'] ) ) {
        $item->add_meta_data( 'Player Name', $values['player_name'] );
    }
    if( isset( $values['player_number_option'] ) ) {
        $item->add_meta_data( 'Player Number Option', ucfirst(str_replace('_', ' ', $values['player_number_option'])) );
    }
    if( isset( $values['logo_upload'] ) ) {
        $item->add_meta_data( 'Logo', 'Uploaded File' );
    }
    if( isset( $values['sponsor_option'] ) ) {
        $item->add_meta_data( 'Sponsor Option', ucfirst(str_replace('_', ' ', $values['sponsor_option'])) );
    }
}
