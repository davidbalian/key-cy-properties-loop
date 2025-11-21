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
     * Check if a term represents "Sale" purpose
     * 
     * Checks for English slug ('sale'), Russian variants ('продажа'), and Polylang original terms
     * 
     * @param WP_Term $term Purpose term
     * @return bool True if term is Sale
     */
    private static function isSaleTerm($term)
    {
        $slug = strtolower($term->slug);
        
        // Check English slug
        if ($slug === 'sale') {
            return true;
        }
        
        // Check Russian variants
        if ($slug === 'продажа' || strpos($slug, 'продажа') !== false) {
            return true;
        }
        
        // Check Polylang original term if available
        if (function_exists('pll_get_term_language')) {
            $original_id = get_term_meta($term->term_id, '_pll_origin', true);
            if ($original_id) {
                $original_term = get_term($original_id, 'purpose');
                if ($original_term && !is_wp_error($original_term) && strtolower($original_term->slug) === 'sale') {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if a term represents "Rent" purpose
     * 
     * Checks for English slug ('rent'), Russian variants ('аренда'), and Polylang original terms
     * 
     * @param WP_Term $term Purpose term
     * @return bool True if term is Rent
     */
    private static function isRentTerm($term)
    {
        $slug = strtolower($term->slug);
        
        // Check English slug
        if ($slug === 'rent') {
            return true;
        }
        
        // Check Russian variants
        if ($slug === 'аренда' || strpos($slug, 'аренда') !== false) {
            return true;
        }
        
        // Check Polylang original term if available
        if (function_exists('pll_get_term_language')) {
            $original_id = get_term_meta($term->term_id, '_pll_origin', true);
            if ($original_id) {
                $original_term = get_term($original_id, 'purpose');
                if ($original_term && !is_wp_error($original_term) && strtolower($original_term->slug) === 'rent') {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get standardized slug for a purpose term
     * 
     * Returns 'sale' or 'rent' regardless of localization
     * 
     * @param WP_Term $term Purpose term
     * @return string Standardized slug ('sale' or 'rent')
     */
    private static function getStandardSlug($term)
    {
        if (self::isSaleTerm($term)) {
            return 'sale';
        }
        if (self::isRentTerm($term)) {
            return 'rent';
        }
        // Fallback to original slug if unknown
        return strtolower($term->slug);
    }
    
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
        
        // Ensure default is used if current_value is empty
        if (empty($current_value)) {
            $current_value = $attrs['default'];
        }
        
        // Determine which term should be selected based on current_value
        // This handles both English slugs and localized slugs
        $selected_term = null;
        foreach ($purposes as $purpose) {
            $standard_slug = self::getStandardSlug($purpose);
            // Check if current_value matches either the localized slug or standard slug
            if ($current_value === $purpose->slug || $current_value === $standard_slug) {
                $selected_term = $purpose;
                break;
            }
        }
        
        // If no match found and default is 'sale', find the sale term
        if (!$selected_term && $attrs['default'] === 'sale') {
            foreach ($purposes as $purpose) {
                if (self::isSaleTerm($purpose)) {
                    $selected_term = $purpose;
                    break;
                }
            }
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-purpose">
            <?php if ($attrs['type'] === 'select') : ?>
                <select name="purpose" class="kcpf-filter-select">
                    <?php foreach ($purposes as $purpose) : 
                        $standard_slug = self::getStandardSlug($purpose);
                        $is_selected = ($selected_term && $selected_term->term_id === $purpose->term_id);
                        $purpose_class = 'kcpf-purpose-' . $standard_slug;
                    ?>
                        <option value="<?php echo esc_attr($purpose->slug); ?>" 
                                data-standard-slug="<?php echo esc_attr($standard_slug); ?>"
                                class="<?php echo esc_attr($purpose_class); ?>"
                                <?php echo $is_selected ? 'selected="selected"' : ''; ?>>
                            <?php echo esc_html($purpose->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($attrs['type'] === 'toggle') : ?>
                <div class="kcpf-toggle-buttons">
                    <?php foreach ($purposes as $purpose) : 
                        $standard_slug = self::getStandardSlug($purpose);
                        $is_selected = ($selected_term && $selected_term->term_id === $purpose->term_id);
                        $purpose_class = 'kcpf-purpose-' . $standard_slug;
                    ?>
                        <label class="kcpf-toggle-label <?php echo $is_selected ? 'active' : ''; ?>">
                            <input type="radio" 
                                   name="purpose" 
                                   value="<?php echo esc_attr($purpose->slug); ?>"
                                   data-standard-slug="<?php echo esc_attr($standard_slug); ?>"
                                   class="<?php echo esc_attr($purpose_class); ?>"
                                   <?php echo $is_selected ? 'checked="checked"' : ''; ?>>
                            <span><?php echo esc_html($purpose->name); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="kcpf-radio-buttons">
                    <?php foreach ($purposes as $purpose) : 
                        $standard_slug = self::getStandardSlug($purpose);
                        $is_selected = ($selected_term && $selected_term->term_id === $purpose->term_id);
                        $purpose_class = 'kcpf-purpose-' . $standard_slug;
                    ?>
                        <label class="kcpf-radio-label">
                            <input type="radio" 
                                   name="purpose" 
                                   value="<?php echo esc_attr($purpose->slug); ?>"
                                   data-standard-slug="<?php echo esc_attr($standard_slug); ?>"
                                   class="<?php echo esc_attr($purpose_class); ?>"
                                   <?php echo $is_selected ? 'checked="checked"' : ''; ?>>
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

