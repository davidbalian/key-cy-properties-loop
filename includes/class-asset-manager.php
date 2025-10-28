<?php
/**
 * Asset Manager Class
 * 
 * Handles enqueuing of CSS and JavaScript assets, and critical CSS injection
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Asset_Manager
{
    /**
     * Initialize asset enqueuing
     */
    public static function init()
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueueAssets']);
    }
    
    /**
     * Enqueue CSS and JavaScript
     */
    public static function enqueueAssets()
    {
        self::enqueueStyles();
        self::enqueueScripts();
        self::localizeScripts();
    }
    
    /**
     * Enqueue CSS files
     */
    private static function enqueueStyles()
    {
        // Enqueue noUiSlider CSS
        wp_enqueue_style(
            'nouislider',
            KCPF_ASSETS_URL . 'libs/nouislider.min.css',
            [],
            '15.7.1'
        );
        
        // Enqueue base card styles (must be loaded before specific card styles)
        wp_enqueue_style(
            'kcpf-property-cards-shared',
            KCPF_ASSETS_URL . 'css/property-cards-shared.css',
            ['nouislider'],
            KCPF_VERSION
        );
        
        // Enqueue sale card styles
        wp_enqueue_style(
            'kcpf-property-cards-sale',
            KCPF_ASSETS_URL . 'css/property-cards-sale.css',
            ['kcpf-property-cards-shared'],
            KCPF_VERSION
        );
        
        // Enqueue rent card styles
        wp_enqueue_style(
            'kcpf-property-cards-rent',
            KCPF_ASSETS_URL . 'css/property-cards-rent.css',
            ['kcpf-property-cards-shared'],
            KCPF_VERSION
        );
        
        // Enqueue multi-unit table styles
        wp_enqueue_style(
            'kcpf-multiunit-tables',
            KCPF_ASSETS_URL . 'css/multiunit-tables.css',
            ['kcpf-property-cards-shared'],
            KCPF_VERSION
        );
        
        // Enqueue filter form styles
        wp_enqueue_style(
            'kcpf-filters-form',
            KCPF_ASSETS_URL . 'css/filters-form.css',
            ['nouislider'],
            KCPF_VERSION
        );
        
        // Enqueue responsive styles (must be loaded after all other styles)
        wp_enqueue_style(
            'kcpf-responsive',
            KCPF_ASSETS_URL . 'css/responsive.css',
            ['kcpf-property-cards-shared', 'kcpf-property-cards-sale', 'kcpf-property-cards-rent', 'kcpf-multiunit-tables', 'kcpf-filters-form'],
            KCPF_VERSION
        );
        
        // Enqueue Map View CSS
        wp_enqueue_style(
            'kcpf-map-view',
            KCPF_ASSETS_URL . 'css/map-view.css',
            ['kcpf-responsive'],
            KCPF_VERSION
        );
        
        // Enqueue Favourites CSS
        wp_enqueue_style(
            'kcpf-favourites',
            KCPF_ASSETS_URL . 'css/favourites.css',
            ['kcpf-property-cards-shared'],
            KCPF_VERSION
        );
    }
    
    /**
     * Enqueue JavaScript files
     */
    private static function enqueueScripts()
    {
        // Enqueue Google Maps API if configured
        self::enqueueGoogleMaps();
        
        // Enqueue noUiSlider JavaScript
        wp_enqueue_script(
            'nouislider',
            KCPF_ASSETS_URL . 'libs/nouislider.min.js',
            [],
            '15.7.1',
            true
        );
        
        // Enqueue Filter Manager Modules (in dependency order)
        
        // 1. AJAX Handler (foundation - no dependencies on other modules)
        wp_enqueue_script(
            'kcpf-ajax-handler',
            KCPF_ASSETS_URL . 'js/filters-ajax-handler.js',
            ['jquery'],
            KCPF_VERSION,
            true
        );
        
        // 2. Range Sliders (depends on noUiSlider)
        wp_enqueue_script(
            'kcpf-range-sliders',
            KCPF_ASSETS_URL . 'js/filters-range-sliders.js',
            ['jquery', 'nouislider'],
            KCPF_VERSION,
            true
        );
        
        // 3. Toggle Handler (no special dependencies)
        wp_enqueue_script(
            'kcpf-toggle-handler',
            KCPF_ASSETS_URL . 'js/filters-toggle-handler.js',
            ['jquery'],
            KCPF_VERSION,
            true
        );
        
        // 4. Multiselect Handler (no special dependencies)
        wp_enqueue_script(
            'kcpf-multiselect-handler',
            KCPF_ASSETS_URL . 'js/filters-multiselect-handler.js',
            ['jquery'],
            KCPF_VERSION,
            true
        );
        
        // 5. Load More (no special dependencies)
        wp_enqueue_script(
            'kcpf-load-more',
            KCPF_ASSETS_URL . 'js/filters-load-more.js',
            ['jquery'],
            KCPF_VERSION,
            true
        );

        // 6. Homepage Manager (depends on Range Sliders for refresh)
        wp_enqueue_script(
            'kcpf-homepage-manager',
            KCPF_ASSETS_URL . 'js/filters-homepage-manager.js',
            ['jquery', 'kcpf-range-sliders'],
            KCPF_VERSION,
            true
        );
        
        // 7. Form Manager (depends on AJAX Handler)
        wp_enqueue_script(
            'kcpf-form-manager',
            KCPF_ASSETS_URL . 'js/filters-form-manager.js',
            ['jquery', 'kcpf-ajax-handler'],
            KCPF_VERSION,
            true
        );
        
        // 8. Coordinator (depends on all modules - initializes everything)
        wp_enqueue_script(
            'kcpf-filters-coordinator',
            KCPF_ASSETS_URL . 'js/filters-coordinator.js',
            [
                'jquery',
                'kcpf-ajax-handler',
                'kcpf-range-sliders',
                'kcpf-toggle-handler',
                'kcpf-multiselect-handler',
                'kcpf-load-more',
                'kcpf-homepage-manager',
                'kcpf-form-manager'
            ],
            KCPF_VERSION,
            true
        );
        
        // Enqueue Map View JavaScript
        $map_dependencies = ['jquery', 'kcpf-filters-coordinator'];
        if (KCPF_Settings_Manager::hasApiKey()) {
            $map_dependencies[] = 'google-maps';
        }
        
        wp_enqueue_script(
            'kcpf-map-view',
            KCPF_ASSETS_URL . 'js/map-view.js',
            $map_dependencies,
            KCPF_VERSION,
            true
        );
        
        // Enqueue Single Property Map JavaScript
        $single_map_dependencies = ['jquery'];
        if (KCPF_Settings_Manager::hasApiKey()) {
            $single_map_dependencies[] = 'google-maps';
        }
        
        wp_enqueue_script(
            'kcpf-single-property-map',
            KCPF_ASSETS_URL . 'js/single-property-map.js',
            $single_map_dependencies,
            KCPF_VERSION,
            true
        );
        
        // Enqueue Favourites JavaScript
        wp_enqueue_script(
            'kcpf-favourites-manager',
            KCPF_ASSETS_URL . 'js/favourites-manager.js',
            ['jquery'],
            KCPF_VERSION,
            true
        );
    }
    
    /**
     * Enqueue Google Maps API
     */
    private static function enqueueGoogleMaps()
    {
        if (KCPF_Settings_Manager::hasApiKey()) {
            $api_key = KCPF_Settings_Manager::getApiKey();
            wp_enqueue_script(
                'google-maps',
                'https://maps.googleapis.com/maps/api/js?key=' . urlencode($api_key) . '&callback=kcpfInitMap',
                [],
                null,
                true
            );
        }
    }
    
    /**
     * Localize scripts with data
     */
    private static function localizeScripts()
    {
        // Attach to AJAX handler since that's what uses kcpfData
        wp_localize_script('kcpf-ajax-handler', 'kcpfData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kcpf_filter_nonce')
        ]);
        
        // Localize favourites data
        wp_localize_script('kcpf-favourites-manager', 'kcpfFavouritesData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kcpf_favourites'),
            'isLoggedIn' => is_user_logged_in()
        ]);
    }
    
}

