<?php
/**
 * Filters AJAX Handlers
 *
 * Provides endpoints to refresh purpose-aware filter fragments.
 *
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Filters_Ajax
{
    /**
     * Register AJAX hooks
     */
    public static function register()
    {
        add_action('wp_ajax_kcpf_refresh_filters', [self::class, 'refreshFilters']);
        add_action('wp_ajax_nopriv_kcpf_refresh_filters', [self::class, 'refreshFilters']);
    }

    /**
     * Refresh filter fragments when purpose changes
     */
    public static function refreshFilters()
    {
        try {
            // Read purpose and current selections from URL manager
            $purpose = KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale';
            
            // Clear any current selections to avoid stale data
            $_GET['location'] = [];
            $_GET['property_type'] = [];
            $_GET['bedrooms'] = [];
            $_GET['bathrooms'] = [];
            $_GET['price_min'] = '';
            $_GET['price_max'] = '';

            // Render updated fragments with purpose-aware data
            $location = KCPF_Filter_Renderer::renderLocation([
                'type' => 'checkbox',
                'show_count' => true,
            ]);
            
            $type = KCPF_Filter_Renderer::renderType([
                'type' => 'checkbox',
            ]);
            
            $price = KCPF_Filter_Renderer::renderPrice([
                'type' => 'slider',
            ]);
            
            $bedrooms = KCPF_Filter_Renderer::renderBedrooms([
                'type' => 'checkbox',
            ]);
            
            $bathrooms = KCPF_Filter_Renderer::renderBathrooms([
                'type' => 'checkbox',
            ]);
            
            // Get price range for JS to update slider
            $priceRange = KCPF_Listing_Values::getMinMax('price', $purpose);

            wp_send_json_success([
                'purpose' => $purpose,
                'html' => [
                    'location' => $location,
                    'type' => $type,
                    'price' => $price,
                    'bedrooms' => $bedrooms,
                    'bathrooms' => $bathrooms,
                ],
                'priceRange' => [
                    'min' => $priceRange['min'],
                    'max' => $priceRange['max'],
                ],
            ]);
        } catch (Exception $e) {
            error_log('KCPF Refresh Filters Error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => 'Failed to refresh filters',
                'error' => $e->getMessage(),
            ]);
        }
    }
}


