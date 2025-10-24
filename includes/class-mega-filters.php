<?php
/**
 * Mega Filters Shortcode Class
 *
 * Renders a comprehensive set of filters in a specific order for property listings.
 * Automatically detects and works with loops on the current page.
 *
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Mega_Filters
{
    /**
     * Render the mega filters shortcode
     *
     * Displays all property filters in the following order:
     * 1. Type (Property Type)
     * 2. Location
     * 3. Bedrooms
     * 4. Bathrooms
     * 5. Price
     * 6. Covered Area
     * 7. Amenities
     * 8. Land Area (Plot Area with custom title)
     * 9. Search by ID
     *
     * @param array $attrs Shortcode attributes
     *                     - apply_text: Text for apply button (default: 'Apply Filters')
     *                     - reset_text: Text for reset button (default: 'Reset Filters')
     *                     - show_apply: Whether to show apply button (default: true)
     *                     - show_reset: Whether to show reset button (default: true)
     * @return string HTML output
     */
    public static function render($attrs)
    {
        try {
            $attrs = shortcode_atts([
                'apply_text' => 'Apply Filters',
                'reset_text' => 'Reset Filters',
                'show_apply' => true,
                'show_reset' => true,
            ], $attrs);

            // Get current filter values from URL
            $current_filters = KCPF_URL_Manager::getCurrentFilters();

            // Build all filters in specified order
            $filters_html = self::renderFiltersInOrder($current_filters);

            // Add action buttons if requested
            $buttons_html = '';
            if ($attrs['show_apply'] || $attrs['show_reset']) {
                $buttons_html = self::renderActionButtons($attrs);
            }

            ob_start();
            ?>
            <div class="kcpf-mega-filters" data-purpose="<?php echo esc_attr($current_filters['purpose'] ?: 'sale'); ?>">
                <div class="kcpf-accordion">
                    <div class="kcpf-accordion-header">
                        <span class="kcpf-accordion-title"><?php esc_html_e('Filters', 'key-cy-properties-filter'); ?></span>
                        <span class="kcpf-accordion-toggle"></span>
                    </div>
                    <div class="kcpf-accordion-content">
                        <form class="kcpf-filters-form" method="get">
                            <?php echo $filters_html; // All filters in order ?>
                            <?php echo $buttons_html; // Apply/Reset buttons ?>
                        </form>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        } catch (Exception $e) {
            error_log('KCPF Mega Filters Render Error: ' . $e->getMessage());
            return '<p>Error loading mega filters. Please try again.</p>';
        }
    }

    /**
     * Render all filters in the specified order
     *
     * @param array $current_filters Current filter values
     * @return string HTML output
     */
    private static function renderFiltersInOrder($current_filters)
    {
        try {
            $html = '';

            // 1. Property Type Filter
            $html .= KCPF_Filter_Renderer::renderType([
                'type' => 'checkbox',
            ]);

            // 2. Location Filter
            $html .= KCPF_Filter_Renderer::renderLocation([
                'type' => 'checkbox',
                'show_count' => true,
            ]);

            // 3. Bedrooms Filter
            $html .= KCPF_Filter_Renderer::renderBedrooms([
                'type' => 'checkbox',
            ]);

            // 4. Bathrooms Filter
            $html .= KCPF_Filter_Renderer::renderBathrooms([
                'type' => 'checkbox',
            ]);

            // 5. Price Filter
            $html .= KCPF_Filter_Renderer::renderPrice([
                'type' => 'slider',
            ]);

            // 6. Covered Area Filter
            $html .= KCPF_Filter_Renderer::renderCoveredArea([
                'type' => 'slider',
            ]);

            // 7. Amenities Filter
            $html .= KCPF_Filter_Renderer::renderAmenities([
                'type' => 'checkbox',
            ]);

            // 8. Land Area Filter (Plot Area with custom title)
            $html .= KCPF_Filter_Renderer::renderPlotArea([
                'type' => 'slider',
            ]);

            // 9. Search by ID Filter
            $html .= KCPF_Filter_Renderer::renderPropertyId([
                'placeholder' => 'Search by Property ID',
            ]);

            return $html;
        } catch (Exception $e) {
            error_log('KCPF Mega Filters Error: ' . $e->getMessage());
            return '<p>Error loading filters. Please try again.</p>';
        }
    }


    /**
     * Render action buttons (Apply/Reset)
     *
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    private static function renderActionButtons($attrs)
    {
        $html = '';

        if ($attrs['show_apply']) {
            $html .= KCPF_Filter_Renderer::renderApplyButton([
                'text' => $attrs['apply_text'],
                'type' => 'reload',
            ]);
        }

        if ($attrs['show_reset']) {
            $html .= KCPF_Filter_Renderer::renderResetButton([
                'text' => $attrs['reset_text'],
            ]);
        }

        return $html;
    }
}
