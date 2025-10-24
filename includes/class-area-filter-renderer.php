<?php
/**
 * Area Filter Renderer
 * 
 * Renders area-related filters (covered area, plot area) with sliders
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Area_Filter_Renderer extends KCPF_Filter_Renderer_Base
{
    /**
     * Render covered area filter
     * 
     * Displays a range slider for filtering properties by covered area.
     * Automatically determines min/max values based on available listings
     * for the current purpose (sale/rent).
     * 
     * @param array $attrs Shortcode attributes
     *                     - min: Minimum area (auto-detected if not specified)
     *                     - max: Maximum area (auto-detected if not specified)
     *                     - step: Step increment (default: 10)
     * @return string HTML output
     */
    public static function renderCoveredArea($attrs)
    {
        try {
            // Get purpose from URL or use default
            $purpose = KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale';
            
            // Get min/max values from actual listings
            $range = KCPF_Listing_Values::getMinMax('covered_area', $purpose);
            
            $attrs = shortcode_atts([
                'min' => $range['min'],
                'max' => $range['max'],
                'step' => 10,
            ], $attrs);
            
            // Validate attributes
            $attrs['min'] = absint($attrs['min']);
            $attrs['max'] = absint($attrs['max']);
            $attrs['step'] = absint($attrs['step']);
            
            if ($attrs['min'] >= $attrs['max']) {
                error_log('KCPF Covered Area Filter Error: Min must be less than max');
                return '';
            }
            
            if ($attrs['step'] <= 0) {
                $attrs['step'] = 1;
            }
            
            $area_min = KCPF_URL_Manager::getFilterValue('covered_area_min');
            $area_max = KCPF_URL_Manager::getFilterValue('covered_area_max');
        } catch (Exception $e) {
            error_log('KCPF Covered Area Filter Error: ' . $e->getMessage());
            return '';
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-covered-area">
            <?php echo self::renderRangeFilter(
                'covered_area',
                __('Covered Area, m²', 'key-cy-properties-filter'),
                $attrs['min'],
                $attrs['max'],
                $attrs['step'],
                $area_min,
                $area_max,
                '',
                'm²',
                ''
            ); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render plot area filter
     * 
     * Displays a range slider for filtering properties by plot area.
     * Automatically determines min/max values based on available listings
     * for the current purpose (sale/rent).
     * 
     * @param array $attrs Shortcode attributes
     *                     - min: Minimum area (auto-detected if not specified)
     *                     - max: Maximum area (auto-detected if not specified)
     *                     - step: Step increment (default: 50)
     * @return string HTML output
     */
    public static function renderPlotArea($attrs)
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
            ], $attrs);
            
            // Validate attributes
            $attrs['min'] = absint($attrs['min']);
            $attrs['max'] = absint($attrs['max']);
            $attrs['step'] = absint($attrs['step']);
            
            if ($attrs['min'] >= $attrs['max']) {
                error_log('KCPF Plot Area Filter Error: Min must be less than max');
                return '';
            }
            
            if ($attrs['step'] <= 0) {
                $attrs['step'] = 1;
            }
            
            $plot_min = KCPF_URL_Manager::getFilterValue('plot_area_min');
            $plot_max = KCPF_URL_Manager::getFilterValue('plot_area_max');
        } catch (Exception $e) {
            error_log('KCPF Plot Area Filter Error: ' . $e->getMessage());
            return '';
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-plot-area">
            <?php echo self::renderRangeFilter(
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
    }
}

