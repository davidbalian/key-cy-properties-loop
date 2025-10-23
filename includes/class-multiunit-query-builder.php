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

        // Use multiple query formats to match any data format (like the debug tool)
        $bedrooms_query = ['relation' => 'OR'];

        foreach ($bedroomsValues as $bedroom) {
            // JSON format: "5":"true"
            $bedrooms_query[] = [
                'key' => $bedroomsKey,
                'value' => '"' . $bedroom . '":"true"',
                'compare' => 'LIKE',
            ];

            // JSON format with spaces: "5": "true"
            $bedrooms_query[] = [
                'key' => $bedroomsKey,
                'value' => '"' . $bedroom . '": "true"',
                'compare' => 'LIKE',
            ];

            // Boolean true: "5":true
            $bedrooms_query[] = [
                'key' => $bedroomsKey,
                'value' => '"' . $bedroom . '":true',
                'compare' => 'LIKE',
            ];

            // PHP serialized format: s:1:"5";s:4:"true"
            $bedrooms_query[] = [
                'key' => $bedroomsKey,
                'value' => 's:' . strlen($bedroom) . ':"' . $bedroom . '";s:4:"true"',
                'compare' => 'LIKE',
            ];

            // PHP serialized boolean: s:1:"5";b:1
            $bedrooms_query[] = [
                'key' => $bedroomsKey,
                'value' => 's:' . strlen($bedroom) . ':"' . $bedroom . '";b:1',
                'compare' => 'LIKE',
            ];

            // Fallback for 8_plus and 9_plus
            if ($bedroom === '8_plus') {
                $bedrooms_query[] = [
                    'key' => $bedroomsKey,
                    'value' => 's:6:"8_plus";s:4:"true"',
                    'compare' => 'LIKE',
                ];
                $bedrooms_query[] = [
                    'key' => $bedroomsKey,
                    'value' => 's:6:"8_plus";b:1',
                    'compare' => 'LIKE',
                ];
            } elseif ($bedroom === '9_plus') {
                $bedrooms_query[] = [
                    'key' => $bedroomsKey,
                    'value' => 's:6:"9_plus";s:4:"true"',
                    'compare' => 'LIKE',
                ];
                $bedrooms_query[] = [
                    'key' => $bedroomsKey,
                    'value' => 's:6:"9_plus";b:1',
                    'compare' => 'LIKE',
                ];
            }
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

        // Use multiple query formats to match any data format (like the debug tool)
        $bathrooms_query = ['relation' => 'OR'];

        foreach ($bathroomsValues as $bathroom) {
            // JSON format: "5":"true"
            $bathrooms_query[] = [
                'key' => $bathroomsKey,
                'value' => '"' . $bathroom . '":"true"',
                'compare' => 'LIKE',
            ];

            // JSON format with spaces: "5": "true"
            $bathrooms_query[] = [
                'key' => $bathroomsKey,
                'value' => '"' . $bathroom . '": "true"',
                'compare' => 'LIKE',
            ];

            // Boolean true: "5":true
            $bathrooms_query[] = [
                'key' => $bathroomsKey,
                'value' => '"' . $bathroom . '":true',
                'compare' => 'LIKE',
            ];

            // PHP serialized format: s:1:"5";s:4:"true"
            $bathrooms_query[] = [
                'key' => $bathroomsKey,
                'value' => 's:' . strlen($bathroom) . ':"' . $bathroom . '";s:4:"true"',
                'compare' => 'LIKE',
            ];

            // PHP serialized boolean: s:1:"5";b:1
            $bathrooms_query[] = [
                'key' => $bathroomsKey,
                'value' => 's:' . strlen($bathroom) . ':"' . $bathroom . '";b:1',
                'compare' => 'LIKE',
            ];

            // Fallback for 8_plus and 9_plus
            if ($bathroom === '8_plus') {
                $bathrooms_query[] = [
                    'key' => $bathroomsKey,
                    'value' => 's:6:"8_plus";s:4:"true"',
                    'compare' => 'LIKE',
                ];
                $bathrooms_query[] = [
                    'key' => $bathroomsKey,
                    'value' => 's:6:"8_plus";b:1',
                    'compare' => 'LIKE',
                ];
            } elseif ($bathroom === '9_plus') {
                $bathrooms_query[] = [
                    'key' => $bathroomsKey,
                    'value' => 's:6:"9_plus";s:4:"true"',
                    'compare' => 'LIKE',
                ];
                $bathrooms_query[] = [
                    'key' => $bathroomsKey,
                    'value' => 's:6:"9_plus";b:1',
                    'compare' => 'LIKE',
                ];
            }
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

