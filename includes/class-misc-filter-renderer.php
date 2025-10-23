<?php
/**
 * Miscellaneous Filter Renderer
 * 
 * Renders miscellaneous filter components (apply button, reset button, property ID search)
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Misc_Filter_Renderer
{
    /**
     * Render apply/submit button
     * 
     * Displays a button to submit/apply the selected filters.
     * 
     * @param array $attrs Shortcode attributes
     *                     - text: Button text (default: 'Apply Filters')
     *                     - type: Action type 'reload' or 'ajax' (default: 'reload')
     * @return string HTML output
     */
    public static function renderApplyButton($attrs)
    {
        $attrs = shortcode_atts([
            'text' => 'Apply Filters',
            'type' => 'reload',
        ], $attrs);
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-apply">
            <button type="submit" class="kcpf-apply-button" data-type="<?php echo esc_attr($attrs['type']); ?>">
                <?php echo esc_html($attrs['text']); ?>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render reset/clear button
     * 
     * Displays a button to clear all selected filters and reset to defaults.
     * 
     * @param array $attrs Shortcode attributes
     *                     - text: Button text (default: 'Reset Filters')
     * @return string HTML output
     */
    public static function renderResetButton($attrs)
    {
        $attrs = shortcode_atts([
            'text' => 'Reset Filters',
        ], $attrs);
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-reset">
            <button type="button" class="kcpf-reset-button">
                <?php echo esc_html($attrs['text']); ?>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render property ID search filter
     * 
     * Displays a text input for searching properties by their ID.
     * 
     * @param array $attrs Shortcode attributes
     *                     - placeholder: Placeholder text (default: 'Search by Property ID')
     * @return string HTML output
     */
    public static function renderPropertyId($attrs)
    {
        $attrs = shortcode_atts([
            'placeholder' => 'Search by Property ID',
        ], $attrs);
        
        $property_id = KCPF_URL_Manager::getFilterValue('property_id');
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-property-id">
            <input type="text" 
                   id="kcpf-property-id"
                   name="property_id" 
                   placeholder="<?php echo esc_attr($attrs['placeholder'] ?: __('Property ID', 'key-cy-properties-filter')); ?>"
                   value="<?php echo esc_attr($property_id); ?>"
                   class="kcpf-input kcpf-property-id-input">
        </div>
        <?php
        return ob_get_clean();
    }
}

