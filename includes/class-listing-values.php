<?php
/**
 * Listing Values Class
 * 
 * Gets min/max values from actual listings for dynamic filter ranges
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Listing_Values
{
    /**
     * Cache for min/max values
     * 
     * @var array
     */
    private static $cache = [];
    
    /**
     * Get min and max values for a numeric field
     * 
     * @param string $field Base field name (price, covered_area, plot_area)
     * @param string $purpose Property purpose (sale or rent)
     * @return array Associative array with 'min' and 'max' keys
     */
    public static function getMinMax($field, $purpose = 'sale')
    {
        $cache_key = $field . '_' . $purpose;
        
        // Return cached value if available
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }
        
        // Get the correct meta key for this field and purpose
        $meta_key = self::getMetaKey($field, $purpose);
        
        // Query to get min and max values
        global $wpdb;
        
        // Build query with purpose taxonomy filter
        $query = "SELECT 
                MIN(CAST(pm.meta_value AS UNSIGNED)) as min_value,
                MAX(CAST(pm.meta_value AS UNSIGNED)) as max_value
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE pm.meta_key = %s
            AND p.post_type = 'properties'
            AND p.post_status = 'publish'
            AND pm.meta_value != ''
            AND pm.meta_value IS NOT NULL
            AND pm.meta_value REGEXP '^[0-9]+$'
            AND CAST(pm.meta_value AS UNSIGNED) > 0
            AND tt.taxonomy = 'purpose'
            AND t.slug = %s";
        
        $query = $wpdb->prepare($query, $meta_key, $purpose);
        
        $result = $wpdb->get_row($query, ARRAY_A);
        
        // If no results, return reasonable defaults
        if (!$result || !isset($result['min_value']) || !isset($result['max_value'])) {
            $defaults = self::getDefaults($field);
            self::$cache[$cache_key] = $defaults;
            return $defaults;
        }
        
        $values = [
            'min' => intval($result['min_value']),
            'max' => intval($result['max_value']),
        ];
        
        // Cache the result
        self::$cache[$cache_key] = $values;
        
        return $values;
    }
    
    /**
     * Get the correct meta key based on field and purpose
     * 
     * @param string $field Base field name
     * @param string $purpose Property purpose
     * @return string Meta key
     */
    private static function getMetaKey($field, $purpose)
    {
        // Use Field_Config to get the correct meta key
        if (class_exists('KCPF_Field_Config')) {
            return KCPF_Field_Config::getMetaKey($field, $purpose);
        }
        
        // Fallback direct mapping
        $purpose = strtolower($purpose);
        
        if ($purpose === 'rent') {
            $mapping = [
                'price' => 'rent_price',
                'covered_area' => 'rent_area',
            ];
            return $mapping[$field] ?? $field;
        }
        
        $mapping = [
            'price' => 'price',
            'covered_area' => 'total_covered_area',
            'plot_area' => 'plot_area_land_only',
        ];
        
        return $mapping[$field] ?? $field;
    }
    
    /**
     * Get default min/max values for a field
     * 
     * @param string $field Field name
     * @return array Associative array with 'min' and 'max' keys
     */
    private static function getDefaults($field)
    {
        $defaults = [
            'price' => ['min' => 0, 'max' => 10000000],
            'covered_area' => ['min' => 0, 'max' => 10000],
            'plot_area' => ['min' => 0, 'max' => 50000],
        ];
        
        return $defaults[$field] ?? ['min' => 0, 'max' => 10000];
    }
    
    /**
     * Clear cache (useful for testing or when listings change)
     */
    public static function clearCache()
    {
        self::$cache = [];
    }
}

