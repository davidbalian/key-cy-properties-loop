<?php
/**
 * Debug script to check bedrooms and bathrooms data
 * 
 * This script will help us understand what values are stored in the database
 * and what the glossaries contain.
 */

// Include WordPress
require_once('../../../wp-load.php');

echo "<h1>Bedrooms and Bathrooms Debug</h1>";

// Check if JetEngine is available
if (function_exists('jet_engine')) {
    echo "<h2>JetEngine is Available</h2>";
    
    // Test bedrooms glossary (ID 7)
    echo "<h3>Bedrooms Glossary (ID 7)</h3>";
    $bedroomsOptions = jet_engine()->glossaries->filters->get_glossary_options(7);
    echo "<pre>" . print_r($bedroomsOptions, true) . "</pre>";
    
    // Test bathrooms glossary (ID 8)
    echo "<h3>Bathrooms Glossary (ID 8)</h3>";
    $bathroomsOptions = jet_engine()->glossaries->filters->get_glossary_options(8);
    echo "<pre>" . print_r($bathroomsOptions, true) . "</pre>";
    
} else {
    echo "<h2>JetEngine is NOT Available</h2>";
}

// Check actual database values
echo "<h2>Database Values</h2>";

// Get some properties to see what's stored
$properties = get_posts([
    'post_type' => 'properties',
    'post_status' => 'publish',
    'posts_per_page' => 5,
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => 'bedrooms',
            'compare' => 'EXISTS'
        ],
        [
            'key' => 'rent_bedrooms',
            'compare' => 'EXISTS'
        ]
    ]
]);

echo "<h3>Sample Properties with Bedrooms Data:</h3>";
foreach ($properties as $property) {
    echo "<h4>Property ID: " . $property->ID . " - " . $property->post_title . "</h4>";
    
    // Check sale bedrooms
    $saleBedrooms = get_post_meta($property->ID, 'bedrooms', true);
    echo "Sale Bedrooms: " . var_export($saleBedrooms, true) . "<br>";
    
    // Check rent bedrooms
    $rentBedrooms = get_post_meta($property->ID, 'rent_bedrooms', true);
    echo "Rent Bedrooms: " . var_export($rentBedrooms, true) . "<br>";
    
    // Check sale bathrooms
    $saleBathrooms = get_post_meta($property->ID, 'bathrooms', true);
    echo "Sale Bathrooms: " . var_export($saleBathrooms, true) . "<br>";
    
    // Check rent bathrooms
    $rentBathrooms = get_post_meta($property->ID, 'rent_bathrooms', true);
    echo "Rent Bathrooms: " . var_export($rentBathrooms, true) . "<br>";
    
    echo "<hr>";
}

// Test the filter values
echo "<h2>Current Filter Values</h2>";
$filters = KCPF_URL_Manager::getCurrentFilters();
echo "<pre>" . print_r($filters, true) . "</pre>";

// Test the meta keys
echo "<h2>Meta Keys</h2>";
echo "Sale Bedrooms Key: " . KCPF_Field_Config::getMetaKey('bedrooms', 'sale') . "<br>";
echo "Rent Bedrooms Key: " . KCPF_Field_Config::getMetaKey('bedrooms', 'rent') . "<br>";
echo "Sale Bathrooms Key: " . KCPF_Field_Config::getMetaKey('bathrooms', 'sale') . "<br>";
echo "Rent Bathrooms Key: " . KCPF_Field_Config::getMetaKey('bathrooms', 'rent') . "<br>";
?>
