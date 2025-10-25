<?php
// Quick test to check bedroom data formats
add_action('wp', 'test_bedroom_data');

function test_bedroom_data() {
    if (!isset($_GET['test_bedroom_data'])) return;
    
    echo '<h1>Bedroom Data Test</h1>';
    
    // Get some sale and rent properties
    $sale_args = ['post_type' => 'properties', 'posts_per_page' => 3, 'tax_query' => [['taxonomy' => 'purpose', 'field' => 'slug', 'terms' => 'sale']]];
    $rent_args = ['post_type' => 'properties', 'posts_per_page' => 3, 'tax_query' => [['taxonomy' => 'purpose', 'field' => 'slug', 'terms' => 'rent']]];
    
    $sale_posts = get_posts($sale_args);
    $rent_posts = get_posts($rent_args);
    
    echo '<h2>Sale Properties (using rent_bedrooms field)</h2>';
    foreach ($sale_posts as $post) {
        $bedrooms = get_post_meta($post->ID, 'rent_bedrooms', true);
        echo '<p>' . $post->post_title . ': <code>' . esc_html(substr($bedrooms, 0, 200)) . '</code></p>';
    }
    
    echo '<h2>Rent Properties (using rent_bedrooms field)</h2>';
    foreach ($rent_posts as $post) {
        $bedrooms = get_post_meta($post->ID, 'rent_bedrooms', true);
        echo '<p>' . $post->post_title . ': <code>' . esc_html(substr($bedrooms, 0, 200)) . '</code></p>';
    }
    
    exit;
}
