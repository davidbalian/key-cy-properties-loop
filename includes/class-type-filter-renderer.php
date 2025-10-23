<?php
/**
 * Property Type Filter Renderer
 * 
 * Renders the property type filter
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Type_Filter_Renderer extends KCPF_Filter_Renderer_Base
{
    /**
     * Render property type filter
     * 
     * Displays a filterable list of property types based on the current purpose (sale/rent).
     * Supports both single-select dropdown and multi-select checkbox modes.
     * 
     * @param array $attrs Shortcode attributes
     *                     - type: 'select' or 'checkbox' (default: 'select')
     * @return string HTML output
     */
    public static function renderType($attrs)
    {
        $attrs = shortcode_atts([
            'type' => 'select',
        ], $attrs);
        
        // Get purpose from URL or use default
        $purpose = KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale';
        
        // Get property types filtered by purpose
        $types = self::getTermsByPurpose('property-type', $purpose);
        
        if (empty($types)) {
            return '';
        }
        
        $current_value = KCPF_URL_Manager::getFilterValue('property_type');
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-type">
            <?php if ($attrs['type'] === 'select') : ?>
                <select name="property_type" class="kcpf-filter-select">
                    <option value=""><?php echo esc_html(__('Property Type', 'key-cy-properties-filter')); ?></option>
                    <?php foreach ($types as $type) : ?>
                        <option value="<?php echo esc_attr($type->slug); ?>" <?php selected($current_value, $type->slug); ?>>
                            <?php echo esc_html($type->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <?php echo self::renderMultiselectDropdown(
                    'property_type',
                    __('Property Type', 'key-cy-properties-filter'),
                    $types,
                    $current_value,
                    false
                ); ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

