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
            // Add sale class if purpose is sale to force single column layout
            $purpose = isset($attrs['purpose']) ? $attrs['purpose'] : 'sale';
            $gridClass = ($purpose === 'sale') ? 'kcpf-properties-grid kcpf-grid-sale' : 'kcpf-properties-grid';
            echo '<div class="' . esc_attr($gridClass) . '">';
            
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
        
        // Check if this is a sale property
        $isSale = ($purposeSlug === 'sale');
        
        // Get formatted values using helper
        $bedrooms = KCPF_Card_Data_Helper::getBedrooms($property_id, $purposeSlug);
        $bathrooms = KCPF_Card_Data_Helper::getBathrooms($property_id, $purposeSlug);
        $price = KCPF_Card_Data_Helper::getPrice($property_id, $purposeSlug);
        
        // Check if multi-unit and get price if applicable
        $isMultiUnit = KCPF_Card_Data_Helper::isMultiUnit($property_id);
        $multiUnitPrice = $isMultiUnit ? KCPF_Card_Data_Helper::getMultiUnitPrice($property_id, $purposeSlug) : null;
        $multiUnitCount = $isMultiUnit ? KCPF_Card_Data_Helper::getMultiUnitCount($property_id) : null;
        $multiUnitTable = $isMultiUnit ? KCPF_Card_Data_Helper::getMultiUnitTable($property_id) : null;
        
        // Get additional data for sale properties
        $cityArea = $isSale ? KCPF_Card_Data_Helper::getCityArea($property_id) : null;
        $propertyType = $isSale ? KCPF_Card_Data_Helper::getPropertyType($property_id) : null;
        $totalCoveredArea = $isSale ? KCPF_Card_Data_Helper::getTotalCoveredArea($property_id, $purposeSlug) : null;
        
        // Render different layouts based on purpose
        if ($isSale) {
            self::renderSaleCard($property_id, $location, $price, $multiUnitPrice, $cityArea, $propertyType, $isMultiUnit, $multiUnitTable, $bedrooms, $bathrooms, $totalCoveredArea);
        } else {
            self::renderRentCard($property_id, $location, $purpose, $price, $isMultiUnit, $multiUnitCount, $bedrooms, $bathrooms);
        }
    }
    
    /**
     * Render card for sale properties with two-column layout
     */
    private static function renderSaleCard($property_id, $location, $price, $multiUnitPrice, $cityArea, $propertyType, $isMultiUnit, $multiUnitTable, $bedrooms, $bathrooms, $totalCoveredArea)
    {
        ?>
        <article class="kcpf-property-card kcpf-property-card-sale">
            <div class="kcpf-property-card-sale-wrapper">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="kcpf-property-image-sale">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('medium'); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="kcpf-property-content-sale">
                    <h2 class="kcpf-property-title">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h2>
                    
                    <div class="kcpf-property-meta-row">
                        <?php if ($cityArea) : ?>
                            <span class="kcpf-city-area"><?php echo esc_html($cityArea); ?></span>
                        <?php endif; ?>
                        
                        <?php if ($location && !is_wp_error($location)) : ?>
                            <span class="kcpf-separator">|</span>
                            <span class="kcpf-location"><?php echo esc_html($location[0]->name); ?></span>
                        <?php endif; ?>
                        
                        <?php if ($propertyType) : ?>
                            <span class="kcpf-separator">|</span>
                            <span class="kcpf-property-type"><?php echo esc_html($propertyType); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($multiUnitPrice) : ?>
                        <div class="kcpf-property-price kcpf-property-price-range">
                            <?php echo esc_html($multiUnitPrice); ?>
                        </div>
                    <?php elseif ($price) : ?>
                        <div class="kcpf-property-price">
                            €<?php echo esc_html($price); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($isMultiUnit && $multiUnitTable) : ?>
                        <div class="kcpf-multiunit-section">
                            <div class="kcpf-multiunit-header">
                                Units: <?php echo esc_html(count($multiUnitTable)); ?>
                            </div>
                            <?php self::renderMultiUnitTable($multiUnitTable, $propertyType); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="kcpf-property-specs">
                        <?php if ($bedrooms) : ?>
                            <span class="kcpf-bedrooms">
                                <?php echo esc_html($bedrooms); ?> Bed
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($bathrooms) : ?>
                            <span class="kcpf-bathrooms">
                                <?php echo esc_html($bathrooms); ?> Bath
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($totalCoveredArea) : ?>
                            <span class="kcpf-covered-area">
                                <?php echo esc_html($totalCoveredArea); ?> m²
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </article>
        <?php
    }
    
    /**
     * Render card for rent properties (original layout)
     */
    private static function renderRentCard($property_id, $location, $purpose, $price, $isMultiUnit, $multiUnitCount, $bedrooms, $bathrooms)
    {
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
                
                <?php if ($price) : ?>
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
     * Render multi-unit table
     */
    private static function renderMultiUnitTable($units, $propertyType)
    {
        // Limit to 3 rows
        $displayUnits = array_slice($units, 0, 3);
        
        // Check if this is land property
        $isLand = strtolower($propertyType) === 'land';
        
        ?>
        <table class="kcpf-multiunit-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <?php if ($isLand) : ?>
                        <th>Land Area</th>
                    <?php else : ?>
                        <th>Bedrooms</th>
                        <th>Bathrooms</th>
                        <th>Covered Area</th>
                    <?php endif; ?>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($displayUnits as $unit) : ?>
                    <tr>
                        <td><?php echo esc_html($unit['unit_id'] ?? ''); ?></td>
                        <td><?php echo esc_html($unit['unit_title'] ?? ''); ?></td>
                        <?php if ($isLand) : ?>
                            <td><?php echo esc_html($unit['unit_plot_area_for_lands'] ?? ''); ?></td>
                        <?php else : ?>
                            <td><?php echo esc_html($unit['unit_bedrooms'] ?? ''); ?></td>
                            <td><?php echo esc_html($unit['unit_bathrooms'] ?? ''); ?></td>
                            <td><?php echo esc_html($unit['unit_covered_area'] ?? ''); ?></td>
                        <?php endif; ?>
                        <td><?php echo esc_html($unit['unit_price'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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

