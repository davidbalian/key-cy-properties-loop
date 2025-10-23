<?php
/**
 * Plugin Loader Class
 * 
 * Handles loading of all plugin dependencies
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Plugin_Loader
{
    /**
     * Load all plugin dependencies
     */
    public static function loadDependencies()
    {
        self::loadCoreClasses();
        self::loadFilterRenderers();
        self::loadFilterClasses();
        self::loadMapViewClasses();
    }
    
    /**
     * Load core classes
     */
    private static function loadCoreClasses()
    {
        require_once KCPF_INCLUDES_DIR . 'class-field-config.php';
        require_once KCPF_INCLUDES_DIR . 'class-glossary-handler.php';
        require_once KCPF_INCLUDES_DIR . 'class-url-manager.php';
        require_once KCPF_INCLUDES_DIR . 'class-multiunit-query-builder.php';
        require_once KCPF_INCLUDES_DIR . 'class-query-handler.php';
        require_once KCPF_INCLUDES_DIR . 'class-card-data-helper.php';
        require_once KCPF_INCLUDES_DIR . 'class-rent-card-view.php';
        require_once KCPF_INCLUDES_DIR . 'class-loop-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-listing-values.php';
        require_once KCPF_INCLUDES_DIR . 'class-debug-viewer.php';
    }
    
    /**
     * Load filter renderer classes
     */
    private static function loadFilterRenderers()
    {
        // Load base class first
        require_once KCPF_INCLUDES_DIR . 'class-filter-renderer-base.php';
        
        // Load specific filter renderers
        require_once KCPF_INCLUDES_DIR . 'class-location-filter-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-purpose-filter-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-price-filter-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-bedrooms-filter-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-bathrooms-filter-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-type-filter-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-area-filter-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-amenities-filter-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-misc-filter-renderer.php';
        
        // Load facade last
        require_once KCPF_INCLUDES_DIR . 'class-filter-renderer.php';
    }
    
    /**
     * Load filter-related classes
     */
    private static function loadFilterClasses()
    {
        require_once KCPF_INCLUDES_DIR . 'class-homepage-filters.php';
        require_once KCPF_INCLUDES_DIR . 'class-filters-ajax.php';
    }
    
    /**
     * Load map view classes
     */
    private static function loadMapViewClasses()
    {
        require_once KCPF_INCLUDES_DIR . 'class-settings-manager.php';
        require_once KCPF_INCLUDES_DIR . 'class-map-filters.php';
        require_once KCPF_INCLUDES_DIR . 'class-map-card-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-map-shortcode.php';
    }
}

