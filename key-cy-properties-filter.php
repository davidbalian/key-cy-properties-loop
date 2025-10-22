<?php
/**
 * Plugin Name: Key CY Properties Filters and Loops
 * Plugin URI: https://balian.cy
 * Description: Custom property filtering system with individual shortcodes for filters and properties loop
 * Version: 2.3.0
 * Author: balian.cy
 * Author URI: https://balian.cy
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: key-cy-properties-filter
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KCPF_VERSION', '2.3.0');
define('KCPF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KCPF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KCPF_INCLUDES_DIR', KCPF_PLUGIN_DIR . 'includes/');
define('KCPF_ASSETS_URL', KCPF_PLUGIN_URL . 'assets/');

/**
 * Main plugin class
 */
class Key_CY_Properties_Filter
{
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct()
    {
        $this->loadDependencies();
        $this->initializePlugin();
    }
    
    /**
     * Load required files
     */
    private function loadDependencies()
    {
        require_once KCPF_INCLUDES_DIR . 'class-field-config.php';
        require_once KCPF_INCLUDES_DIR . 'class-glossary-handler.php';
        require_once KCPF_INCLUDES_DIR . 'class-url-manager.php';
        require_once KCPF_INCLUDES_DIR . 'class-query-handler.php';
        require_once KCPF_INCLUDES_DIR . 'class-loop-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-filter-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-debug-viewer.php';
    }
    
    /**
     * Initialize plugin components
     */
    private function initializePlugin()
    {
        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        
        // Register shortcodes
        $this->registerShortcodes();
        
        // Register AJAX handlers
        $this->registerAjaxHandlers();
        
        // Initialize debug viewer (admin only)
        if (is_admin()) {
            KCPF_Debug_Viewer::init();
        }
    }
    
    /**
     * Register AJAX handlers
     */
    private function registerAjaxHandlers()
    {
        // For logged-in users
        add_action('wp_ajax_kcpf_load_properties', [$this, 'ajaxLoadProperties']);
        
        // For non-logged-in users
        add_action('wp_ajax_nopriv_kcpf_load_properties', [$this, 'ajaxLoadProperties']);
    }
    
    /**
     * AJAX handler to load filtered properties
     */
    public function ajaxLoadProperties()
    {
        try {
            // Get attributes from AJAX request
            $attrs = [
                'purpose' => isset($_GET['purpose']) ? sanitize_text_field($_GET['purpose']) : 'sale',
                'posts_per_page' => isset($_GET['posts_per_page']) ? intval($_GET['posts_per_page']) : 10,
            ];
            
            // Render properties loop
            $html = KCPF_Loop_Renderer::render($attrs);
            
            // Return JSON response
            wp_send_json_success([
                'html' => $html,
            ]);
        } catch (Exception $e) {
            // Log error
            error_log('KCPF AJAX Error: ' . $e->getMessage());
            
            // Return error response
            wp_send_json_error([
                'message' => 'Error loading properties',
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Register all shortcodes
     */
    private function registerShortcodes()
    {
        // Properties loop
        add_shortcode('properties_loop', [KCPF_Loop_Renderer::class, 'render']);
        
        // Filter shortcodes
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
    }
    
    /**
     * Enqueue CSS and JavaScript
     */
    public function enqueueAssets()
    {
        // Enqueue noUiSlider CSS
        wp_enqueue_style(
            'nouislider',
            KCPF_ASSETS_URL . 'libs/nouislider.min.css',
            [],
            '15.7.1'
        );
        
        // Enqueue CSS
        wp_enqueue_style(
            'kcpf-filters',
            KCPF_ASSETS_URL . 'css/filters.css',
            ['nouislider'],
            KCPF_VERSION
        );
        
        // Enqueue noUiSlider JavaScript
        wp_enqueue_script(
            'nouislider',
            KCPF_ASSETS_URL . 'libs/nouislider.min.js',
            [],
            '15.7.1',
            true
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'kcpf-filters',
            KCPF_ASSETS_URL . 'js/filters.js',
            ['jquery', 'nouislider'],
            KCPF_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('kcpf-filters', 'kcpfData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kcpf_filter_nonce')
        ]);
    }
}

// Initialize plugin
add_action('plugins_loaded', function() {
    Key_CY_Properties_Filter::getInstance();
});

