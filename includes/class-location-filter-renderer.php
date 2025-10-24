<?php
/**
 * Location Filter Renderer
 * 
 * Renders the location filter with select or multiselect options
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Location_Filter_Renderer extends KCPF_Filter_Renderer_Base
{
    /**
     * Render location filter
     * 
     * Displays a filterable list of locations based on the current purpose (sale/rent).
     * Supports both single-select dropdown and multi-select checkbox modes.
     * 
     * @param array $attrs Shortcode attributes
     *                     - type: 'select' or 'checkbox' (default: 'select')
     *                     - placeholder: Custom placeholder text
     *                     - show_count: Whether to show property counts
     * @return string HTML output
     */
    public static function renderLocation($attrs)
    {
        try {
            $attrs = shortcode_atts([
                'type' => 'select',
                'placeholder' => 'Location',
                'show_count' => false,
            ], $attrs);
            
            // Get purpose from URL or use default
            $purpose = KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale';
            
            // Get locations filtered by purpose
            $locations = self::getTermsByPurpose('location', $purpose);
            
            if (empty($locations)) {
                return '';
            }
        } catch (Exception $e) {
            error_log('KCPF Location Filter Error: ' . $e->getMessage());
            return '';
        }
        
        $current_value = KCPF_URL_Manager::getFilterValue('location');
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-location">
            <?php if ($attrs['type'] === 'select') : ?>
                <select id="kcpf-location" name="location" class="kcpf-filter-select">
                    <option value=""><?php echo esc_html($attrs['placeholder'] ?: __('Location', 'key-cy-properties-filter')); ?></option>
                    <?php foreach ($locations as $location) : ?>
                        <option value="<?php echo esc_attr($location->slug); ?>" <?php selected($current_value, $location->slug); ?>>
                            <?php echo esc_html($location->name); ?>
                            <?php if ($attrs['show_count']) : ?>
                                (<?php echo $location->count; ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <?php echo self::renderMultiselectDropdown(
                    'location',
                    __('Location', 'key-cy-properties-filter'),
                    $locations,
                    $current_value,
                    $attrs['show_count']
                ); ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

