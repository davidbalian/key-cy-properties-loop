<?php
/**
 * Debug script to test pagination functionality
 * Access via ?debug_pagination=1 (only for admins).
 */

add_action('wp', 'debug_pagination_test');

function debug_pagination_test() {
    if (!isset($_GET['debug_pagination']) || !current_user_can('manage_options')) {
        return;
    }

    echo '<h1>Pagination Debug Test</h1>';

    // Test different pages
    $pages_to_test = [1, 2, 3, 4, 5];
    $posts_per_page = 10;

    echo '<h2>Testing Query Handler with different pages</h2>';
    echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
    echo '<thead><tr><th>Page</th><th>Query Args</th><th>Found Posts</th><th>Max Pages</th></tr></thead>';
    echo '<tbody>';

    foreach ($pages_to_test as $page) {
        // Simulate GET params
        $_GET['paged'] = $page;
        $_GET['posts_per_page'] = $posts_per_page;

        $attrs = [
            'purpose' => 'sale',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
        ];

        $query_args = KCPF_Query_Handler::buildQueryArgs($attrs);
        $query = new WP_Query($query_args);

        echo '<tr>';
        echo '<td>' . esc_html($page) . '</td>';
        echo '<td><pre style="font-size: 10px; max-width: 300px; overflow: auto;">' . esc_html(print_r($query_args, true)) . '</pre></td>';
        echo '<td>' . esc_html($query->found_posts) . '</td>';
        echo '<td>' . esc_html($query->max_num_pages) . '</td>';
        echo '</tr>';

        wp_reset_postdata();
    }

    echo '</tbody></table>';

    echo '<h2>Testing AJAX Handler Simulation</h2>';
    echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
    echo '<thead><tr><th>Page</th><th>AJAX Response HTML Length</th><th>Contains Grid</th><th>Data Attributes</th></tr></thead>';
    echo '<tbody>';

    foreach ($pages_to_test as $page) {
        // Simulate AJAX request
        $_GET['action'] = 'kcpf_load_properties';
        $_GET['paged'] = $page;
        $_GET['purpose'] = 'sale';
        $_GET['posts_per_page'] = $posts_per_page;

        try {
            ob_start();
            KCPF_Ajax_Manager::ajaxLoadProperties();
            $response = ob_get_clean();

            // Parse JSON response
            $json_response = json_decode($response, true);
            $html = $json_response['data']['html'] ?? '';

            // Check if HTML contains grid
            $has_grid = strpos($html, 'kcpf-properties-grid') !== false;

            // Extract data attributes
            preg_match('/data-current-page="([^"]*)"/', $html, $current_page_match);
            preg_match('/data-max-pages="([^"]*)"/', $html, $max_pages_match);

            $current_page = $current_page_match[1] ?? 'not found';
            $max_pages = $max_pages_match[1] ?? 'not found';

            echo '<tr>';
            echo '<td>' . esc_html($page) . '</td>';
            echo '<td>' . esc_html(strlen($html)) . ' chars</td>';
            echo '<td>' . esc_html($has_grid ? 'Yes' : 'No') . '</td>';
            echo '<td>Current: ' . esc_html($current_page) . ', Max: ' . esc_html($max_pages) . '</td>';
            echo '</tr>';

        } catch (Exception $e) {
            echo '<tr>';
            echo '<td>' . esc_html($page) . '</td>';
            echo '<td colspan="3">Error: ' . esc_html($e->getMessage()) . '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody></table>';

    wp_reset_postdata();
    exit; // Prevent loading the rest of the page
}
