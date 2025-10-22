<?php
/**
 * Multi-Unit Query Builder Class
 * 
 * Handles query building for multi-unit properties with min/max ranges
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_MultiUnit_Query_Builder
{
    /**
     * Build price query supporting both regular and multi-unit properties
     * 
     * @param array $filters Current filters
     * @param string $purpose Current purpose (sale/rent)
     * @return array Meta query array
     */
    public static function buildPriceQuery($filters, $purpose)
    {
        $priceKey = KCPF_Field_Config::getMetaKey('price', $purpose);
        $minValue = isset($filters['price_min']) && $filters['price_min'] !== '' ? intval($filters['price_min']) : null;
        $maxValue = isset($filters['price_max']) && $filters['price_max'] !== '' ? intval($filters['price_max']) : null;
        
        // Build query for both regular and multi-unit properties
        $price_query = ['relation' => 'OR'];
        
        // Add regular property query
        $price_query[] = self::buildRegularPropertyQuery($priceKey, $minValue, $maxValue);
        
        // Add multi-unit property query
        $price_query[] = self::buildMultiUnitPropertyQuery('minimum_buy_price', 'maximum_buy_price', $minValue, $maxValue);
        
        return $price_query;
    }
    
    /**
     * Build covered area query supporting both regular and multi-unit properties
     * 
     * @param array $filters Current filters
     * @param string $purpose Current purpose (sale/rent)
     * @return array Meta query array
     */
    public static function buildCoveredAreaQuery($filters, $purpose)
    {
        $coveredAreaKey = KCPF_Field_Config::getMetaKey('covered_area', $purpose);
        $minValue = isset($filters['covered_area_min']) && $filters['covered_area_min'] !== '' ? intval($filters['covered_area_min']) : null;
        $maxValue = isset($filters['covered_area_max']) && $filters['covered_area_max'] !== '' ? intval($filters['covered_area_max']) : null;
        
        // Build query for both regular and multi-unit properties
        $area_query = ['relation' => 'OR'];
        
        // Add regular property query
        $area_query[] = self::buildRegularPropertyQuery($coveredAreaKey, $minValue, $maxValue);
        
        // Add multi-unit property query
        $area_query[] = self::buildMultiUnitPropertyQuery('minimum_covered_area', 'maximum_covered_area', $minValue, $maxValue);
        
        return $area_query;
    }
    
    /**
     * Build plot area query supporting both regular and multi-unit properties
     * 
     * @param array $filters Current filters
     * @return array Meta query array
     */
    public static function buildPlotAreaQuery($filters)
    {
        $minValue = isset($filters['plot_area_min']) && $filters['plot_area_min'] !== '' ? intval($filters['plot_area_min']) : null;
        $maxValue = isset($filters['plot_area_max']) && $filters['plot_area_max'] !== '' ? intval($filters['plot_area_max']) : null;
        
        // Build query for both regular and multi-unit properties
        $plot_query = ['relation' => 'OR'];
        
        // Add regular property query
        $plot_query[] = self::buildRegularPropertyQuery('plot_area_land_only', $minValue, $maxValue);
        
        // Add multi-unit property query
        $plot_query[] = self::buildMultiUnitPropertyQuery('minimum_plot_area', 'maximum_plot_area', $minValue, $maxValue);
        
        return $plot_query;
    }
    
    /**
     * Build query for regular properties (non multi-unit)
     * 
     * @param string $metaKey Meta field key
     * @param int|null $minValue Minimum filter value
     * @param int|null $maxValue Maximum filter value
     * @return array Meta query array
     */
    private static function buildRegularPropertyQuery($metaKey, $minValue, $maxValue)
    {
        $regular_query = ['relation' => 'AND'];
        
        // Check that multi-unit is not set or is false
        $regular_query[] = [
            'relation' => 'OR',
            [
                'key' => 'multi-unit',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key' => 'multi-unit',
                'value' => '1',
                'compare' => '!=',
            ],
        ];
        
        // Add value condition for regular properties
        // Only add the condition if at least one value is provided
        if ($minValue !== null && $maxValue !== null) {
            // Both values provided - use BETWEEN
            $regular_query[] = [
                'key' => $metaKey,
                'value' => [$minValue, $maxValue],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN',
            ];
        } elseif ($minValue !== null) {
            // Only min value provided - use >=
            $regular_query[] = [
                'key' => $metaKey,
                'value' => $minValue,
                'type' => 'NUMERIC',
                'compare' => '>=',
            ];
        } elseif ($maxValue !== null) {
            // Only max value provided - use <=
            $regular_query[] = [
                'key' => $metaKey,
                'value' => $maxValue,
                'type' => 'NUMERIC',
                'compare' => '<=',
            ];
        }
        // If both are null, don't add any value condition
        
        return $regular_query;
    }
    
    /**
     * Build query for multi-unit properties
     * 
     * @param string $minMetaKey Minimum value meta field key
     * @param string $maxMetaKey Maximum value meta field key
     * @param int|null $minValue Minimum filter value
     * @param int|null $maxValue Maximum filter value
     * @return array Meta query array
     */
    private static function buildMultiUnitPropertyQuery($minMetaKey, $maxMetaKey, $minValue, $maxValue)
    {
        $multiunit_query = ['relation' => 'AND'];
        
        // Check that multi-unit is true
        $multiunit_query[] = [
            'key' => 'multi-unit',
            'value' => '1',
            'compare' => '=',
        ];
        
        // Add conditions for minimum field
        if ($minValue) {
            $multiunit_query[] = [
                'key' => $minMetaKey,
                'value' => $minValue,
                'type' => 'NUMERIC',
                'compare' => '>=',
            ];
        }
        
        // Add conditions for maximum field
        if ($maxValue) {
            $multiunit_query[] = [
                'key' => $maxMetaKey,
                'value' => $maxValue,
                'type' => 'NUMERIC',
                'compare' => '<=',
            ];
        }
        
        return $multiunit_query;
    }
}

