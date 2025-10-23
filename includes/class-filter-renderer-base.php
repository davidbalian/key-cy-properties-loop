<?php
/**
 * Filter Renderer Base Class
 * 
 * Provides shared functionality for all filter renderers
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Filter_Renderer_Base
{
    /**
     * Get taxonomy terms filtered by purpose
     * 
     * This method retrieves terms from a taxonomy and filters them to only include
     * terms that have properties matching the specified purpose (sale or rent).
     * 
     * @param string $taxonomy Taxonomy name (e.g., 'location', 'property-type')
     * @param string $purpose Property purpose ('sale' or 'rent')
     * @return array Filtered terms with updated counts
     */
    public static function getTermsByPurpose($taxonomy, $purpose = 'sale')
    {
        // Get all terms from the taxonomy
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
        ]);
        
        if (empty($terms) || is_wp_error($terms)) {
            return [];
        }
        
        // Filter terms to only include those with properties for the given purpose
        $filtered_terms = [];
        
        foreach ($terms as $term) {
            // Check if this term has any properties with the given purpose
            $args = [
                'post_type' => 'properties',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $term->term_id,
                    ],
                    [
                        'taxonomy' => 'purpose',
                        'field' => 'slug',
                        'terms' => $purpose,
                    ],
                ],
            ];
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                // Update the count to reflect only properties with this purpose
                $term->count = $query->found_posts;
                $filtered_terms[] = $term;
            }
            
            wp_reset_postdata();
        }
        
        return $filtered_terms;
    }
    
    /**
     * Render a multiselect dropdown
     * 
     * @param string $filter_name Filter name (e.g., 'location', 'property_type')
     * @param string $placeholder Placeholder text
     * @param array $options Array of options with 'slug'/'value' and 'name'/'label' keys
     * @param mixed $current_value Current selected value(s)
     * @param bool $show_count Whether to show count in parentheses
     * @return string HTML output
     */
    protected static function renderMultiselectDropdown($filter_name, $placeholder, $options, $current_value, $show_count = false)
    {
        $current_values = is_array($current_value) ? $current_value : ($current_value ? [$current_value] : []);
        
        ob_start();
        ?>
        <div class="kcpf-multiselect-dropdown" data-filter-name="<?php echo esc_attr($filter_name); ?>">
            <div class="kcpf-multiselect-trigger">
                <div class="kcpf-multiselect-selected">
                    <?php if (empty($current_values)) : ?>
                        <span class="kcpf-placeholder"><?php echo esc_html($placeholder); ?></span>
                    <?php else: ?>
                        <?php foreach ($current_values as $val) : 
                            $option = array_filter($options, function($opt) use ($val) { 
                                return (isset($opt->slug) && $opt->slug === $val) || (isset($opt['value']) && $opt['value'] === $val);
                            });
                            $option = !empty($option) ? reset($option) : null;
                            $label = $option ? (isset($option->name) ? $option->name : $option['label']) : $val;
                        ?>
                            <span class="kcpf-chip">
                                <?php echo esc_html($label); ?>
                                <span class="kcpf-chip-remove" data-value="<?php echo esc_attr($val); ?>">&times;</span>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <span class="kcpf-multiselect-arrow">▼</span>
            </div>
            <div class="kcpf-multiselect-dropdown-menu">
                <?php foreach ($options as $option) : 
                    $value = isset($option->slug) ? $option->slug : $option['value'];
                    $label = isset($option->name) ? $option->name : $option['label'];
                    $count = isset($option->count) ? $option->count : 0;
                ?>
                    <label class="kcpf-multiselect-option">
                        <input type="checkbox" 
                               name="<?php echo esc_attr($filter_name); ?>[]" 
                               value="<?php echo esc_attr($value); ?>"
                               <?php checked(in_array($value, $current_values)); ?>>
                        <span><?php echo esc_html($label); ?></span>
                        <?php if ($show_count && $count > 0) : ?>
                            <span class="kcpf-count">(<?php echo $count; ?>)</span>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render a range filter with slider
     * 
     * @param string $filter_name Filter name (e.g., 'price', 'covered_area')
     * @param string $placeholder Placeholder text
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @param int $step Step increment
     * @param mixed $current_min Current minimum value
     * @param mixed $current_max Current maximum value
     * @param string $prefix Prefix for input (e.g., '€')
     * @param string $suffix Suffix for input (e.g., 'm²')
     * @param string $format Format type ('currency' or '')
     * @return string HTML output
     */
    protected static function renderRangeFilter($filter_name, $placeholder, $min, $max, $step, $current_min, $current_max, $prefix = '', $suffix = '', $format = '')
    {
        ob_start();
        ?>
        <div class="kcpf-range-dropdown">
            <div class="kcpf-range-trigger">
                <div class="kcpf-range-display">
                    <span class="kcpf-placeholder"><?php echo esc_html($placeholder); ?></span>
                </div>
                <span class="kcpf-multiselect-arrow">▼</span>
            </div>
            
            <div class="kcpf-range-dropdown-menu">
                <div class="kcpf-range-slider-container">
                    <div class="kcpf-range-slider" 
                         data-min="<?php echo esc_attr($min); ?>"
                         data-max="<?php echo esc_attr($max); ?>"
                         data-step="<?php echo esc_attr($step); ?>"
                         data-value-min="<?php echo esc_attr($current_min ?: $min); ?>"
                         data-value-max="<?php echo esc_attr($current_max ?: $max); ?>"
                         <?php if ($format) : ?>data-format="<?php echo esc_attr($format); ?>"<?php endif; ?>>
                    </div>
                    
                    <div class="kcpf-range-inputs">
                        <div class="kcpf-input-wrapper">
                            <?php if ($prefix) : ?>
                                <span class="kcpf-input-prefix"><?php echo esc_html($prefix); ?></span>
                            <?php endif; ?>
                            <input type="number" 
                                   name="<?php echo esc_attr($filter_name); ?>_min" 
                                   placeholder="Min"
                                   value="<?php echo esc_attr($current_min); ?>"
                                   min="<?php echo esc_attr($min); ?>"
                                   max="<?php echo esc_attr($max); ?>"
                                   step="<?php echo esc_attr($step); ?>"
                                   class="kcpf-input kcpf-range-min">
                            <?php if ($suffix) : ?>
                                <span class="kcpf-input-suffix"><?php echo esc_html($suffix); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <span class="kcpf-range-separator">-</span>
                        
                        <div class="kcpf-input-wrapper">
                            <?php if ($prefix) : ?>
                                <span class="kcpf-input-prefix"><?php echo esc_html($prefix); ?></span>
                            <?php endif; ?>
                            <input type="number" 
                                   name="<?php echo esc_attr($filter_name); ?>_max" 
                                   placeholder="Max"
                                   value="<?php echo esc_attr($current_max); ?>"
                                   min="<?php echo esc_attr($min); ?>"
                                   max="<?php echo esc_attr($max); ?>"
                                   step="<?php echo esc_attr($step); ?>"
                                   class="kcpf-input kcpf-range-max">
                            <?php if ($suffix) : ?>
                                <span class="kcpf-input-suffix"><?php echo esc_html($suffix); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

