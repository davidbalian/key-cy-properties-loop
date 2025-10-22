<?php
/**
 * Loop Renderer Class
 * 
 * Renders the properties loop with filtered results
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Loop_Renderer
{
    /**
     * Render properties loop shortcode
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function render($attrs)
    {
        $attrs = shortcode_atts([
            'purpose' => 'sale',
            'posts_per_page' => 10,
        ], $attrs, 'properties_loop');
        
        // Build query
        $query_args = KCPF_Query_Handler::buildQueryArgs($attrs);
        $query = new WP_Query($query_args);
        
        ob_start();
        
        echo '<div class="kcpf-properties-loop" id="kcpf-properties-loop">';
        
        if ($query->have_posts()) {
            echo '<div class="kcpf-properties-grid">';
            
            while ($query->have_posts()) {
                $query->the_post();
                self::renderPropertyCard();
            }
            
            echo '</div>';
            
            // Pagination
            self::renderPagination($query);
        } else {
            self::renderNoResults();
        }
        
        echo '</div>';
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Render a single property card
     */
    private static function renderPropertyCard()
    {
        $property_id = get_the_ID();
        $location = get_the_terms($property_id, 'location');
        $purpose = get_the_terms($property_id, 'purpose');
        
        // Determine purpose for dynamic field selection
        $purposeSlug = 'sale';
        if ($purpose && !is_wp_error($purpose) && !empty($purpose)) {
            $purposeSlug = $purpose[0]->slug;
        }
        
        // Get formatted values using helper
        $bedrooms = KCPF_Card_Data_Helper::getBedrooms($property_id, $purposeSlug);
        $bathrooms = KCPF_Card_Data_Helper::getBathrooms($property_id, $purposeSlug);
        $price = KCPF_Card_Data_Helper::getPrice($property_id, $purposeSlug);
        
        // Check if multi-unit and get price if applicable
        $isMultiUnit = KCPF_Card_Data_Helper::isMultiUnit($property_id);
        $multiUnitPrice = $isMultiUnit ? KCPF_Card_Data_Helper::getMultiUnitPrice($property_id, $purposeSlug) : null;
        $multiUnitCount = $isMultiUnit ? KCPF_Card_Data_Helper::getMultiUnitCount($property_id) : null;
        
        ?>
        <article class="kcpf-property-card">
            <?php if (has_post_thumbnail()) : ?>
                <div class="kcpf-property-image">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail('medium'); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="kcpf-property-content">
                <h2 class="kcpf-property-title">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </h2>
                
                <?php if ($multiUnitPrice) : ?>
                    <div class="kcpf-property-price kcpf-property-price-range">
                        <?php echo esc_html($multiUnitPrice); ?>
                    </div>
                <?php elseif ($price) : ?>
                    <div class="kcpf-property-price">
                        €<?php echo esc_html($price); ?>
                    </div>
                <?php endif; ?>
                
                <div class="kcpf-property-meta">
                    <?php if ($location && !is_wp_error($location)) : ?>
                        <span class="kcpf-location">
                            <i class="dashicons dashicons-location"></i>
                            <?php echo esc_html($location[0]->name); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($isMultiUnit && $multiUnitCount) : ?>
                        <span class="kcpf-multiunit-badge">
                            <i class="dashicons dashicons-admin-multisite"></i>
                            <?php echo esc_html($multiUnitCount); ?> Units
                        </span>
                    <?php else : ?>
                        <?php if ($bedrooms) : ?>
                            <span class="kcpf-bedrooms">
                                <i class="dashicons dashicons-admin-home"></i>
                                <?php echo esc_html($bedrooms); ?> Bed
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($bathrooms) : ?>
                            <span class="kcpf-bathrooms">
                                <i class="dashicons dashicons-admin-home"></i>
                                <?php echo esc_html($bathrooms); ?> Bath
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <?php if ($purpose && !is_wp_error($purpose)) : ?>
                    <div class="kcpf-property-purpose">
                        <?php echo esc_html($purpose[0]->name); ?>
                    </div>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }
    
    /**
     * Render pagination
     * 
     * @param WP_Query $query Query object
     */
    private static function renderPagination($query)
    {
        if ($query->max_num_pages <= 1) {
            return;
        }
        
        // Get current page from URL filters (works with AJAX)
        $filters = KCPF_URL_Manager::getCurrentFilters();
        $current_page = !empty($filters['paged']) ? intval($filters['paged']) : 1;
        
        echo '<nav class="kcpf-pagination">';
        
        for ($i = 1; $i <= $query->max_num_pages; $i++) {
            $filters['paged'] = $i;
            $url = KCPF_URL_Manager::buildFilterUrl($filters, false);
            $class = ($i === $current_page) ? 'current' : '';
            
            printf(
                '<a href="%s" class="kcpf-page-link %s">%d</a>',
                esc_url($url),
                esc_attr($class),
                $i
            );
        }
        
        echo '</nav>';
    }
    
    /**
     * Render no results message
     */
    private static function renderNoResults()
    {
        ?>
        <div class="kcpf-no-results">
            <p><?php esc_html_e('No properties found matching your criteria.', 'key-cy-properties-filter'); ?></p>
            <?php if (KCPF_URL_Manager::hasActiveFilters()) : ?>
                <a href="<?php echo esc_url(KCPF_URL_Manager::getResetUrl()); ?>" class="kcpf-reset-link">
                    <?php esc_html_e('Clear all filters', 'key-cy-properties-filter'); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}

