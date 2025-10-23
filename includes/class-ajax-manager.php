<?php
/**
 * AJAX Manager Class
 * 
 * Handles registration and execution of all AJAX handlers
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Ajax_Manager
{
    /**
     * Register all AJAX handlers
     */
    public static function register()
    {
        self::registerPropertiesLoopHandlers();
        self::registerMapViewHandlers();
        self::registerFilterHandlers();
        self::registerTestHandlers();
    }
    
    /**
     * Register properties loop AJAX handlers
     */
    private static function registerPropertiesLoopHandlers()
    {
        add_action('wp_ajax_kcpf_load_properties', [__CLASS__, 'ajaxLoadProperties']);
        add_action('wp_ajax_nopriv_kcpf_load_properties', [__CLASS__, 'ajaxLoadProperties']);
    }
    
    /**
     * Register map view AJAX handlers
     */
    private static function registerMapViewHandlers()
    {
        add_action('wp_ajax_kcpf_load_map_properties', [KCPF_Map_Shortcode::class, 'ajaxLoadMapProperties']);
        add_action('wp_ajax_nopriv_kcpf_load_map_properties', [KCPF_Map_Shortcode::class, 'ajaxLoadMapProperties']);
        
        add_action('wp_ajax_kcpf_get_property_card', [KCPF_Map_Shortcode::class, 'ajaxGetPropertyCard']);
        add_action('wp_ajax_nopriv_kcpf_get_property_card', [KCPF_Map_Shortcode::class, 'ajaxGetPropertyCard']);
    }
    
    /**
     * Register filter AJAX handlers
     */
    private static function registerFilterHandlers()
    {
        KCPF_Filters_Ajax::register();
    }
    
    /**
     * Register test AJAX handlers
     */
    private static function registerTestHandlers()
    {
        add_action('wp_ajax_kcpf_test', [__CLASS__, 'ajaxTest']);
        add_action('wp_ajax_nopriv_kcpf_test', [__CLASS__, 'ajaxTest']);
    }
    
    /**
     * Simple AJAX test endpoint
     */
    public static function ajaxTest()
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
    public static function ajaxLoadProperties()
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
            
            // Specifically log bedroom and bathroom filters
            if (isset($_GET['bedrooms'])) {
                error_log('[KCPF] Bedrooms filter received: ' . print_r($_GET['bedrooms'], true));
                error_log('[KCPF] Bedrooms filter type: ' . gettype($_GET['bedrooms']));
                error_log('[KCPF] Bedrooms filter is_array: ' . (is_array($_GET['bedrooms']) ? 'true' : 'false'));
            }
            
            if (isset($_GET['bathrooms'])) {
                error_log('[KCPF] Bathrooms filter received: ' . print_r($_GET['bathrooms'], true));
                error_log('[KCPF] Bathrooms filter type: ' . gettype($_GET['bathrooms']));
                error_log('[KCPF] Bathrooms filter is_array: ' . (is_array($_GET['bathrooms']) ? 'true' : 'false'));
            }
            
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
}

