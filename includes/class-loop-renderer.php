<?php
/**
 * Loop Renderer Class
 *
 * Renders the properties loop with filtered results
 *
 * DEBUG MODE: Add ?debug_filters=1 to any URL to see comprehensive bedroom/bathroom filter debugging
 * Shows: current filters, database data format, query patterns, and test results
 *
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Loop_Renderer
{
    /**
     * Render properties loop shortcode
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function render($attrs)
    {
        $attrs = shortcode_atts([
            'purpose' => 'sale',
            'posts_per_page' => 10,
        ], $attrs, 'properties_loop');
        
        // Build query
        $query_args = KCPF_Query_Handler::buildQueryArgs($attrs);
        $query = new WP_Query($query_args);
        
        ob_start();
        
        // Add purpose data attribute for identification
        $purpose_attr = isset($attrs['purpose']) ? $attrs['purpose'] : 'sale';
        echo '<div class="kcpf-properties-loop" id="kcpf-properties-loop" data-purpose="' . esc_attr($purpose_attr) . '">';
        
        if ($query->have_posts()) {
            // Determine grid class based on purpose
            $purpose = isset($attrs['purpose']) ? $attrs['purpose'] : 'sale';
            if ($purpose === 'sale') {
                $gridClass = 'kcpf-properties-grid kcpf-grid-sale';
            } elseif ($purpose === 'rent') {
                $gridClass = 'kcpf-properties-grid kcpf-grid-rent';
            } else {
                $gridClass = 'kcpf-properties-grid';
            }
            
            // Get current page info for infinite scroll
            $current_page = !empty($query->query_vars['paged']) ? intval($query->query_vars['paged']) : 1;
            $max_pages = $query->max_num_pages;
            
            echo '<div class="' . esc_attr($gridClass) . '" data-current-page="' . esc_attr($current_page) . '" data-max-pages="' . esc_attr($max_pages) . '">';

            // Show debug info if requested, even when results are found
            if (defined('WP_DEBUG') && WP_DEBUG || (isset($_REQUEST['debug_filters']) && $_REQUEST['debug_filters'] === '1')) {
                echo '<div style="background: #d4edda; padding: 15px; margin: 20px 0; border: 2px solid #28a745; font-family: monospace; font-size: 12px;">';
                echo '<h4 style="margin-top: 0; color: #155724;">✅ PROPERTIES FOUND - DEBUG INFO</h4>';
                echo '<p><strong>Found ' . $query->found_posts . ' properties matching filters</strong></p>';
                echo '<p><strong>Current page:</strong> ' . $current_page . ' of ' . $max_pages . '</p>';
                echo '<p><strong>$_GET:</strong> ' . print_r($_GET, true) . '</p>';
                echo '<p><strong>$_REQUEST:</strong> ' . print_r($_REQUEST, true) . '</p>';
                echo '</div>';
            }

            // Show debug link when results are found but no debug is active
            if (!(defined('WP_DEBUG') && WP_DEBUG) && !(isset($_REQUEST['debug_filters']) && $_REQUEST['debug_filters'] === '1')) {
                echo '<div style="background: #e3f2fd; padding: 8px; margin: 10px 0; border: 1px solid #2196f3; text-align: center; font-size: 12px;">';
                echo '<p style="margin: 0;">🔍 <a href="' . esc_url(add_query_arg('debug_filters', '1')) . '" style="color: #1976d2;">Enable Debug Mode</a> to see detailed filter analysis</p>';
                echo '</div>';
            }

            while ($query->have_posts()) {
                $query->the_post();
                self::renderPropertyCard();
            }

            echo '</div>';
            
            // Infinite scroll loader (hidden by default)
            if ($current_page < $max_pages) {
                echo '<div class="kcpf-infinite-loader" style="display: none;">';
                echo '<div class="kcpf-loading-spinner"></div>';
                echo '<p class="kcpf-loading-text">Loading more properties...</p>';
                echo '</div>';
            }
        } else {
            self::renderNoResults();
        }
        
        echo '</div>';
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }

    /**
     * Get sample property data for debugging
     */
    private static function getSamplePropertyData($bedroomsKey)
    {
        $args = [
            'post_type' => 'properties',
            'posts_per_page' => 5,
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
                    'key' => $bedroomsKey,
                    'compare' => 'EXISTS'
                ]
            ]
        ];

        $query = new WP_Query($args);
        $sample_data = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $property_id = get_the_ID();
                $bedrooms_value = get_post_meta($property_id, $bedroomsKey, true);

                if (!empty($bedrooms_value)) {
                    $sample_data[] = [
                        'id' => $property_id,
                        'title' => get_the_title(),
                        'bedrooms_raw' => $bedrooms_value,
                        'bedrooms_type' => gettype($bedrooms_value),
                        'has_5_bedrooms' => strpos($bedrooms_value, '"5":"true"') !== false
                    ];
                }
            }
            wp_reset_postdata();
        }

        return $sample_data;
    }

    /**
     * Render a single property card
     */
    private static function renderPropertyCard()
    {
        $property_id = get_the_ID();
        $purpose = get_the_terms($property_id, 'purpose');
        
        // Determine purpose for dynamic field selection
        $purposeSlug = 'sale';
        if ($purpose && !is_wp_error($purpose) && !empty($purpose)) {
            $purposeSlug = $purpose[0]->slug;
        }
        
        // Check if this is a sale property
        $isSale = ($purposeSlug === 'sale');
        
        // Render different layouts based on purpose using existing renderers
        if ($isSale) {
            // Use Map Card Renderer for sale properties (no duplication)
            echo KCPF_Map_Card_Renderer::renderCard($property_id, $purposeSlug);
        } else {
            // Get data needed for rent card view
            $location = get_the_terms($property_id, 'location');
            $price = KCPF_Card_Data_Helper::getPrice($property_id, $purposeSlug);
            $isMultiUnit = KCPF_Card_Data_Helper::isMultiUnit($property_id);
            $multiUnitCount = $isMultiUnit ? KCPF_Card_Data_Helper::getMultiUnitCount($property_id) : null;
            $bedrooms = KCPF_Card_Data_Helper::getBedrooms($property_id, $purposeSlug);
            $bathrooms = KCPF_Card_Data_Helper::getBathrooms($property_id, $purposeSlug);
            
            // Use Rent Card View for rent properties
            KCPF_Rent_Card_View::render($property_id, $location, $purpose, $price, $isMultiUnit, $multiUnitCount, $bedrooms, $bathrooms, $purposeSlug);
        }
    }
    
    
    /**
     * Render no results message
     */
    private static function renderNoResults()
    {
        // Get current filters for debugging
        $filters = KCPF_URL_Manager::getCurrentFilters();
        $purpose = isset($filters['purpose']) ? $filters['purpose'] : 'sale';

        // Get the query args that were used
        $query_args = KCPF_Query_Handler::buildQueryArgs(['purpose' => $purpose]);

        // Get bedroom query if it exists
        $bedroom_query = [];
        $bedroomsKey = KCPF_Field_Config::getMetaKey('bedrooms', $purpose);

        if (!empty($query_args['meta_query'])) {
            foreach ($query_args['meta_query'] as $query) {
                if (isset($query['relation']) && $query['relation'] === 'OR') {
                    foreach ($query as $subquery) {
                        if (is_array($subquery) && isset($subquery['key']) && $subquery['key'] === $bedroomsKey) {
                            $bedroom_query = $query;
                            break 2;
                        }
                    }
                } elseif (isset($query['key']) && $query['key'] === $bedroomsKey) {
                    $bedroom_query = $query;
                    break;
                }
            }
        }

        // Get actual sample data from database
        $sample_properties = self::getSamplePropertyData($bedroomsKey);

        ?>
        <div class="kcpf-no-results">
            <p><?php esc_html_e('No properties found matching your criteria.', 'key-cy-properties-filter'); ?></p>

            <!-- Quick debug link -->
            <div style="background: #e3f2fd; padding: 10px; margin: 10px 0; border: 1px solid #2196f3; text-align: center;">
                <p style="margin: 0; font-size: 14px;">
                    <strong>🔍 Debug Mode:</strong>
                    <a href="<?php echo esc_url(add_query_arg('debug_filters', '1')); ?>" style="color: #1976d2; text-decoration: none; font-weight: bold;">
                        Enable Debug Mode
                    </a>
                    (Shows detailed filter analysis)
                </p>
            </div>

            <?php if (defined('WP_DEBUG') && WP_DEBUG || (isset($_REQUEST['debug_filters']) && $_REQUEST['debug_filters'] === '1')): ?>
            <div style="background: #fff3cd; padding: 20px; margin: 20px 0; border: 2px solid #ffc107; font-family: monospace; font-size: 12px; line-height: 1.4;">
                <h3 style="margin-top: 0; color: #856404;">🔍 BEDROOM/BATHROOM FILTER DEBUG</h3>

                <div style="background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007cba;">
                    <h4 style="margin-top: 0; color: #007cba;">📋 Current Filters:</h4>
                    <pre><?php echo esc_html(print_r($filters, true)); ?></pre>
                </div>

                <div style="background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #28a745;">
                    <h4 style="margin-top: 0; color: #28a745;">🔑 Meta Keys Being Used:</h4>
                    <p><strong>Bedrooms Key:</strong> <?php echo esc_html($bedroomsKey); ?></p>
                    <p><strong>Bathrooms Key:</strong> <?php echo esc_html(KCPF_Field_Config::getMetaKey('bathrooms', $purpose)); ?></p>
                </div>

                <div style="background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545;">
                    <h4 style="margin-top: 0; color: #dc3545;">🔍 Query Being Built:</h4>
                    <p><strong>Bedroom Query:</strong></p>
                    <pre><?php echo esc_html(print_r($bedroom_query, true)); ?></pre>
                    <p><strong>Full Meta Query:</strong></p>
                    <pre><?php echo esc_html(print_r($query_args['meta_query'] ?? [], true)); ?></pre>
                </div>

                <div style="background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #6f42c1;">
                    <h4 style="margin-top: 0; color: #6f42c1;">💾 Actual Database Data (First 5 Properties):</h4>
                    <?php if (!empty($sample_properties)): ?>
                        <?php foreach ($sample_properties as $i => $prop): ?>
                            <div style="background: #fff; padding: 10px; margin: 5px 0; border: 1px solid #ddd;">
                                <p><strong>Property <?php echo $i + 1; ?>:</strong> <?php echo esc_html($prop['title']); ?> (ID: <?php echo esc_html($prop['id']); ?>)</p>
                                <p><strong>Raw Data:</strong> <code><?php echo esc_html($prop['bedrooms_raw']); ?></code></p>
                                <p><strong>Data Type:</strong> <?php echo esc_html($prop['bedrooms_type']); ?></p>
                                <p><strong>Contains "5":"true":</strong> <?php echo esc_html($prop['has_5_bedrooms'] ? 'YES ✅' : 'NO ❌'); ?></p>
                                <p><strong>Contains "2":"true":</strong> <?php echo esc_html(strpos($prop['bedrooms_raw'], '"2":"true"') !== false ? 'YES ✅' : 'NO ❌'); ?></p>
                                <p><strong>Contains "3":"true":</strong> <?php echo esc_html(strpos($prop['bedrooms_raw'], '"3":"true"') !== false ? 'YES ✅' : 'NO ❌'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No properties found in database with bedroom data.</p>
                    <?php endif; ?>
                </div>

                <div style="background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #fd7e14;">
                    <h4 style="margin-top: 0; color: #fd7e14;">🔧 Query Patterns Being Searched:</h4>
                    <?php if (!empty($filters['bedrooms'])): ?>
                        <p><strong>Looking for bedroom values:</strong> <?php echo esc_html(implode(', ', $filters['bedrooms'])); ?></p>
                        <div style="background: #fff; padding: 10px; border: 1px solid #ddd;">
                            <p><strong>Query will search for these patterns:</strong></p>
                            <ul>
                                <?php foreach ($filters['bedrooms'] as $bedroom): ?>
                                    <li>"<?php echo esc_html($bedroom); ?>":"true"</li>
                                    <li>"<?php echo esc_html($bedroom); ?>": "true"</li>
                                    <li>"<?php echo esc_html($bedroom); ?>":true</li>
                                    <li>s:<?php echo strlen($bedroom); ?>:"<?php echo esc_html($bedroom); ?>";s:4:"true"</li>
                                    <li>s:<?php echo strlen($bedroom); ?>:"<?php echo esc_html($bedroom); ?>";b:1</li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p>No bedroom filters active.</p>
                    <?php endif; ?>
                </div>

                <div style="background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #20c997;">
                    <h4 style="margin-top: 0; color: #20c997;">📊 Test Query Results:</h4>
                    <?php
                    // Test each query pattern individually
                    if (!empty($filters['bedrooms']) && !empty($sample_properties)) {
                        echo "<p><strong>Testing each query pattern on sample data:</strong></p>";
                        foreach ($filters['bedrooms'] as $bedroom) {
                            echo "<p><strong>Testing bedroom: $bedroom</strong></p>";
                            $patterns = [
                                '"' . $bedroom . '":"true"',
                                '"' . $bedroom . '": "true"',
                                '"' . $bedroom . '":true',
                                's:' . strlen($bedroom) . ':"' . $bedroom . '";s:4:"true"',
                                's:' . strlen($bedroom) . ':"' . $bedroom . '";b:1'
                            ];

                            foreach ($patterns as $pattern) {
                                $matches = 0;
                                foreach ($sample_properties as $prop) {
                                    if (strpos($prop['bedrooms_raw'], $pattern) !== false) {
                                        $matches++;
                                    }
                                }
                                echo "<p style='margin-left: 20px;'>Pattern '$pattern': <strong>$matches matches</strong></p>";
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (KCPF_URL_Manager::hasActiveFilters()) : ?>
                <a href="<?php echo esc_url(KCPF_URL_Manager::getResetUrl()); ?>" class="kcpf-reset-link">
                    <?php esc_html_e('Clear all filters', 'key-cy-properties-filter'); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}

