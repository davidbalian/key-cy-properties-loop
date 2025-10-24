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
            <form class="kcpf-filters-form" method="get">
                <?php echo $filters_html; // All filters in order ?>
                <?php echo $buttons_html; // Apply/Reset buttons ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render all filters in the specified order
     *
     * @param array $current_filters Current filter values
     * @return string HTML output
     */
    private static function renderFiltersInOrder($current_filters)
    {
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
        $html .= self::renderLandAreaFilter($current_filters);

        // 9. Search by ID Filter
        $html .= KCPF_Filter_Renderer::renderPropertyId([
            'placeholder' => 'Search by Property ID',
        ]);

        return $html;
    }

    /**
     * Render land area filter (plot area with custom title)
     *
     * @param array $current_filters Current filter values
     * @return string HTML output
     */
    private static function renderLandAreaFilter($current_filters)
    {
        try {
            // Get purpose from URL or use default
            $purpose = KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale';

            // Get min/max values from actual listings
            $range = KCPF_Listing_Values::getMinMax('plot_area', $purpose);

            $attrs = shortcode_atts([
                'min' => $range['min'],
                'max' => $range['max'],
                'step' => 50,
            ], []);

            // Validate attributes
            $attrs['min'] = absint($attrs['min']);
            $attrs['max'] = absint($attrs['max']);
            $attrs['step'] = absint($attrs['step']);

            if ($attrs['min'] >= $attrs['max']) {
                return '';
            }

            if ($attrs['step'] <= 0) {
                $attrs['step'] = 1;
            }

            $plot_min = KCPF_URL_Manager::getFilterValue('plot_area_min');
            $plot_max = KCPF_URL_Manager::getFilterValue('plot_area_max');

            ob_start();
            ?>
            <div class="kcpf-filter kcpf-filter-plot-area kcpf-filter-land-area">
                <?php echo KCPF_Filter_Renderer_Base::renderRangeFilter(
                    'plot_area',
                    __('Land Area, m²', 'key-cy-properties-filter'),
                    $attrs['min'],
                    $attrs['max'],
                    $attrs['step'],
                    $plot_min,
                    $plot_max,
                    '',
                    'm²',
                    ''
                ); ?>
            </div>
            <?php
            return ob_get_clean();
        } catch (Exception $e) {
            error_log('KCPF Land Area Filter Error: ' . $e->getMessage());
            return '';
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
