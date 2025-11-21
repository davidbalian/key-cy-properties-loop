<?php
/**
 * Query Handler Class
 * 
 * Converts URL filter parameters to WP_Query arguments
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Query_Handler
{
    /**
     * Build WP_Query arguments from URL filters
     * 
     * @param array $attrs Shortcode attributes
     * @return array WP_Query arguments
     */
    public static function buildQueryArgs($attrs = [])
    {
        // Check if filtering is enabled (default to true for backward compatibility)
        $isFilterable = !isset($attrs['isFilterable']) || $attrs['isFilterable'] === 'true' || $attrs['isFilterable'] === true;
        
        // Only get filters if filtering is enabled
        $filters = $isFilterable ? KCPF_URL_Manager::getCurrentFilters() : [];
        
        $args = [
            'post_type' => 'properties',
            'post_status' => 'publish',
            'posts_per_page' => isset($attrs['posts_per_page']) ? intval($attrs['posts_per_page']) : 10,
            // Use paged from attrs if provided (for AJAX), otherwise from filters
            'paged' => !empty($attrs['paged']) ? intval($attrs['paged']) : (!empty($filters['paged']) ? intval($filters['paged']) : 1),
        ];
        
        // Property ID search - only apply if filtering is enabled
        if ($isFilterable && !empty($filters['property_id'])) {
            $args['post__in'] = [intval($filters['property_id'])];
        }
        
        // Build tax_query - only if filtering is enabled
        if ($isFilterable) {
            $tax_query = self::buildTaxQuery($filters, $attrs);
            if (!empty($tax_query)) {
                $args['tax_query'] = $tax_query;
            }
            
            // Build meta_query
            $purpose = self::getCurrentPurpose($filters, $attrs);
            $meta_query = self::buildMetaQuery($filters, $purpose);
            if (!empty($meta_query)) {
                $args['meta_query'] = $meta_query;
            }
        } else {
            // When not filterable, still apply purpose from shortcode attributes if specified
            if (!empty($attrs['purpose'])) {
                // Resolve term ID for robustness
                $ids = self::resolveTermId($attrs['purpose'], 'purpose');
                if ($ids) {
                    $args['tax_query'] = [
                        [
                            'taxonomy' => 'purpose',
                            'field' => 'term_id',
                            'terms' => $ids,
                        ]
                    ];
                }
            }
        }
        
        
        return $args;
    }
    
    /**
     * Build taxonomy query from filters
     * 
     * @param array $filters Current filters
     * @param array $attrs Shortcode attributes
     * @return array Tax query array
     */
    private static function buildTaxQuery($filters, $attrs)
    {
        $tax_query = ['relation' => 'AND'];
        
        // Location filter
        if (!empty($filters['location'])) {
            $ids = self::resolveTermId($filters['location'], 'location');
            if ($ids) {
                $tax_query[] = [
                    'taxonomy' => 'location',
                    'field' => 'term_id',
                    'terms' => $ids,
                ];
            }
        }
        
        // Purpose filter (sale/rent) - shortcode purpose overrides URL filters
        $purpose_slug = !empty($attrs['purpose']) ? $attrs['purpose'] : ($filters['purpose'] ?? 'sale');
        if ($purpose_slug) {
            $ids = self::resolveTermId($purpose_slug, 'purpose');
            if ($ids) {
                $tax_query[] = [
                    'taxonomy' => 'purpose',
                    'field' => 'term_id',
                    'terms' => $ids,
                ];
            }
        }
        
        // Property type filter
        if (!empty($filters['property_type'])) {
            $ids = self::resolveTermId($filters['property_type'], 'property-type');
            if ($ids) {
                $tax_query[] = [
                    'taxonomy' => 'property-type',
                    'field' => 'term_id',
                    'terms' => $ids,
                ];
            }
        }
        
        // Remove relation if no queries added
        if (count($tax_query) === 1) {
            return [];
        }
        
        return $tax_query;
    }
    
    /**
     * Build meta query from filters
     * 
     * @param array $filters Current filters
     * @param string $purpose Current purpose (sale/rent)
     * @return array Meta query array
     */
    private static function buildMetaQuery($filters, $purpose = 'sale')
    {
        $meta_query = ['relation' => 'AND'];
        
        // Price range filter - handle both regular and multi-unit properties
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $meta_query[] = KCPF_MultiUnit_Query_Builder::buildPriceQuery($filters, $purpose);
        }
        
        // Bedrooms filter - apply to both regular and multi-unit properties
        if (!empty($filters['bedrooms'])) {
            $bedrooms_query = KCPF_MultiUnit_Query_Builder::buildBedroomsQuery($filters, $purpose);
            if (!empty($bedrooms_query)) {
                $meta_query[] = $bedrooms_query;
            }
        }
        
        // Bathrooms filter - apply to both regular and multi-unit properties
        if (!empty($filters['bathrooms'])) {
            $bathrooms_query = KCPF_MultiUnit_Query_Builder::buildBathroomsQuery($filters, $purpose);
            if (!empty($bathrooms_query)) {
                $meta_query[] = $bathrooms_query;
            }
        }
        
        // Covered area filter - handle both regular and multi-unit properties
        if (!empty($filters['covered_area_min']) || !empty($filters['covered_area_max'])) {
            $meta_query[] = KCPF_MultiUnit_Query_Builder::buildCoveredAreaQuery($filters, $purpose);
        }
        
        // Plot area filter - handle both regular and multi-unit properties
        if (!empty($filters['plot_area_min']) || !empty($filters['plot_area_max'])) {
            $meta_query[] = KCPF_MultiUnit_Query_Builder::buildPlotAreaQuery($filters);
        }
        
        // Amenities filter
        if (!empty($filters['amenities'])) {
            $amenities = is_array($filters['amenities']) ? $filters['amenities'] : [$filters['amenities']];
            
            foreach ($amenities as $amenity) {
                $meta_query[] = [
                    'key' => 'amenities',
                    'value' => $amenity,
                    'compare' => 'LIKE',
                ];
            }
        }
        
        // Remove relation if no queries added
        if (count($meta_query) === 1) {
            return [];
        }
        
        return $meta_query;
    }
    
    /**
     * Get total count of filtered properties
     * 
     * @param array $attrs Shortcode attributes
     * @return int Total count
     */
    public static function getTotalCount($attrs = [])
    {
        $args = self::buildQueryArgs($attrs);
        $args['posts_per_page'] = -1;
        $args['fields'] = 'ids';
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    /**
     * Get current purpose from filters or shortcode attributes
     *
     * @param array $filters Current filters
     * @param array $attrs Shortcode attributes
     * @return string Purpose (sale or rent)
     */
    private static function getCurrentPurpose($filters, $attrs)
    {
        // Priority: shortcode attribute > URL filter > default (sale)
        if (!empty($attrs['purpose'])) {
            return $attrs['purpose'];
        }

        if (!empty($filters['purpose'])) {
            return $filters['purpose'];
        }

        return 'sale';
    }

    /**
     * Resolve term ID from slug (handling English/translated slugs)
     *
     * @param string|array $slug Term slug(s)
     * @param string $taxonomy Taxonomy
     * @return int|array|null Term ID(s) or null if not found
     */
    private static function resolveTermId($slug, $taxonomy)
    {
        if (empty($slug)) {
            return null;
        }

        // Handle array of slugs
        if (is_array($slug)) {
            $ids = [];
            foreach ($slug as $s) {
                $id = self::resolveSingleTermId($s, $taxonomy);
                if ($id) {
                    $ids[] = $id;
                }
            }
            return !empty($ids) ? $ids : null;
        }

        return self::resolveSingleTermId($slug, $taxonomy);
    }

    /**
     * Resolve single term ID
     *
     * @param string $slug Term slug
     * @param string $taxonomy Taxonomy
     * @return int|null Term ID
     */
    private static function resolveSingleTermId($slug, $taxonomy)
    {
        // 1. Try normal lookup (works if slug matches current language term)
        $term = get_term_by('slug', $slug, $taxonomy);
        if ($term) {
            // Translate the ID to current language (just to be safe/consistent)
            if (function_exists('icl_object_id')) {
                return icl_object_id($term->term_id, $taxonomy, true);
            }
            return $term->term_id;
        }

        // 2. Try looking up by English slug if we are in another language
        // We use get_terms with suppress_filter to find the term regardless of language
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'slug' => $slug,
            'hide_empty' => false,
            'suppress_filters' => true, // Bypass WPML language filtering
        ]);
        
        if (!empty($terms) && !is_wp_error($terms)) {
            $term = reset($terms);
            // Translate the ID to current language
            if (function_exists('icl_object_id')) {
                return icl_object_id($term->term_id, $taxonomy, true);
            }
            return $term->term_id;
        }
        
        return null;
    }
}
