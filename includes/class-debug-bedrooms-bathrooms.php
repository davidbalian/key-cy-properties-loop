<?php
/**
 * Debug Class for Bedrooms and Bathrooms
 * 
 * Provides debugging functionality for bedrooms and bathrooms filters
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Debug_Bedrooms_Bathrooms
{
    /**
     * Register debug shortcode
     */
    public static function register()
    {
        add_shortcode('kcpf_debug_bedrooms_bathrooms', [self::class, 'renderDebug']);
    }
    
    /**
     * Render debug information
     * 
     * @param array $atts Shortcode attributes
     * @return string Debug output
     */
    public static function renderDebug($atts)
    {
        if (!current_user_can('manage_options')) {
            return '<p>Debug information is only available to administrators.</p>';
        }
        
        ob_start();
        ?>
        <div style="background: #f1f1f1; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
            <h2>Bedrooms and Bathrooms Debug Information</h2>
            
            <?php self::renderJetEngineStatus(); ?>
            <?php self::renderGlossaryData(); ?>
            <?php self::renderDatabaseValues(); ?>
            <?php self::renderFilterValues(); ?>
            <?php self::renderMetaKeys(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render JetEngine status
     */
    private static function renderJetEngineStatus()
    {
        ?>
        <h3>JetEngine Status</h3>
        <?php if (function_exists('jet_engine')): ?>
            <p style="color: green;">✅ JetEngine is available</p>
        <?php else: ?>
            <p style="color: red;">❌ JetEngine is NOT available</p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Render glossary data
     */
    private static function renderGlossaryData()
    {
        ?>
        <h3>Glossary Data</h3>
        
        <h4>Bedrooms Glossary (ID 7)</h4>
        <?php
        if (function_exists('jet_engine')) {
            $bedroomsOptions = jet_engine()->glossaries->filters->get_glossary_options(7);
            if (!empty($bedroomsOptions)) {
                echo '<pre style="background: white; padding: 10px; border: 1px solid #ddd;">' . print_r($bedroomsOptions, true) . '</pre>';
            } else {
                echo '<p style="color: red;">No options found for Bedrooms glossary (ID 7)</p>';
            }
        } else {
            echo '<p style="color: red;">JetEngine not available</p>';
        }
        ?>
        
        <h4>Bathrooms Glossary (ID 8)</h4>
        <?php
        if (function_exists('jet_engine')) {
            $bathroomsOptions = jet_engine()->glossaries->filters->get_glossary_options(8);
            if (!empty($bathroomsOptions)) {
                echo '<pre style="background: white; padding: 10px; border: 1px solid #ddd;">' . print_r($bathroomsOptions, true) . '</pre>';
            } else {
                echo '<p style="color: red;">No options found for Bathrooms glossary (ID 8)</p>';
            }
        } else {
            echo '<p style="color: red;">JetEngine not available</p>';
        }
        ?>
        <?php
    }
    
    /**
     * Render database values
     */
    private static function renderDatabaseValues()
    {
        ?>
        <h3>Database Values (Sample Properties)</h3>
        <?php
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
                ],
                [
                    'key' => 'bathrooms',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => 'rent_bathrooms',
                    'compare' => 'EXISTS'
                ]
            ]
        ]);
        
        if (empty($properties)) {
            echo '<p style="color: red;">No properties found with bedrooms/bathrooms data</p>';
            return;
        }
        
        foreach ($properties as $property) {
            echo '<div style="background: white; padding: 10px; margin: 10px 0; border: 1px solid #ddd;">';
            echo '<h4>Property ID: ' . $property->ID . ' - ' . $property->post_title . '</h4>';
            
            // Check sale bedrooms
            $saleBedrooms = get_post_meta($property->ID, 'bedrooms', true);
            echo '<strong>Sale Bedrooms:</strong> ' . var_export($saleBedrooms, true) . '<br>';
            
            // Check rent bedrooms
            $rentBedrooms = get_post_meta($property->ID, 'rent_bedrooms', true);
            echo '<strong>Rent Bedrooms:</strong> ' . var_export($rentBedrooms, true) . '<br>';
            
            // Check sale bathrooms
            $saleBathrooms = get_post_meta($property->ID, 'bathrooms', true);
            echo '<strong>Sale Bathrooms:</strong> ' . var_export($saleBathrooms, true) . '<br>';
            
            // Check rent bathrooms
            $rentBathrooms = get_post_meta($property->ID, 'rent_bathrooms', true);
            echo '<strong>Rent Bathrooms:</strong> ' . var_export($rentBathrooms, true) . '<br>';
            
            echo '</div>';
        }
        ?>
        <?php
    }
    
    /**
     * Render current filter values
     */
    private static function renderFilterValues()
    {
        ?>
        <h3>Current Filter Values</h3>
        <?php
        $filters = KCPF_URL_Manager::getCurrentFilters();
        echo '<pre style="background: white; padding: 10px; border: 1px solid #ddd;">' . print_r($filters, true) . '</pre>';
        ?>
        <?php
    }
    
    /**
     * Render meta keys
     */
    private static function renderMetaKeys()
    {
        ?>
        <h3>Meta Keys Configuration</h3>
        <p><strong>Sale Bedrooms Key:</strong> <?php echo KCPF_Field_Config::getMetaKey('bedrooms', 'sale'); ?></p>
        <p><strong>Rent Bedrooms Key:</strong> <?php echo KCPF_Field_Config::getMetaKey('bedrooms', 'rent'); ?></p>
        <p><strong>Sale Bathrooms Key:</strong> <?php echo KCPF_Field_Config::getMetaKey('bathrooms', 'sale'); ?></p>
        <p><strong>Rent Bathrooms Key:</strong> <?php echo KCPF_Field_Config::getMetaKey('bathrooms', 'rent'); ?></p>
        <?php
    }
}
