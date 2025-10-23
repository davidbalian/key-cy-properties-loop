<?php
/**
 * Bathrooms Filter Renderer
 * 
 * Renders the bathrooms filter with various display types
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Bathrooms_Filter_Renderer extends KCPF_Filter_Renderer_Base
{
    /**
     * Render bathrooms filter
     * 
     * Displays a filterable list of bathroom counts.
     * Options are loaded from JetEngine glossary with fallback to hardcoded values.
     * Supports select, checkbox, and button group display types.
     * 
     * @param array $attrs Shortcode attributes
     *                     - type: 'select', 'checkbox', or 'button' (default: 'checkbox')
     *                     - glossary_id: JetEngine glossary ID (default: '8')
     * @return string HTML output
     */
    public static function renderBathrooms($attrs)
    {
        $attrs = shortcode_atts([
            'type' => 'checkbox',
            'glossary_id' => '8', // JetEngine Bathrooms glossary ID
        ], $attrs);
        
        $current_value = KCPF_URL_Manager::getFilterValue('bathrooms');
        
        // Get bathroom options from JetEngine glossary
        $glossaryOptions = KCPF_Glossary_Handler::getOptionsForRendering($attrs['glossary_id']);
        
        // Fallback if glossary is empty or JetEngine is not available
        if (empty($glossaryOptions)) {
            $glossaryOptions = [
                ['value' => '1', 'label' => '1'],
                ['value' => '2', 'label' => '2'],
                ['value' => '3', 'label' => '3'],
                ['value' => '4', 'label' => '4'],
                ['value' => '5', 'label' => '5'],
                ['value' => '6', 'label' => '6'],
                ['value' => '7', 'label' => '7'],
                ['value' => '8_plus', 'label' => '8+'],
            ];
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-bathrooms">
            <?php if ($attrs['type'] === 'select') : ?>
                <select name="bathrooms" class="kcpf-filter-select">
                    <option value=""><?php echo esc_html(__('Bathrooms', 'key-cy-properties-filter')); ?></option>
                    <?php foreach ($glossaryOptions as $option) : ?>
                        <option value="<?php echo esc_attr($option['value']); ?>" <?php selected($current_value, $option['value']); ?>>
                            <?php echo esc_html($option['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($attrs['type'] === 'checkbox') : ?>
                <?php echo self::renderMultiselectDropdown(
                    'bathrooms',
                    __('Bathrooms', 'key-cy-properties-filter'),
                    $glossaryOptions,
                    $current_value,
                    false
                ); ?>
            <?php else : ?>
                <div class="kcpf-button-group">
                    <?php foreach ($glossaryOptions as $option) : ?>
                        <label class="kcpf-button-label <?php echo ($current_value == $option['value']) ? 'active' : ''; ?>">
                            <input type="radio" 
                                   name="bathrooms" 
                                   value="<?php echo esc_attr($option['value']); ?>"
                                   <?php checked($current_value, $option['value']); ?>>
                            <span><?php echo esc_html($option['label']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

