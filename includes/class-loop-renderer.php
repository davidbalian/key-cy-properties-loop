<?php
/**
 * Loop Renderer Class
 * 
 * Renders the properties loop with filtered results
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
        if (!empty($query_args['meta_query'])) {
            foreach ($query_args['meta_query'] as $query) {
                if (isset($query['key']) && $query['key'] === KCPF_Field_Config::getMetaKey('bedrooms', $purpose)) {
                    $bedroom_query = $query;
                    break;
                }
            }
        }
        
        ?>
        <div class="kcpf-no-results">
            <p><?php esc_html_e('No properties found matching your criteria.', 'key-cy-properties-filter'); ?></p>
            
            <div style="background: #f5f5f5; padding: 15px; margin: 20px 0; border: 1px solid #ddd; font-family: monospace; font-size: 13px;">
                <h3 style="margin-top: 0;">Debug Information:</h3>
                <div style="margin-bottom: 15px;">
                    <strong>Current Filters:</strong>
                    <pre style="background: #fff; padding: 10px; margin: 5px 0; overflow: auto;"><?php echo esc_html(print_r($filters, true)); ?></pre>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Bedroom Values:</strong>
                    <pre style="background: #fff; padding: 10px; margin: 5px 0; overflow: auto;"><?php echo esc_html(print_r($filters['bedrooms'] ?? 'Not Set', true)); ?></pre>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Bedroom Query:</strong>
                    <pre style="background: #fff; padding: 10px; margin: 5px 0; overflow: auto;"><?php echo esc_html(print_r($bedroom_query, true)); ?></pre>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Full Meta Query:</strong>
                    <pre style="background: #fff; padding: 10px; margin: 5px 0; overflow: auto;"><?php echo esc_html(print_r($query_args['meta_query'] ?? [], true)); ?></pre>
                </div>
            </div>

            <?php if (KCPF_URL_Manager::hasActiveFilters()) : ?>
                <a href="<?php echo esc_url(KCPF_URL_Manager::getResetUrl()); ?>" class="kcpf-reset-link">
                    <?php esc_html_e('Clear all filters', 'key-cy-properties-filter'); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}

