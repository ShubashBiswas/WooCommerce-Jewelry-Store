### Add a Product Attribute for Metal Type and Purity
Create Product Attributes:

Go to Products > Attributes in the WordPress dashboard.
Create the following attributes:

Metal Type (values: Gold, Silver)
Purity (values: 24K, 22K, 18K, Sterling Silver, etc.)

Assign Attributes to Products:
While creating or editing a product, assign these attributes under the Attributes section.
Ensure these attributes are set as visible on the product page and used for variations.

---

### **Centralized Price Management**
You can store the price per gram for gold and silver (based on purity) in a global location, like **site-wide options** or a custom settings page. Then, all products will automatically use these values without needing individual updates.

---

### **Steps to Automate Pricing Updates**

#### **1. Create a Global Settings Page for Metal Prices**
You can create a settings page in the WordPress admin panel to input the current price per gram for different purities.

##### **Code to Create the Settings Page:**

Add this code to your theme's `functions.php` file or a custom plugin:

```php
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
    register_setting('metal_prices_settings', 'gold_24k_price_per_gram');
    register_setting('metal_prices_settings', 'gold_22k_price_per_gram');
    register_setting('metal_prices_settings', 'gold_18k_price_per_gram');

    // Silver Prices
    register_setting('metal_prices_settings', 'silver_price_per_gram');

    // Settings sections and fields
    add_settings_section('gold_prices_section', 'Gold Prices', null, 'metal-prices');
    add_settings_field('gold_24k', 'Gold 24K (per gram)', 'gold_24k_field', 'metal-prices', 'gold_prices_section');
    add_settings_field('gold_22k', 'Gold 22K (per gram)', 'gold_22k_field', 'metal-prices', 'gold_prices_section');
    add_settings_field('gold_18k', 'Gold 18K (per gram)', 'gold_18k_field', 'metal-prices', 'gold_prices_section');

    add_settings_section('silver_prices_section', 'Silver Prices', null, 'metal-prices');
    add_settings_field('silver', 'Silver (per gram)', 'silver_field', 'metal-prices', 'silver_prices_section');
}

// Callback functions for the fields
function gold_24k_field() {
    $value = get_option('gold_24k_price_per_gram', '');
    echo '<input type="text" name="gold_24k_price_per_gram" value="' . esc_attr($value) . '" />';
}

function gold_22k_field() {
    $value = get_option('gold_22k_price_per_gram', '');
    echo '<input type="text" name="gold_22k_price_per_gram" value="' . esc_attr($value) . '" />';
}

function gold_18k_field() {
    $value = get_option('gold_18k_price_per_gram', '');
    echo '<input type="text" name="gold_18k_price_per_gram" value="' . esc_attr($value) . '" />';
}

function silver_field() {
    $value = get_option('silver_price_per_gram', '');
    echo '<input type="text" name="silver_price_per_gram" value="' . esc_attr($value) . '" />';
}
```

---

#### **2. Use Global Prices in the Pricing Calculation**
Update the pricing function to pull values from the global settings instead of product-level custom fields:

```php
add_filter('woocommerce_get_price', 'dynamic_metal_price_from_global_settings', 10, 2);
function dynamic_metal_price_from_global_settings($price, $product) {
    // Get the metal type and purity from the product attributes
    $metal_type = $product->get_attribute('Metal Type'); // Example: "Gold", "Silver"
    $purity = $product->get_attribute('Purity'); // Example: "24K", "22K", "18K", "Sterling Silver"

    // Get the weight of the product (in grams)
    $weight = (float) $product->get_weight();

    // Fetch the appropriate price per gram from global settings
    $price_per_gram = 0;

    if ($metal_type === 'Gold') {
        if ($purity === '24K') {
            $price_per_gram = get_option('gold_24k_price_per_gram', 0);
        } elseif ($purity === '22K') {
            $price_per_gram = get_option('gold_22k_price_per_gram', 0);
        } elseif ($purity === '18K') {
            $price_per_gram = get_option('gold_18k_price_per_gram', 0);
        }
    } elseif ($metal_type === 'Silver') {
        if ($purity === 'Sterling Silver') {
            $price_per_gram = get_option('silver_price_per_gram', 0);
        }
    }

    // Calculate the final price: price per gram × weight
    if ($price_per_gram && $weight) {
        $price = $price_per_gram * $weight;
    }

    return $price;
}
```

---

#### **3. Update Prices via the Admin Panel**
1. Go to the **Metal Prices** settings page in the WordPress admin menu.
2. Enter the latest prices for gold and silver per gram (based on purity).
3. Save the changes.

All products will now dynamically calculate their prices based on the updated global prices, without requiring individual updates.

---

### **Benefits of This Approach**
- **Centralized Updates**: You only need to update prices in one place (the global settings page).
- **Dynamic Pricing**: All products automatically calculate their prices based on the latest values.
- **Time-Saving**: Eliminates the need to manually update each product when prices change.

---
