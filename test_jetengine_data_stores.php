<?php
/**
 * Test JetEngine Data Stores API
 *
 * This file explores JetEngine data store functionality
 * Access via: yoursite.com/test_jetengine_data_stores.php
 */

if (!function_exists('jet_engine')) {
    die('JetEngine is not active.');
}

echo '<h1>JetEngine Data Stores Test</h1>';
echo '<style>body { font-family: monospace; padding: 20px; line-height: 1.6; } pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; } code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }</style>';

// Get JetEngine instance
$jet_engine = jet_engine();

// Check if data stores are available
if (!isset($jet_engine->data_stores)) {
    echo '<p><strong>Data stores not available in this JetEngine version.</strong></p>';
    echo '<p>Available JetEngine components:</p><ul>';

    foreach ($jet_engine as $component => $instance) {
        echo '<li>' . $component . '</li>';
    }
    echo '</ul>';
    exit;
}

$data_stores = $jet_engine->data_stores;

echo '<h2>Data Stores Instance</h2>';
echo '<p>Class: <code>' . get_class($data_stores) . '</code></p>';

echo '<h2>Available Methods</h2>';
$methods = get_class_methods($data_stores);
echo '<ul>';
foreach ($methods as $method) {
    echo '<li><code>' . $method . '</code></li>';
}
echo '</ul>';

echo '<h2>Get All Data Stores</h2>';
// Try to get all data stores
if (method_exists($data_stores, 'get_stores')) {
    $stores = $data_stores->get_stores();
    echo '<p>Found ' . count($stores) . ' data stores:</p>';

    if (!empty($stores)) {
        echo '<ul>';
        foreach ($stores as $store_id => $store) {
            echo '<li><strong>' . $store_id . '</strong>';
            if (is_array($store) && isset($store['name'])) {
                echo ' - ' . $store['name'];
            }
            echo '</li>';
        }
        echo '</ul>';
    }
} elseif (method_exists($data_stores, 'get_items')) {
    $stores = $data_stores->get_items();
    echo '<p>Found ' . count($stores) . ' data stores (via get_items):</p>';

    if (!empty($stores)) {
        echo '<ul>';
        foreach ($stores as $store_id => $store) {
            echo '<li><strong>' . $store_id . '</strong>';
            if (is_array($store) && isset($store['name'])) {
                echo ' - ' . $store['name'];
            }
            echo '</li>';
        }
        echo '</ul>';
    }
} else {
    echo '<p>No method found to list data stores.</p>';
}

echo '<h2>Data Store Operations Test</h2>';

// Test basic operations if we have stores
if (isset($stores) && !empty($stores)) {
    $first_store_id = key($stores);
    echo '<p>Testing operations on store: <code>' . $first_store_id . '</code></p>';

    // Try to get store data
    if (method_exists($data_stores, 'get_store')) {
        $store_data = $data_stores->get_store($first_store_id);
        echo '<h3>Store Data:</h3><pre>';
        print_r($store_data);
        echo '</pre>';
    }

    // Try to get items from store
    if (method_exists($data_stores, 'get_store_items')) {
        $items = $data_stores->get_store_items($first_store_id);
        echo '<h3>Store Items (' . count($items) . '):</h3>';
        if (!empty($items)) {
            echo '<pre>';
            print_r(array_slice($items, 0, 5)); // Show first 5 items
            echo '</pre>';
            if (count($items) > 5) {
                echo '<p>... and ' . (count($items) - 5) . ' more items</p>';
            }
        } else {
            echo '<p>No items in this store.</p>';
        }
    }
}

echo '<h2>API Usage Examples</h2>';
echo '<pre>';
echo '// Get JetEngine data stores instance
$data_stores = jet_engine()->data_stores;

// Get all data stores
$stores = $data_stores->get_stores();

// Get items from a specific store
$items = $data_stores->get_store_items($store_id);

// Add item to store (if method exists)
$data_stores->add_to_store($store_id, $item_data);

// Remove item from store (if method exists)
$data_stores->remove_from_store($store_id, $item_id);
';
echo '</pre>';
?>
