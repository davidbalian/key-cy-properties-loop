<?php
/**
 * Purpose Filter Renderer
 * 
 * Renders the purpose filter (Sale/Rent) with various display types
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Purpose_Filter_Renderer
{
    /**
     * Render purpose filter (Sale/Rent)
     * 
     * Displays a selector for property purpose with support for different display types:
     * - select: Dropdown menu
     * - toggle: Toggle button style
     * - radio: Radio button style
     * 
     * @param array $attrs Shortcode attributes
     *                     - type: 'select', 'toggle', or 'radio' (default: 'select')
     *                     - default: Default selected purpose (default: 'sale')
     * @return string HTML output
     */
    public static function renderPurpose($attrs)
    {
        $attrs = shortcode_atts([
            'type' => 'select',
            'default' => 'sale',
        ], $attrs);
        
        $purposes = get_terms([
            'taxonomy' => 'purpose',
            'hide_empty' => true,
        ]);
        
        if (empty($purposes) || is_wp_error($purposes)) {
            return '';
        }
        
        $current_value = KCPF_URL_Manager::getFilterValue('purpose') ?: $attrs['default'];
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-purpose">
            <?php if ($attrs['type'] === 'select') : ?>
                <select name="purpose" class="kcpf-filter-select">
                    <?php foreach ($purposes as $purpose) : ?>
                        <option value="<?php echo esc_attr($purpose->slug); ?>" <?php selected($current_value, $purpose->slug); ?>>
                            <?php echo esc_html($purpose->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($attrs['type'] === 'toggle') : ?>
                <div class="kcpf-toggle-buttons">
                    <?php foreach ($purposes as $purpose) : ?>
                        <label class="kcpf-toggle-label <?php echo ($current_value === $purpose->slug) ? 'active' : ''; ?>">
                            <input type="radio" 
                                   name="purpose" 
                                   value="<?php echo esc_attr($purpose->slug); ?>"
                                   <?php checked($current_value, $purpose->slug); ?>>
                            <span><?php echo esc_html($purpose->name); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="kcpf-radio-buttons">
                    <?php foreach ($purposes as $purpose) : ?>
                        <label class="kcpf-radio-label">
                            <input type="radio" 
                                   name="purpose" 
                                   value="<?php echo esc_attr($purpose->slug); ?>"
                                   <?php checked($current_value, $purpose->slug); ?>>
                            <span><?php echo esc_html($purpose->name); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

