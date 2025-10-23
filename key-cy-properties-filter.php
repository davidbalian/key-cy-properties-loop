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
        $this->loadManagerClasses();
        $this->initializePlugin();
    }
    
    /**
     * Load manager classes
     */
    private function loadManagerClasses()
    {
        require_once KCPF_INCLUDES_DIR . 'class-plugin-loader.php';
        require_once KCPF_INCLUDES_DIR . 'class-shortcode-manager.php';
        require_once KCPF_INCLUDES_DIR . 'class-ajax-manager.php';
        require_once KCPF_INCLUDES_DIR . 'class-asset-manager.php';
    }
    
    /**
     * Initialize plugin components
     */
    private function initializePlugin()
    {
        // Load all plugin dependencies
        KCPF_Plugin_Loader::loadDependencies();
        
        // Initialize asset manager
        KCPF_Asset_Manager::init();
        
        // Register shortcodes
        KCPF_Shortcode_Manager::register();
        
        // Register AJAX handlers
        KCPF_Ajax_Manager::register();
        
        // Initialize admin features
        if (is_admin()) {
            KCPF_Debug_Viewer::init();
            KCPF_Settings_Manager::init();
        }
    }
}

// Initialize plugin
add_action('plugins_loaded', function() {
    Key_CY_Properties_Filter::getInstance();
});
