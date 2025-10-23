<?php
/**
 * URL Manager Class
 * 
 * Handles reading and writing filter parameters to/from URL
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_URL_Manager
{
    /**
     * Get current filter values from URL
     * 
     * @return array Filter values
     */
    public static function getCurrentFilters()
    {
        $filters = [
            'location' => self::getParam('location'),
            'purpose' => self::getParam('purpose'),
            'property_type' => self::getParam('property_type'),
            'price_min' => self::getParam('price_min'),
            'price_max' => self::getParam('price_max'),
            'bedrooms' => self::getParam('bedrooms'),
            'bathrooms' => self::getParam('bathrooms'),
            'amenities' => self::getParam('amenities'),
            'covered_area_min' => self::getParam('covered_area_min'),
            'covered_area_max' => self::getParam('covered_area_max'),
            'plot_area_min' => self::getParam('plot_area_min'),
            'plot_area_max' => self::getParam('plot_area_max'),
            'property_id' => self::getParam('property_id'),
            'paged' => self::getParam('paged', 1),
        ];
        
        // Log bedroom and bathroom filters specifically
        error_log('[KCPF] URL_Manager - Checking all $_GET keys: ' . print_r(array_keys($_GET), true));
        error_log('[KCPF] URL_Manager - Raw $_GET bedrooms: ' . print_r($_GET['bedrooms'] ?? 'NOT_SET', true));
        error_log('[KCPF] URL_Manager - Raw $_GET bathrooms: ' . print_r($_GET['bathrooms'] ?? 'NOT_SET', true));
        error_log('[KCPF] URL_Manager - Raw $_GET bedrooms[]: ' . print_r($_GET['bedrooms[]'] ?? 'NOT_SET', true));
        error_log('[KCPF] URL_Manager - Raw $_GET bathrooms[]: ' . print_r($_GET['bathrooms[]'] ?? 'NOT_SET', true));
        error_log('[KCPF] URL_Manager - Processed bedrooms: ' . print_r($filters['bedrooms'], true));
        error_log('[KCPF] URL_Manager - Processed bathrooms: ' . print_r($filters['bathrooms'], true));
        error_log('[KCPF] URL_Manager - Bedrooms empty check: ' . (empty($filters['bedrooms']) ? 'EMPTY' : 'NOT_EMPTY'));
        error_log('[KCPF] URL_Manager - Bathrooms empty check: ' . (empty($filters['bathrooms']) ? 'EMPTY' : 'NOT_EMPTY'));
        
        return $filters;
    }
    
    /**
     * Get a single parameter from URL
     *
     * @param string $key Parameter key
     * @param mixed $default Default value
     * @return mixed Parameter value
     */
    public static function getParam($key, $default = '')
    {
        // Special handling for bedrooms and bathrooms to collect multiple values
        if ($key === 'bedrooms' || $key === 'bathrooms') {
            // Check for multiple values with same key (bedroom=2&bedroom=3&bedroom=5)
            $multiple_values = [];

            // Parse query string to find all instances of this parameter
            if (isset($_SERVER['QUERY_STRING'])) {
                parse_str($_SERVER['QUERY_STRING'], $query_params);
                if (isset($query_params[$key]) && is_array($query_params[$key])) {
                    $multiple_values = array_map('sanitize_text_field', $query_params[$key]);
                } elseif (isset($query_params[$key]) && !empty($query_params[$key])) {
                    $multiple_values = [sanitize_text_field($query_params[$key])];
                }
            }

            // Debug logging
            error_log("[KCPF] getParam called for: $key");
            error_log("[KCPF] Multiple values found: " . print_r($multiple_values, true));
            error_log("[KCPF] isset(\$_GET[$key]): " . (isset($_GET[$key]) ? 'true' : 'false'));
            if (isset($_GET[$key])) {
                error_log("[KCPF] \$_GET[$key] value: " . print_r($_GET[$key], true));
                error_log("[KCPF] \$_GET[$key] type: " . gettype($_GET[$key]));
            }

            if (!empty($multiple_values)) {
                return count($multiple_values) === 1 ? $multiple_values[0] : $multiple_values;
            }
        }

        if (!isset($_GET[$key]) || $_GET[$key] === '') {
            return $default;
        }

        // Handle array parameters (for checkboxes with [])
        if (is_array($_GET[$key])) {
            return array_map('sanitize_text_field', $_GET[$key]);
        }

        return sanitize_text_field($_GET[$key]);
    }
    
    /**
     * Build URL with updated filters
     * 
     * @param array $filters Filters to add/update
     * @param bool $merge Merge with existing filters or replace
     * @return string URL with filters
     */
    public static function buildFilterUrl($filters = [], $merge = true)
    {
        $current = $merge ? self::getCurrentFilters() : [];
        $updated = array_merge($current, $filters);
        
        // Remove empty values
        $updated = array_filter($updated, function($value) {
            return $value !== '' && $value !== null;
        });
        
        // Get current URL without query string
        $base_url = self::getBaseUrl();
        
        // Build query string
        if (!empty($updated)) {
            return add_query_arg($updated, $base_url);
        }
        
        return $base_url;
    }
    
    /**
     * Get base URL without query parameters
     * 
     * @return string Base URL
     */
    public static function getBaseUrl()
    {
        global $wp;
        return home_url($wp->request);
    }
    
    /**
     * Get reset URL (clears all filters)
     * 
     * @return string Reset URL
     */
    public static function getResetUrl()
    {
        return self::getBaseUrl();
    }
    
    /**
     * Check if any filters are active
     * 
     * @return bool Whether filters are active
     */
    public static function hasActiveFilters()
    {
        $filters = self::getCurrentFilters();
        unset($filters['paged']); // Don't count pagination
        
        foreach ($filters as $value) {
            if ($value !== '' && $value !== null) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get filter value for a specific key
     * 
     * @param string $key Filter key
     * @return mixed Filter value
     */
    public static function getFilterValue($key)
    {
        $filters = self::getCurrentFilters();
        return $filters[$key] ?? '';
    }
}

