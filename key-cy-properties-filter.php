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
define('KCPF_VERSION', '2.4.2');
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

// Standalone debug page (no AJAX interference)
add_action('init', 'kcpf_standalone_debug_page');
function kcpf_standalone_debug_page() {
    if (isset($_GET['kcpf_debug']) && $_GET['kcpf_debug'] === '1') {
        if (!current_user_can('manage_options')) {
            wp_die('Admin access required');
        }

        // Prevent any redirects or AJAX interference
        define('KCPF_DEBUG_MODE', true);

        // Output debug page directly
        kcpf_render_debug_page();
        exit;
    }
}

function kcpf_render_debug_page() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Key CY Properties Filter Debug</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .debug-container { max-width: 1200px; margin: 0 auto; }
            .debug-section { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .debug-title { color: #333; border-bottom: 3px solid #007cba; padding-bottom: 10px; }
            .debug-content { font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.5; }
            .sample-property { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #28a745; }
            .query-pattern { background: #fff3cd; padding: 10px; margin: 5px 0; border-left: 4px solid #ffc107; }
            .no-match { background: #f8d7da; padding: 10px; margin: 5px 0; border-left: 4px solid #dc3545; }
            code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-family: 'Courier New', monospace; }
            .success { color: #28a745; }
            .warning { color: #ffc107; }
            .danger { color: #dc3545; }
        </style>
    </head>
    <body>
        <div class="debug-container">
            <h1>🔍 Key CY Properties Filter Debug</h1>
            <p>This page shows bedroom/bathroom data format and query analysis without AJAX interference.</p>

            <?php
            // Get sample properties with bedroom data
            $args = [
                'post_type' => 'properties',
                'posts_per_page' => 10,
                'post_status' => 'publish',
                'tax_query' => [
                    [
                        'taxonomy' => 'purpose',
                        'field' => 'slug',
                        'terms' => 'sale'
                    ]
                ],
                'meta_query' => [
                    [
                        'key' => 'bedrooms',
                        'compare' => 'EXISTS'
                    ]
                ]
            ];

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                echo '<div class="debug-section">';
                echo '<h2 class="debug-title">📊 Sample Properties Analysis</h2>';
                echo '<p><strong>Found ' . $query->found_posts . ' sale properties with bedroom data</strong></p>';

                while ($query->have_posts()) {
                    $query->the_post();
                    $property_id = get_the_ID();

                    // Get both unserialized (default) and raw serialized data
                    $bedrooms_unserialized = get_post_meta($property_id, 'bedrooms', true);
                    $bedrooms_raw = get_post_meta($property_id, 'bedrooms', false);

                    echo '<div class="sample-property">';
                    echo '<h3>' . get_the_title() . ' (ID: ' . $property_id . ')</h3>';

                    if (is_array($bedrooms_unserialized)) {
                        echo '<p><strong>Unserialized data:</strong> <code>' . esc_html(print_r($bedrooms_unserialized, true)) . '</code></p>';
                        echo '<p><strong>Data type:</strong> array (unserialized)</p>';

                        // Show which bedrooms are true
                        echo '<p><strong>Bedrooms set to true:</strong> ';
                        $true_bedrooms = [];
                        foreach ($bedrooms_unserialized as $bedroom_num => $value) {
                            if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
                                $true_bedrooms[] = $bedroom_num;
                            }
                        }
                        echo '<span class="success">' . implode(', ', $true_bedrooms) . '</span></p>';
                    } else {
                        echo '<p><strong>Raw bedroom data:</strong> <code>' . esc_html($bedrooms_unserialized) . '</code></p>';
                        echo '<p><strong>Data type:</strong> ' . gettype($bedrooms_unserialized) . '</p>';
                    }

                    // Get raw database value to see serialized format
                    global $wpdb;
                    $raw_db_value = $wpdb->get_var($wpdb->prepare(
                        "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s",
                        $property_id, 'bedrooms'
                    ));

                    if ($raw_db_value) {
                        echo '<p><strong>Raw database value:</strong> <code>' . esc_html($raw_db_value) . '</code></p>';

                        // Test PHP array serialization patterns
                        $array_patterns = [
                            'i:5;b:1' => 'PHP array: i:5;b:1 (bedroom 5 = true)',
                            'i:5;s:4:"true"' => 'PHP array: i:5;s:4:"true" (bedroom 5 = "true")',
                            'i:2;b:1' => 'PHP array: i:2;b:1 (bedroom 2 = true)',
                            'i:3;b:1' => 'PHP array: i:3;b:1 (bedroom 3 = true)',
                            'i:4;b:1' => 'PHP array: i:4;b:1 (bedroom 4 = true)'
                        ];

                        echo '<div class="query-pattern">';
                        echo '<p><strong>PHP Array Pattern Tests:</strong></p>';
                        foreach ($array_patterns as $pattern => $description) {
                            $matches = strpos($raw_db_value, $pattern) !== false;
                            $class = $matches ? 'success' : 'danger';
                            echo '<p class="' . $class . '">✅ ' . $description . ': <strong>' . ($matches ? 'MATCH' : 'NO MATCH') . '</strong></p>';
                        }
                        echo '</div>';
                    }

                    echo '</div>';
                }
                wp_reset_postdata();

                echo '</div>';

                // Test actual queries
                echo '<div class="debug-section">';
                echo '<h2 class="debug-title">🔍 Query Testing</h2>';
                echo '<p><strong>Testing different query approaches:</strong></p>';

                $test_values = ['2', '3', '4', '5'];
                foreach ($test_values as $test_bedroom) {
                    echo '<h3>Testing bedroom value: ' . $test_bedroom . '</h3>';

                    // Test multiple query formats
                    $query_formats = [
                        'PHP Array Boolean' => ['key' => 'bedrooms', 'value' => 'i:' . $test_bedroom . ';b:1', 'compare' => 'LIKE'],
                        'PHP Array String' => ['key' => 'bedrooms', 'value' => 'i:' . $test_bedroom . ';s:4:"true"', 'compare' => 'LIKE'],
                        'JSON format' => ['key' => 'bedrooms', 'value' => '"' . $test_bedroom . '":"true"', 'compare' => 'LIKE'],
                        'JSON with spaces' => ['key' => 'bedrooms', 'value' => '"' . $test_bedroom . '": "true"', 'compare' => 'LIKE'],
                        'Boolean format' => ['key' => 'bedrooms', 'value' => '"' . $test_bedroom . '":true', 'compare' => 'LIKE']
                    ];

                    foreach ($query_formats as $format_name => $meta_query) {
                        $test_args = [
                            'post_type' => 'properties',
                            'posts_per_page' => -1,
                            'post_status' => 'publish',
                            'tax_query' => [
                                [
                                    'taxonomy' => 'purpose',
                                    'field' => 'slug',
                                    'terms' => 'sale'
                                ]
                            ],
                            'meta_query' => [$meta_query]
                        ];

                        $test_query = new WP_Query($test_args);
                        echo '<p><strong>' . $format_name . ':</strong> ' . $test_query->found_posts . ' properties found</p>';

                        if ($test_query->found_posts > 0) {
                            echo '<div style="background: #d4edda; padding: 10px; margin: 5px 0; border-left: 4px solid #28a745;">';
                            echo '<strong>✅ SUCCESS! This query format works:</strong><br>';
                            echo 'Query: <code>' . esc_html(json_encode($meta_query)) . '</code>';
                            echo '</div>';
                        }
                        wp_reset_postdata();
                    }
                }

                echo '</div>';
            } else {
                echo '<div class="debug-section">';
                echo '<h2 class="debug-title">❌ No Properties Found</h2>';
                echo '<p>No sale properties found with bedroom data. This might indicate an issue with the data or the query.</p>';
                echo '</div>';
            }

            // Show current query builder output
            echo '<div class="debug-section">';
            echo '<h2 class="debug-title">🔧 Current Query Builder Output</h2>';
            echo '<p><strong>Testing the current bedroom query builder:</strong></p>';

            $test_filters = [
                'bedrooms' => ['5'],
                'purpose' => 'sale'
            ];

            $bedroom_query = KCPF_MultiUnit_Query_Builder::buildBedroomsQuery($test_filters, 'sale');
            echo '<p><strong>Generated query:</strong></p>';
            echo '<pre>' . esc_html(print_r($bedroom_query, true)) . '</pre>';

            if (!empty($bedroom_query)) {
                $test_args = [
                    'post_type' => 'properties',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'tax_query' => [
                        [
                            'taxonomy' => 'purpose',
                            'field' => 'slug',
                            'terms' => 'sale'
                        ]
                    ],
                    'meta_query' => [$bedroom_query]
                ];

                $test_query = new WP_Query($test_args);
                echo '<p><strong>Query result:</strong> ' . $test_query->found_posts . ' properties found</p>';
                wp_reset_postdata();

                if ($test_query->found_posts > 0) {
                    echo '<div style="background: #d4edda; padding: 10px; margin: 5px 0; border-left: 4px solid #28a745;">';
                    echo '<strong>✅ SUCCESS! The current query builder works!</strong>';
                    echo '</div>';
                } else {
                    echo '<div style="background: #f8d7da; padding: 10px; margin: 5px 0; border-left: 4px solid #dc3545;">';
                    echo '<strong>❌ The current query builder is not working.</strong>';
                    echo '</div>';
                }
            }

            echo '</div>';
            ?>

            <div class="debug-section">
                <h2 class="debug-title">📋 Usage Instructions</h2>
                <p><strong>Current URL:</strong> <code><?php echo esc_html(add_query_arg([])); ?></code></p>
                <p><strong>To test filters:</strong> Add bedroom/bathroom parameters to the URL above</p>
                <p><strong>Example:</strong> <code><?php echo esc_html(add_query_arg(['bedrooms' => '5'])); ?></code></p>
                <p><strong>Multiple values:</strong> <code><?php echo esc_html(add_query_arg(['bedrooms' => '2,3,5'])); ?></code></p>
            </div>

            <p><a href="<?php echo esc_url(home_url('/properties/')); ?>" style="color: #007cba;">← Back to Properties</a></p>
        </div>
    </body>
    </html>
    <?php
}
