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
     * Detect the purpose from properties_loop shortcodes on the current page
     *
     * @return string Detected purpose ('sale', 'rent', or 'sale' as default)
     */
    private static function detectPagePurpose()
    {
        // Only detect on singular pages/posts
        if (!is_singular()) {
            return 'sale';
        }

        global $post;
        if (!$post || !isset($post->post_content)) {
            return 'sale';
        }

        $content = $post->post_content;

        // Look for properties_loop shortcode with purpose attribute
        if (preg_match('/\[properties_loop[^]]*purpose\s*=\s*["\']([^"\']+)["\'][^\]]*\]/i', $content, $matches)) {
            $purpose = strtolower(trim($matches[1]));
            if (in_array($purpose, ['sale', 'rent'])) {
                return $purpose;
            }
        }

        // Look for properties_loop shortcode without purpose (defaults to sale)
        if (strpos($content, '[properties_loop') !== false) {
            return 'sale';
        }

        return 'sale';
    }

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
            // Get purpose from request or detect from page content
            $purpose = isset($_GET['purpose']) ? sanitize_text_field($_GET['purpose']) : null;
            if (!$purpose) {
                $purpose = self::detectPagePurpose();
            }

            // Get attributes from AJAX request - pass all filter parameters
            $attrs = [
                'purpose' => $purpose ?: 'sale',
                'posts_per_page' => isset($_GET['posts_per_page']) ? intval($_GET['posts_per_page']) : 10,
                'paged' => isset($_GET['paged']) ? intval($_GET['paged']) : 1,
            ];
            
            // Note: All other filter parameters are read from $_GET by URL_Manager
            // No need to explicitly pass them here as they're accessed via getCurrentFilters()
            
            
            // Render properties loop
            $html = KCPF_Loop_Renderer::render($attrs);
            
            
            // Return JSON response
            wp_send_json_success([
                'html' => $html,
            ]);
            
            // Exit is not needed as wp_send_json_success already exits
        } catch (Exception $e) {
            // Ensure clean output buffer before sending error
            @ob_clean();

            // Return error response
            wp_send_json_error([
                'message' => 'Error loading properties',
                'error' => $e->getMessage(),
            ]);
        } catch (Error $e) {
            // Catch fatal errors too
            @ob_clean();

            wp_send_json_error([
                'message' => 'Fatal error loading properties',
                'error' => $e->getMessage(),
            ]);
        }
    }
}

