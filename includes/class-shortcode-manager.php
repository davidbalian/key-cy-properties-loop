<?php
/**
 * Shortcode Manager Class
 * 
 * Handles registration of all plugin shortcodes
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Shortcode_Manager
{
    /**
     * Register all shortcodes
     */
    public static function register()
    {
        self::registerLoopShortcodes();
        self::registerFilterShortcodes();
        self::registerMapShortcodes();
    }
    
    /**
     * Register properties loop shortcodes
     */
    private static function registerLoopShortcodes()
    {
        add_shortcode('properties_loop', [KCPF_Loop_Renderer::class, 'render']);
        add_shortcode('homepage_filters', [KCPF_Homepage_Filters::class, 'render']);
    }
    
    /**
     * Register filter shortcodes
     */
    private static function registerFilterShortcodes()
    {
        add_shortcode('property_filter_location', [KCPF_Filter_Renderer::class, 'renderLocation']);
        add_shortcode('property_filter_purpose', [KCPF_Filter_Renderer::class, 'renderPurpose']);
        add_shortcode('property_filter_price', [KCPF_Filter_Renderer::class, 'renderPrice']);
        add_shortcode('property_filter_bedrooms', [KCPF_Filter_Renderer::class, 'renderBedrooms']);
        add_shortcode('property_filter_bathrooms', [KCPF_Filter_Renderer::class, 'renderBathrooms']);
        add_shortcode('property_filter_type', [KCPF_Filter_Renderer::class, 'renderType']);
        add_shortcode('property_filter_amenities', [KCPF_Filter_Renderer::class, 'renderAmenities']);
        add_shortcode('property_filter_covered_area', [KCPF_Filter_Renderer::class, 'renderCoveredArea']);
        add_shortcode('property_filter_plot_area', [KCPF_Filter_Renderer::class, 'renderPlotArea']);
        add_shortcode('property_filter_id', [KCPF_Filter_Renderer::class, 'renderPropertyId']);
        add_shortcode('property_filters_apply', [KCPF_Filter_Renderer::class, 'renderApplyButton']);
        add_shortcode('property_filters_reset', [KCPF_Filter_Renderer::class, 'renderResetButton']);
        
        // Debug shortcode
        KCPF_Debug_Bedrooms_Bathrooms::register();
    }
    
    /**
     * Register map view shortcodes
     */
    private static function registerMapShortcodes()
    {
        add_shortcode('properties_map', [KCPF_Map_Shortcode::class, 'render']);
    }
}

