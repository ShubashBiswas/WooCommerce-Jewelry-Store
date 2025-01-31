<?php
/**
 * Plugin Name: Metal Prices Management
 * Description: Manage dynamic metal prices & making charge in WooCommerce.
 * Version: 1.0
 * Author: Shubash Kumar Biswas
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add a menu item for Metal Prices
add_action('admin_menu', 'add_metal_prices_settings_page');
function add_metal_prices_settings_page() {
    add_menu_page(
        'Metal Prices',        // Page title
        'Metal Prices',        // Menu title
        'manage_options',      // Capability
        'metal-prices',        // Menu slug
        'metal_prices_page',   // Callback function
        'dashicons-admin-generic', // Icon
        60                     // Position
    );
}

// Display the settings page
function metal_prices_page() {
    ?>
    <div class="wrap">
        <h1>Metal Prices Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('metal_prices_settings');
            do_settings_sections('metal-prices');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings for metal prices
add_action('admin_init', 'register_metal_prices_settings');
function register_metal_prices_settings() {
    // Gold Prices
    register_setting('metal_prices_settings', 'gold_22k_price_per_gram');
    register_setting('metal_prices_settings', 'gold_21k_price_per_gram');
    register_setting('metal_prices_settings', 'gold_18k_price_per_gram');

    // Silver Prices
    register_setting('metal_prices_settings', 'silver_price_per_gram');

    // Making Charge
    register_setting('metal_prices_settings', 'making_charge_percentage');

    // Settings sections and fields
    add_settings_section('gold_prices_section', 'Gold Prices', null, 'metal-prices');
    add_settings_field('gold_22k', 'Gold 22K (per gram)', 'gold_22k_field', 'metal-prices', 'gold_prices_section');
    add_settings_field('gold_21k', 'Gold 21K (per gram)', 'gold_21k_field', 'metal-prices', 'gold_prices_section');
    add_settings_field('gold_18k', 'Gold 18K (per gram)', 'gold_18k_field', 'metal-prices', 'gold_prices_section');

    add_settings_section('silver_prices_section', 'Silver Prices', null, 'metal-prices');
    add_settings_field('silver', 'Silver (per gram)', 'silver_field', 'metal-prices', 'silver_prices_section');

    // Making Charge Section
    add_settings_section('making_charge_section', 'Making Charge', null, 'metal-prices');
    add_settings_field('making_charge', 'Making Charge (%)', 'making_charge_field', 'metal-prices', 'making_charge_section');
}

// Callback functions for the fields
function gold_22k_field() {
    $value = get_option('gold_22k_price_per_gram', '');
    echo '<input type="text" name="gold_22k_price_per_gram" value="' . esc_attr($value) . '" />';
}

function gold_21k_field() {
    $value = get_option('gold_21k_price_per_gram', '');
    echo '<input type="text" name="gold_21k_price_per_gram" value="' . esc_attr($value) . '" />';
}

function gold_18k_field() {
    $value = get_option('gold_18k_price_per_gram', '');
    echo '<input type="text" name="gold_18k_price_per_gram" value="' . esc_attr($value) . '" />';
}

function silver_field() {
    $value = get_option('silver_price_per_gram', '');
    echo '<input type="text" name="silver_price_per_gram" value="' . esc_attr($value) . '" />';
}

function making_charge_field() {
    $value = get_option('making_charge_percentage', '15'); // Default to 15%
    echo '<input type="number" step="0.01" name="making_charge_percentage" value="' . esc_attr($value) . '" /> %';
}


add_filter('woocommerce_get_price', 'dynamic_metal_price_from_global_settings', 10, 2);
function dynamic_metal_price_from_global_settings($price, $product) {
    // Get the metal type and purity from the product attributes
    $metal_type = $product->get_attribute('Metal Type'); // Example: "Gold", "Silver"
    $purity = $product->get_attribute('Purity'); // Example: "22K", "21K", "18K", "Sterling Silver"

    // Get the weight of the product (in grams)
    $weight = (float) $product->get_weight();

    // Fetch the appropriate price per gram from global settings
    $price_per_gram = 0;

    if ($metal_type === 'Gold') {
        if ($purity === '22K') {
            $price_per_gram = get_option('gold_22k_price_per_gram', 0);
        } elseif ($purity === '21K') {
            $price_per_gram = get_option('gold_21k_price_per_gram', 0);
        } elseif ($purity === '18K') {
            $price_per_gram = get_option('gold_18k_price_per_gram', 0);
        }
    } elseif ($metal_type === 'Silver') {
        if ($purity === 'Sterling Silver') {
            $price_per_gram = get_option('silver_price_per_gram', 0);
        }
    }

    // Get the making charge percentage from admin settings (default 15%)
    $making_charge_percentage = (float) get_option('making_charge_percentage', 15);

    // Calculate the base price: price per gram × weight
    if ($price_per_gram && $weight) {
        $base_price = $price_per_gram * $weight;
        $making_charge = ($base_price * $making_charge_percentage) / 100; // Apply making charge
        $price = $base_price + $making_charge;
    }

    return $price;
}
