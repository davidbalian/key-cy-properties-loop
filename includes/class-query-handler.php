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
        $filters = KCPF_URL_Manager::getCurrentFilters();
        
        error_log('[KCPF] Query Handler - Filters from URL_Manager: ' . print_r($filters, true));
        
        $args = [
            'post_type' => 'properties',
            'post_status' => 'publish',
            'posts_per_page' => isset($attrs['posts_per_page']) ? intval($attrs['posts_per_page']) : 10,
            'paged' => !empty($filters['paged']) ? intval($filters['paged']) : 1,
        ];
        
        // Property ID search
        if (!empty($filters['property_id'])) {
            $args['post__in'] = [intval($filters['property_id'])];
        }
        
        // Build tax_query
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
        
        error_log('[KCPF] Query Handler - Final query args: ' . print_r($args, true));
        
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
            $tax_query[] = [
                'taxonomy' => 'location',
                'field' => 'slug',
                'terms' => $filters['location'],
            ];
        }
        
        // Purpose filter (sale/rent)
        $purpose = !empty($filters['purpose']) ? $filters['purpose'] : ($attrs['purpose'] ?? 'sale');
        if ($purpose) {
            $tax_query[] = [
                'taxonomy' => 'purpose',
                'field' => 'slug',
                'terms' => $purpose,
            ];
        }
        
        // Property type filter
        if (!empty($filters['property_type'])) {
            $tax_query[] = [
                'taxonomy' => 'property-type',
                'field' => 'slug',
                'terms' => $filters['property_type'],
            ];
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
            $bedroomsKey = KCPF_Field_Config::getMetaKey('bedrooms', $purpose);
            $bedroomsValues = is_array($filters['bedrooms']) ? $filters['bedrooms'] : [$filters['bedrooms']];
            
            // Build OR query for bedrooms (works for all property types)
            $bedrooms_query = ['relation' => 'OR'];
            
            // Add each bedroom value as an OR condition
            foreach ($bedroomsValues as $bedroom) {
                $bedrooms_query[] = [
                    'key' => $bedroomsKey,
                    'value' => $bedroom,
                    'compare' => '=',
                ];
            }
            
            $meta_query[] = $bedrooms_query;
        }
        
        // Bathrooms filter - apply to both regular and multi-unit properties
        if (!empty($filters['bathrooms'])) {
            $bathroomsKey = KCPF_Field_Config::getMetaKey('bathrooms', $purpose);
            $bathroomsValues = is_array($filters['bathrooms']) ? $filters['bathrooms'] : [$filters['bathrooms']];
            
            // Build OR query for bathrooms (works for all property types)
            $bathrooms_query = ['relation' => 'OR'];
            
            // Add each bathroom value as an OR condition
            foreach ($bathroomsValues as $bathroom) {
                $bathrooms_query[] = [
                    'key' => $bathroomsKey,
                    'value' => $bathroom,
                    'compare' => '=',
                ];
            }
            
            $meta_query[] = $bathrooms_query;
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
        // Priority: URL filter > shortcode attribute > default (sale)
        if (!empty($filters['purpose'])) {
            return $filters['purpose'];
        }
        
        if (!empty($attrs['purpose'])) {
            return $attrs['purpose'];
        }
        
        return 'sale';
    }
}

