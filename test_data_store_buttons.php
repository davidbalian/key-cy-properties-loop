<?php
/**
 * Test Data Store Buttons in Property Cards
 *
 * This page tests the JetEngine data store button integration
 * Access via: yoursite.com/test_data_store_buttons.php
 */

require_once 'key-cy-properties-filter.php';

// Get some sample properties for testing
$query = new WP_Query([
    'post_type' => 'properties',
    'posts_per_page' => 6,
    'post_status' => 'publish'
]);

echo '<!DOCTYPE html>';
echo '<html lang="en">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>Data Store Button Test</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }';
echo '.container { max-width: 1200px; margin: 0 auto; }';
echo 'h1 { color: #333; margin-bottom: 30px; }';
echo '.test-info { background: #e8f5e8; border: 1px solid #4CAF50; padding: 15px; border-radius: 4px; margin-bottom: 20px; }';
echo '.test-info h2 { margin-top: 0; color: #2E7D32; }';
echo '.test-info ul { margin: 10px 0; }';
echo '.test-info li { margin: 5px 0; }';
echo '.error { background: #ffebee; border: 1px solid #f44336; padding: 15px; border-radius: 4px; margin-bottom: 20px; }';
echo '.properties-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-top: 20px; }';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<div class="container">';

echo '<h1>JetEngine Data Store Button Test</h1>';

echo '<div class="test-info">';
echo '<h2>Testing Data Store Button Integration</h2>';
echo '<p>This page tests the integration of JetEngine data store buttons with property cards.</p>';
echo '<ul>';
echo '<li>✅ Data store buttons should appear in the top-left corner of each property card</li>';
echo '<li>✅ Unfavorited properties show outline star (add to favorites)</li>';
echo '<li>✅ Favorited properties show filled star (remove from favorites)</li>';
echo '<li>✅ Clicking should trigger JetEngine\'s JavaScript functionality</li>';
echo '<li>✅ Buttons should be properly positioned and styled</li>';
echo '</ul>';
echo '</div>';

// Check if JetEngine is available
if (!function_exists('jet_engine')) {
    echo '<div class="error">';
    echo '<h3>❌ JetEngine Not Available</h3>';
    echo '<p>JetEngine plugin is not active. Data store functionality will not work.</p>';
    echo '</div>';
} else {
    echo '<div class="test-info">';
    echo '<h3>✅ JetEngine Available</h3>';
    echo '<p>JetEngine is active. Testing data store button rendering...</p>';
    echo '</div>';
}

if ($query->have_posts()) {
    echo '<h2>Sample Properties with Data Store Buttons</h2>';
    echo '<div class="properties-grid">';

    while ($query->have_posts()) {
        $query->the_post();
        $property_id = get_the_ID();

        // Get property purpose
        $purpose_terms = get_the_terms($property_id, 'purpose');
        $purpose = 'sale';
        if ($purpose_terms && !is_wp_error($purpose_terms) && !empty($purpose_terms)) {
            $purpose = $purpose_terms[0]->slug;
        }

        // Render property card with data store button
        echo KCPF_Map_Card_Renderer::renderCard($property_id, $purpose);
    }

    echo '</div>';
    wp_reset_postdata();
} else {
    echo '<div class="error">';
    echo '<h3>No Properties Found</h3>';
    echo '<p>Please add some properties to test the data store button functionality.</p>';
    echo '</div>';
}

echo '<div class="test-info">';
echo '<h2>Technical Details</h2>';
echo '<ul>';
echo '<li><strong>Data Store Slug:</strong> favorite_properties_store</li>';
echo '<li><strong>Button Renderer:</strong> KCPF_Data_Store_Button_Renderer</li>';
echo '<li><strong>Integration:</strong> Map Card Renderer & Rent Card View</li>';
echo '<li><strong>Styling:</strong> property-cards-shared.css</li>';
echo '</ul>';
echo '<p><em>Note: Make sure users are logged in to test the favorites functionality, as data stores are typically user-specific.</em></p>';
echo '</div>';

echo '</div>';
echo '</body>';
echo '</html>';
?>
