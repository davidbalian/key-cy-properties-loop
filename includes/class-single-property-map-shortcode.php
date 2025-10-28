<?php
/**
 * Single Property Map Shortcode Class
 * 
 * Renders a map view for a single property with radius circle
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Single_Property_Map_Shortcode
{
    /**
     * Render the single property map shortcode
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function render($attrs)
    {
        $attrs = shortcode_atts([
            'property_id' => 0,
            'height' => '400px',
            'zoom' => 15,
        ], $attrs, 'single_property_map');
        
        $property_id = intval($attrs['property_id']);
        $height = sanitize_text_field($attrs['height']);
        $zoom = intval($attrs['zoom']);
        
        // If no property ID provided, try to get current post
        if (!$property_id) {
            global $post;
            if ($post && $post->post_type === 'properties') {
                $property_id = $post->ID;
            }
        }
        
        // Validate property ID
        if (!$property_id || get_post_type($property_id) !== 'properties') {
            return self::renderError('Invalid property ID');
        }
        
        // Check if Google Maps API key is configured
        if (!KCPF_Settings_Manager::hasApiKey()) {
            return self::renderApiKeyWarning();
        }
        
        // Get property coordinates
        $coordinates = get_post_meta($property_id, 'display_coordinates', true);
        
        if (empty($coordinates)) {
            return self::renderError('Property location not available');
        }
        
        // Parse coordinates (format: "lat,lng")
        $coords = explode(',', $coordinates);
        if (count($coords) !== 2) {
            return self::renderError('Invalid property coordinates');
        }
        
        $lat = floatval(trim($coords[0]));
        $lng = floatval(trim($coords[1]));
        
        // Validate coordinates
        if ($lat === 0.0 || $lng === 0.0) {
            return self::renderError('Invalid property coordinates');
        }
        
        // Get property purpose
        $purpose_terms = get_the_terms($property_id, 'purpose');
        $purpose = 'sale';
        if ($purpose_terms && !is_wp_error($purpose_terms) && !empty($purpose_terms)) {
            $purpose = $purpose_terms[0]->slug;
        }
        
        // Render output
        ob_start();
        ?>
        <div class="kcpf-single-property-map" 
             data-property-id="<?php echo esc_attr($property_id); ?>"
             data-lat="<?php echo esc_attr($lat); ?>"
             data-lng="<?php echo esc_attr($lng); ?>"
             data-zoom="<?php echo esc_attr($zoom); ?>"
             data-purpose="<?php echo esc_attr($purpose); ?>">
            
            <!-- Map Container -->
            <div class="kcpf-single-map-container" style="height: <?php echo esc_attr($height); ?>;">
                <div id="kcpf-single-property-map-<?php echo esc_attr($property_id); ?>" 
                     class="kcpf-single-google-map"></div>
            </div>
            
            <!-- Loading State -->
            <div class="kcpf-single-map-loading" style="display: none;">
                <div class="kcpf-loading-spinner"></div>
                <p><?php _e('Loading map...', 'key-cy-properties-filter'); ?></p>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render error message
     * 
     * @param string $message Error message
     * @return string HTML output
     */
    private static function renderError($message)
    {
        return '<div class="kcpf-single-map-error"><p>' . esc_html($message) . '</p></div>';
    }
    
    /**
     * Render API key warning
     * 
     * @return string HTML output
     */
    private static function renderApiKeyWarning()
    {
        if (!current_user_can('manage_options')) {
            return '<div class="kcpf-single-map-error"><p>' . 
                   __('Map view is not configured. Please contact the site administrator.', 'key-cy-properties-filter') . 
                   '</p></div>';
        }
        
        $settings_url = admin_url('options-general.php?page=' . KCPF_Settings_Manager::PAGE_SLUG);
        
        ob_start();
        ?>
        <div class="kcpf-single-map-error">
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
