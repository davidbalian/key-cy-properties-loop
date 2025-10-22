/**
 * JetEngine Glossaries Inspector - Simple Version
 * 
 * Add this to a snippet plugin (Code Snippets, WPCode, etc.)
 * Access via: yoursite.com/?check_glossaries=1
 */

add_action('template_redirect', 'check_jetengine_glossaries');

function check_jetengine_glossaries() {
    if (!isset($_GET['check_glossaries'])) {
        return;
    }
    
    if (!function_exists('jet_engine')) {
        die('JetEngine is not active.');
    }
    
    echo '<h1>JetEngine Glossaries</h1>';
    echo '<style>body { font-family: monospace; padding: 20px; line-height: 1.6; } pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; } code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }</style>';
    
    // Get glossaries from settings
    $glossaries_data = jet_engine()->glossaries->data->get_item_for_register();
    
    if (empty($glossaries_data)) {
        echo '<p>No glossaries found.</p>';
        echo '<p>Make sure you have created glossaries in JetEngine → Glossaries.</p>';
    } else {
        echo '<p>Found <strong>' . count($glossaries_data) . '</strong> glossaries:</p>';
        
        foreach ($glossaries_data as $glossary) {
            $glossary_id = isset($glossary['id']) ? $glossary['id'] : '';
            $glossary_name = isset($glossary['name']) ? $glossary['name'] : 'Untitled';
            
            echo '<hr>';
            echo '<h2>' . esc_html($glossary_name) . '</h2>';
            echo '<p><strong>ID:</strong> <code>' . esc_html($glossary_id) . '</code></p>';
            
            // Get glossary options using the correct method
            $options = jet_engine()->glossaries->filters->get_glossary_options($glossary_id);
            
            if (!empty($options)) {
                echo '<p><strong>Options (' . count($options) . '):</strong></p>';
                echo '<ul>';
                foreach ($options as $value => $label) {
                    echo '<li><strong>Value:</strong> <code>' . esc_html($value) . '</code> → <strong>Label:</strong> ' . esc_html($label) . '</li>';
                }
                echo '</ul>';
                
                echo '<details><summary>Raw Array (value => label)</summary><pre>';
                print_r($options);
                echo '</pre></details>';
            } else {
                echo '<p><em>No options in this glossary.</em></p>';
            }
            
            echo '<details><summary>Full Glossary Data</summary><pre>';
            print_r($glossary);
            echo '</pre></details>';
        }
    }
    
    echo '<hr><h2>Usage Example:</h2>';
    echo '<pre>';
    echo '// Get options for a specific glossary' . "\n";
    echo '$options = jet_engine()->glossaries->filters->get_glossary_options( \'glossary_id_here\' );' . "\n\n";
    echo '// Returns: array( \'value1\' => \'Label 1\', \'value2\' => \'Label 2\' )' . "\n\n";
    echo '// Loop through options' . "\n";
    echo 'foreach ($options as $value => $label) {' . "\n";
    echo '    echo $value;  // The stored value' . "\n";
    echo '    echo $label;  // The display label' . "\n";
    echo '}';
    echo '</pre>';
    
    exit;
}
