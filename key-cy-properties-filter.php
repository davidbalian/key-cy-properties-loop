<?php
/**
 * Plugin Name: Key CY Properties Filters and Loops
 * Plugin URI: https://balian.cy
 * Description: Custom property filtering system with individual shortcodes for filters and properties loop
 * Version: 2.4.1
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
define('KCPF_VERSION', '2.4.1');
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
        require_once KCPF_INCLUDES_DIR . 'class-multiunit-query-builder.php';
        require_once KCPF_INCLUDES_DIR . 'class-query-handler.php';
        require_once KCPF_INCLUDES_DIR . 'class-card-data-helper.php';
        require_once KCPF_INCLUDES_DIR . 'class-rent-card-view.php';
        require_once KCPF_INCLUDES_DIR . 'class-loop-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-listing-values.php';
        require_once KCPF_INCLUDES_DIR . 'class-filter-renderer.php';
        require_once KCPF_INCLUDES_DIR . 'class-homepage-filters.php';
        require_once KCPF_INCLUDES_DIR . 'class-filters-ajax.php';
        require_once KCPF_INCLUDES_DIR . 'class-debug-viewer.php';
        
        // Style Editor classes - Disabled
        // require_once KCPF_INCLUDES_DIR . 'class-style-settings-manager.php';
        // require_once KCPF_INCLUDES_DIR . 'class-css-generator.php';
        // require_once KCPF_INCLUDES_DIR . 'class-style-preview.php';
        // require_once KCPF_INCLUDES_DIR . 'class-style-editor.php';
    }
    
    /**
     * Initialize plugin components
     */
    private function initializePlugin()
    {
        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        
        // Add critical CSS overrides
        add_action('wp_head', [$this, 'addCriticalOverrides'], 100);
        
        // Register shortcodes
        $this->registerShortcodes();
        
        // Register AJAX handlers
        $this->registerAjaxHandlers();
        
        // Initialize admin features
        if (is_admin()) {
            KCPF_Debug_Viewer::init();
            // Style Editor disabled
            // KCPF_Style_Editor::init();
        }
    }
    
    /**
     * Register AJAX handlers
     */
    private function registerAjaxHandlers()
    {
        // For logged-in users
        add_action('wp_ajax_kcpf_load_properties', [$this, 'ajaxLoadProperties']);
        add_action('wp_ajax_kcpf_test', [$this, 'ajaxTest']);
        KCPF_Filters_Ajax::register();
        
        // For non-logged-in users
        add_action('wp_ajax_nopriv_kcpf_load_properties', [$this, 'ajaxLoadProperties']);
        add_action('wp_ajax_nopriv_kcpf_test', [$this, 'ajaxTest']);
        KCPF_Filters_Ajax::register();
    }
    
    /**
     * Simple AJAX test endpoint
     */
    public function ajaxTest()
    {
        wp_send_json_success([
            'message' => 'AJAX is working!',
            'received_params' => $_GET,
            'timestamp' => current_time('mysql'),
        ]);
    }
    
    /**
     * AJAX handler to load filtered properties
     */
    public function ajaxLoadProperties()
    {
        // Set a time limit to prevent hanging
        set_time_limit(60);
        
        // Ensure we have a clean output buffer
        @ob_clean();
        
        try {
            // Log the start of the request
            error_log('[KCPF] ============================================');
            error_log('[KCPF] AJAX request started');
            error_log('[KCPF] Total $_GET parameters: ' . count($_GET));
            error_log('[KCPF] $_GET contents: ' . print_r($_GET, true));
            
            // Log all filter parameters being received
            $receivedFilters = [];
            foreach ($_GET as $key => $value) {
                if ($key !== 'action') {
                    $receivedFilters[$key] = $value;
                }
            }
            error_log('[KCPF] Filter parameters received: ' . print_r($receivedFilters, true));
            
            // Get attributes from AJAX request - pass all filter parameters
            $attrs = [
                'purpose' => isset($_GET['purpose']) ? sanitize_text_field($_GET['purpose']) : 'sale',
                'posts_per_page' => isset($_GET['posts_per_page']) ? intval($_GET['posts_per_page']) : 10,
            ];
            
            // Note: All other filter parameters are read from $_GET by URL_Manager
            // No need to explicitly pass them here as they're accessed via getCurrentFilters()
            
            error_log('[KCPF] Calling render with attrs: ' . print_r($attrs, true));
            
            // Render properties loop
            $html = KCPF_Loop_Renderer::render($attrs);
            
            error_log('[KCPF] Render completed, HTML length: ' . strlen($html));
            
            // Return JSON response
            wp_send_json_success([
                'html' => $html,
            ]);
            
            // Exit is not needed as wp_send_json_success already exits
        } catch (Exception $e) {
            // Log error with full details
            error_log('[KCPF] AJAX Exception: ' . $e->getMessage());
            error_log('[KCPF] AJAX Trace: ' . $e->getTraceAsString());
            
            // Ensure clean output buffer before sending error
            @ob_clean();
            
            // Return error response
            wp_send_json_error([
                'message' => 'Error loading properties',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } catch (Error $e) {
            // Catch fatal errors too
            error_log('[KCPF] AJAX Fatal Error: ' . $e->getMessage());
            error_log('[KCPF] AJAX Trace: ' . $e->getTraceAsString());
            
            // Ensure clean output buffer before sending error
            @ob_clean();
            
            wp_send_json_error([
                'message' => 'Fatal error loading properties',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
        // Homepage composite
        add_shortcode('homepage_filters', [KCPF_Homepage_Filters::class, 'render']);
        
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
     * Add critical CSS overrides
     */
    public function addCriticalOverrides()
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
        </style>
        <?php
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
        
        // Dynamic CSS generation disabled
        // if (class_exists('KCPF_CSS_Generator')) {
        //     try {
        //         $custom_css = KCPF_CSS_Generator::generate();
        //         if (!empty($custom_css)) {
        //             wp_add_inline_style('kcpf-filters', $custom_css);
        //         }
        //     } catch (Exception $e) {
        //         error_log('KCPF Style Editor Error: ' . $e->getMessage());
        //     }
        // }
        
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

