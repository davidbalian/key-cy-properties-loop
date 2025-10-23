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
        add_action('wp_head', [__CLASS__, 'addCriticalOverrides'], 100);
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
        
        // Enqueue main filters CSS
        wp_enqueue_style(
            'kcpf-filters',
            KCPF_ASSETS_URL . 'css/filters.css',
            ['nouislider'],
            KCPF_VERSION
        );
        
        // Enqueue Map View CSS
        wp_enqueue_style(
            'kcpf-map-view',
            KCPF_ASSETS_URL . 'css/map-view.css',
            ['kcpf-filters'],
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
        
        // Enqueue main filters JavaScript
        wp_enqueue_script(
            'kcpf-filters',
            KCPF_ASSETS_URL . 'js/filters.js',
            ['jquery', 'nouislider'],
            KCPF_VERSION,
            true
        );
        
        // Enqueue Map View JavaScript
        $map_dependencies = ['jquery', 'kcpf-filters'];
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
        wp_localize_script('kcpf-filters', 'kcpfData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kcpf_filter_nonce')
        ]);
    }
    
    /**
     * Add critical CSS overrides to wp_head
     */
    public static function addCriticalOverrides()
    {
        ?>
        <style type="text/css">
            .kcpf-multiselect-trigger,
            .kcpf-filter .kcpf-multiselect-trigger,
            .kcpf-multiselect-dropdown .kcpf-multiselect-trigger {
                background-color: #fff !important;
            }
            .kcpf-filter-select {
                background-color: #fff !important;
            }
            .kcpf-input,
            .kcpf-property-id-input {
                background-color: #fff !important;
            }
            .kcpf-toggle-label span,
            .kcpf-radio-label span,
            .kcpf-button-label span {
                background-color: #fff !important;
            }
            .kcpf-reset-button {
                background-color: #f0f0f0 !important;
            }
            .kcpf-chip {
                background-color: #f0f0f0 !important;
                color: #000 !important;
            }
            .kcpf-chip-remove {
                color: #000 !important;
                font-size: 1rem !important;
            }
            .kcpf-multiselect-dropdown.active .kcpf-multiselect-dropdown-menu {
                display: block !important;
                padding: 0.5rem !important;
            }
            .kcpf-multiselect-option {
                display: flex !important;
                padding: 0.5rem 0.75rem !important;
                margin-bottom: 0.5rem !important;
            }
            .kcpf-placeholder {
                color: #666 !important;
            }
            .kcpf-range-trigger {
                background-color: #fff !important;
            }
            .kcpf-range-dropdown-menu {
                background-color: #fff !important;
            }
            /* Ensure Sale/Rent labels are visible */
            .kcpf-filter-purpose label,
            .kcpf-radio-label,
            .kcpf-toggle-label {
                display: inline-flex !important;
                align-items: center !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            .kcpf-filter-purpose .kcpf-radio-label span,
            .kcpf-filter-purpose .kcpf-toggle-label span {
                display: inline-block !important;
                visibility: visible !important;
            }
            .kcpf-filter-purpose input[type="radio"] {
                display: inline-block !important;
                visibility: visible !important;
            }
            /* Loading state for filter refresh */
            .kcpf-homepage-filters.kcpf-refreshing {
                position: relative;
            }
            .kcpf-homepage-filters.kcpf-refreshing .kcpf-filters-form {
                opacity: 0.5;
                pointer-events: none;
            }
            .kcpf-refresh-spinner {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 1000;
                background: rgba(255, 255, 255, 0.9);
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            .kcpf-spinner {
                border: 3px solid #f3f3f3;
                border-top: 3px solid #3498db;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                animation: kcpf-spin 1s linear infinite;
            }
            @keyframes kcpf-spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            /* Input prefix/suffix styling */
            .kcpf-input-wrapper {
                position: relative;
                display: inline-flex;
                align-items: center;
                flex: 1;
            }
            .kcpf-input-prefix {
                position: absolute;
                left: 12px;
                color: #666;
                font-size: 14px;
                pointer-events: none;
                z-index: 1;
            }
            .kcpf-input-wrapper .kcpf-input {
                padding-left: 28px !important;
            }
            .kcpf-input-suffix {
                position: absolute;
                right: 12px;
                color: #666;
                font-size: 12px;
                pointer-events: none;
                z-index: 1;
            }
            .kcpf-input-wrapper .kcpf-input {
                padding-right: 32px !important;
            }
        </style>
        <?php
    }
}

