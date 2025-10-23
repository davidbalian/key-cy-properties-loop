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

if (!class_exists('KCPF_MultiUnit_Query_Builder')) {
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
        
        // Simplified: Just query the regular price field for now
        // Multi-unit properties also have a price field, so this will work for both
        $price_query = [];
        
        if ($minValue !== null && $maxValue !== null) {
            $price_query[] = [
                'key' => $priceKey,
                'value' => [$minValue, $maxValue],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN',
            ];
        } elseif ($minValue !== null) {
            $price_query[] = [
                'key' => $priceKey,
                'value' => $minValue,
                'type' => 'NUMERIC',
                'compare' => '>=',
            ];
        } elseif ($maxValue !== null) {
            $price_query[] = [
                'key' => $priceKey,
                'value' => $maxValue,
                'type' => 'NUMERIC',
                'compare' => '<=',
            ];
        }
        
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
        
        // Simplified: Just query the regular covered area field
        $area_query = [];
        
        if ($minValue !== null && $maxValue !== null) {
            $area_query[] = [
                'key' => $coveredAreaKey,
                'value' => [$minValue, $maxValue],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN',
            ];
        } elseif ($minValue !== null) {
            $area_query[] = [
                'key' => $coveredAreaKey,
                'value' => $minValue,
                'type' => 'NUMERIC',
                'compare' => '>=',
            ];
        } elseif ($maxValue !== null) {
            $area_query[] = [
                'key' => $coveredAreaKey,
                'value' => $maxValue,
                'type' => 'NUMERIC',
                'compare' => '<=',
            ];
        }
        
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

        // Simplified: Just query the regular plot area field
        $plot_query = [];

        if ($minValue !== null && $maxValue !== null) {
            $plot_query[] = [
                'key' => 'plot_area_land_only',
                'value' => [$minValue, $maxValue],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN',
            ];
        } elseif ($minValue !== null) {
            $plot_query[] = [
                'key' => 'plot_area_land_only',
                'value' => $minValue,
                'type' => 'NUMERIC',
                'compare' => '>=',
            ];
        } elseif ($maxValue !== null) {
            $plot_query[] = [
                'key' => 'plot_area_land_only',
                'value' => $maxValue,
                'type' => 'NUMERIC',
                'compare' => '<=',
            ];
        }

        return $plot_query;
    }

    /**
     * Build bedrooms query supporting both regular and multi-unit properties
     *
     * @param array $filters Current filters
     * @param string $purpose Current purpose (sale/rent)
     * @return array Meta query array
     */
    public static function buildBedroomsQuery($filters, $purpose)
    {
        if (!isset($filters['bedrooms']) || empty($filters['bedrooms'])) {
            return [];
        }

        $bedroomsKey = KCPF_Field_Config::getMetaKey('bedrooms', $purpose);
        // Bedrooms should already be an array from URL_Manager
        $bedroomsValues = $filters['bedrooms'];
        error_log('[KCPF] Processing bedrooms values: ' . print_r($bedroomsValues, true));
        error_log('[KCPF] Using meta key: ' . $bedroomsKey);

        // Use exact same query format as the working debug tool
        $bedrooms_query = [
            'key' => $bedroomsKey,
            'value' => '"' . $bedroomsValues[0] . '":"true"',
            'compare' => 'LIKE'
        ];

        // Fallback: try different serialization lengths for 9_plus
        if ($bedroomsValues[0] === '9_plus') {
            $bedrooms_query = [
                'relation' => 'OR',
                [
                    'key' => $bedroomsKey,
                    'value' => '"9_plus":"true"',
                    'compare' => 'LIKE',
                ],
                [
                    'key' => $bedroomsKey,
                    'value' => 's:6:"9_plus";s:4:"true"',
                    'compare' => 'LIKE',
                ],
                [
                    'key' => $bedroomsKey,
                    'value' => 's:6:"9_plus";b:1',
                    'compare' => 'LIKE',
                ]
            ];
        }

        return $bedrooms_query;
    }

    /**
     * Build bathrooms query supporting both regular and multi-unit properties
     *
     * @param array $filters Current filters
     * @param string $purpose Current purpose (sale/rent)
     * @return array Meta query array
     */
    public static function buildBathroomsQuery($filters, $purpose)
    {
        if (!isset($filters['bathrooms']) || empty($filters['bathrooms'])) {
            return [];
        }

        $bathroomsKey = KCPF_Field_Config::getMetaKey('bathrooms', $purpose);
        // Bathrooms should already be an array from URL_Manager
        $bathroomsValues = $filters['bathrooms'];
        error_log('[KCPF] Processing bathrooms values: ' . print_r($bathroomsValues, true));
        error_log('[KCPF] Using meta key: ' . $bathroomsKey);

        // Use exact same query format as the working debug tool
        $bathrooms_query = [
            'key' => $bathroomsKey,
            'value' => '"' . $bathroomsValues[0] . '":"true"',
            'compare' => 'LIKE'
        ];

        // Fallback: try different serialization lengths for 9_plus
        if ($bathroomsValues[0] === '9_plus') {
            $bathrooms_query = [
                'relation' => 'OR',
                [
                    'key' => $bathroomsKey,
                    'value' => '"9_plus":"true"',
                    'compare' => 'LIKE',
                ],
                [
                    'key' => $bathroomsKey,
                    'value' => 's:6:"9_plus";s:4:"true"',
                    'compare' => 'LIKE',
                ],
                [
                    'key' => $bathroomsKey,
                    'value' => 's:6:"9_plus";b:1',
                    'compare' => 'LIKE',
                ]
            ];
        }

        return $bathrooms_query;
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
        // Create nested meta_query for regular properties
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
        // Create nested meta_query for multi-unit properties
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
}

