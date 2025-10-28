<?php
/**
 * Test Single Property Map Shortcode
 * 
 * This file can be used to test the single property map shortcode
 * Place it in your WordPress root directory and access via browser
 */

// Load WordPress
require_once('wp-config.php');

// Get a property ID for testing
$query = new WP_Query([
    'post_type' => 'properties',
    'posts_per_page' => 1,
    'meta_query' => [
        [
            'key' => 'display_coordinates',
            'compare' => 'EXISTS'
        ]
    ]
]);

if ($query->have_posts()) {
    $query->the_post();
    $property_id = get_the_ID();
    $coordinates = get_post_meta($property_id, 'display_coordinates', true);
    
    echo "<h1>Single Property Map Test</h1>";
    echo "<p><strong>Property ID:</strong> {$property_id}</p>";
    echo "<p><strong>Property Title:</strong> " . get_the_title() . "</p>";
    echo "<p><strong>Coordinates:</strong> {$coordinates}</p>";
    echo "<hr>";
    
    // Test the shortcode
    echo "<h2>Shortcode Output:</h2>";
    echo do_shortcode("[single_property_map property_id=\"{$property_id}\" height=\"500px\"]");
    
    wp_reset_postdata();
} else {
    echo "<h1>No Properties Found</h1>";
    echo "<p>No properties with coordinates found for testing.</p>";
}
?>
