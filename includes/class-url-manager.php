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
        return [
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
        if (!isset($_GET[$key]) || $_GET[$key] === '') {
            return $default;
        }
        
        // Handle array parameters (for checkboxes)
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

