<?php
/**
 * Map Shortcode Class
 * 
 * Renders the properties map view with filters and interactive Google Map
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Map_Shortcode
{
    /**
     * Render the map shortcode
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function render($attrs)
    {
        $attrs = shortcode_atts([
            'purpose' => 'sale',
        ], $attrs, 'properties_map');
        
        $purpose = sanitize_text_field($attrs['purpose']);
        
        // Check if Google Maps API key is configured
        if (!KCPF_Settings_Manager::hasApiKey()) {
            return self::renderApiKeyWarning();
        }
        
        // Get initial properties
        $properties = self::getProperties($purpose);
        
        // Render output
        ob_start();
        ?>
        <div class="kcpf-map-view" data-purpose="<?php echo esc_attr($purpose); ?>">
            <!-- Filters Section -->
            <div class="kcpf-map-filters-section">
                <?php
                // Set purpose in GET parameters for mega filters
                $_GET['purpose'] = $purpose;
                echo KCPF_Filter_Renderer::renderMegaFilters([]);
                ?>
            </div>
            
            <!-- Two Column Layout -->
            <div class="kcpf-map-layout">
                <!-- Left Column: Property Cards -->
                <div class="kcpf-map-sidebar">
                    <div class="kcpf-map-results-header">
                        <span class="kcpf-map-results-count">
                            <?php echo sprintf(
                                _n('%d property found', '%d properties found', count($properties), 'key-cy-properties-filter'),
                                count($properties)
                            ); ?>
                        </span>
                    </div>
                    <div class="kcpf-map-cards-container" id="kcpf-map-cards">
                        <?php echo KCPF_Map_Card_Renderer::renderCards($properties, $purpose); ?>
                    </div>
                    <div class="kcpf-map-loading" style="display: none;">
                        <div class="kcpf-loading-spinner"></div>
                        <p><?php _e('Loading properties...', 'key-cy-properties-filter'); ?></p>
                    </div>
                </div>
                
                <!-- Right Column: Google Map -->
                <div class="kcpf-map-container">
                    <div id="kcpf-google-map" class="kcpf-google-map"></div>
                </div>
            </div>
        </div>
        
        <!-- Output properties data as JSON for JavaScript -->
        <script type="application/json" id="kcpf-map-properties-data">
        <?php echo wp_json_encode(self::getPropertiesData($properties, $purpose)); ?>
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get properties based on filters
     * 
     * @param string $purpose Property purpose
     * @param array $filters Optional filters array
     * @return array Array of property IDs
     */
    public static function getProperties($purpose = 'sale', $filters = [])
    {
        // Set purpose in attrs
        $attrs = [
            'purpose' => $purpose,
            'posts_per_page' => 50, // Reasonable limit for map view
        ];
        
        // If filters are provided, temporarily set them in $_GET
        $original_get = $_GET;
        if (!empty($filters)) {
            $_GET = array_merge($_GET, $filters);
        }
        
        // Build query
        $query_args = KCPF_Query_Handler::buildQueryArgs($attrs);
        $query_args['fields'] = 'ids'; // Only get IDs for performance
        
        $query = new WP_Query($query_args);
        $property_ids = $query->posts;
        
        // Restore original $_GET
        $_GET = $original_get;
        
        wp_reset_postdata();
        
        return $property_ids;
    }
    
    /**
     * Get properties data for JavaScript
     * 
     * @param array $property_ids Array of property IDs
     * @param string $purpose Property purpose
     * @return array Properties data
     */
    private static function getPropertiesData($property_ids, $purpose = 'sale')
    {
        $data = [];
        
        foreach ($property_ids as $property_id) {
            $coordinates = get_post_meta($property_id, 'display_coordinates', true);
            
            // Skip properties without coordinates
            if (empty($coordinates)) {
                continue;
            }
            
            // Parse coordinates (format: "lat,lng")
            $coords = explode(',', $coordinates);
            if (count($coords) !== 2) {
                continue;
            }
            
            $lat = floatval(trim($coords[0]));
            $lng = floatval(trim($coords[1]));
            
            // Validate coordinates
            if ($lat === 0.0 || $lng === 0.0) {
                continue;
            }
            
            $data[] = [
                'id' => $property_id,
                'title' => get_the_title($property_id),
                'lat' => $lat,
                'lng' => $lng,
                'url' => get_permalink($property_id),
            ];
        }
        
        return $data;
    }
    
    /**
     * AJAX handler for loading filtered properties
     */
    public static function ajaxLoadMapProperties()
    {
        try {
            // Get purpose
            $purpose = isset($_GET['purpose']) ? sanitize_text_field($_GET['purpose']) : 'sale';
            
            // Get filters
            $filters = KCPF_URL_Manager::getCurrentFilters();
            
            // Get properties
            $property_ids = self::getProperties($purpose, $filters);
            
            // Get properties data for map
            $properties_data = self::getPropertiesData($property_ids, $purpose);
            
            // Render cards HTML
            $cards_html = KCPF_Map_Card_Renderer::renderCards($property_ids, $purpose);
            
            // Return response
            wp_send_json_success([
                'count' => count($property_ids),
                'cards_html' => $cards_html,
                'properties_data' => $properties_data,
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => 'Error loading properties',
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * AJAX handler for getting a single property card for info window
     */
    public static function ajaxGetPropertyCard()
    {
        try {
            $property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : 0;
            
            if (!$property_id) {
                wp_send_json_error(['message' => 'No property ID provided']);
                return;
            }
            
            // Get property purpose
            $purpose_terms = get_the_terms($property_id, 'purpose');
            $purpose = 'sale';
            if ($purpose_terms && !is_wp_error($purpose_terms) && !empty($purpose_terms)) {
                $purpose = $purpose_terms[0]->slug;
            }
            
            // Render info window card
            $html = self::renderInfoWindowCard($property_id, $purpose);
            
            wp_send_json_success(['html' => $html]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => 'Error loading property card',
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Render property card for info window
     * 
     * @param int $property_id Property ID
     * @param string $purpose Property purpose
     * @return string HTML output
     */
    private static function renderInfoWindowCard($property_id, $purpose = 'sale')
    {
        // Use the same card renderer as the sidebar, but hide multi-unit tables
        $cardHtml = KCPF_Map_Card_Renderer::renderCard($property_id, $purpose, true);
        
        // Add info-window-card class to the article tag
        $cardHtml = str_replace(
            '<article class="kcpf-property-card',
            '<article class="kcpf-property-card kcpf-info-window-card',
            $cardHtml
        );
        
        return $cardHtml;
    }
    
    /**
     * Render API key warning
     * 
     * @return string HTML output
     */
    private static function renderApiKeyWarning()
    {
        if (!current_user_can('manage_options')) {
            return '<div class="kcpf-map-error"><p>' . 
                   __('Map view is not configured. Please contact the site administrator.', 'key-cy-properties-filter') . 
                   '</p></div>';
        }
        
        $settings_url = admin_url('options-general.php?page=' . KCPF_Settings_Manager::PAGE_SLUG);
        
        ob_start();
        ?>
        <div class="kcpf-map-error">
            <h3><?php _e('Google Maps API Key Required', 'key-cy-properties-filter'); ?></h3>
            <p><?php _e('The map view requires a Google Maps API key to function.', 'key-cy-properties-filter'); ?></p>
            <p>
                <a href="<?php echo esc_url($settings_url); ?>" class="button button-primary">
                    <?php _e('Configure API Key', 'key-cy-properties-filter'); ?>
                </a>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }
}

