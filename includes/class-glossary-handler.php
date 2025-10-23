<?php
/**
 * Glossary Handler Class
 * 
 * Fetches values from JetEngine glossaries
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Glossary_Handler
{
    /**
     * Get glossary options from JetEngine by glossary ID
     * 
     * @param string|int $glossaryId Glossary ID
     * @return array Array of options [value => label]
     */
    public static function getGlossaryOptions($glossaryId)
    {
        try {
            if (!function_exists('jet_engine')) {
                error_log('KCPF Glossary Handler: JetEngine is not available');
                return [];
            }
            
            if (empty($glossaryId)) {
                error_log('KCPF Glossary Handler: Empty glossary ID provided');
                return [];
            }
            
            // Use the correct JetEngine method to get glossary options
            $options = jet_engine()->glossaries->filters->get_glossary_options($glossaryId);
            
            error_log('[KCPF] Glossary Handler - ID: ' . $glossaryId . ', Options: ' . print_r($options, true));
            
            if (empty($options)) {
                error_log('KCPF Glossary Handler: No options found for glossary ID "' . $glossaryId . '"');
                return [];
            }
            
            return $options;
        } catch (Exception $e) {
            error_log('KCPF Glossary Handler Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get glossary options formatted for select/checkbox rendering
     * 
     * @param string|int $glossaryId Glossary ID
     * @return array Array of options with 'value' and 'label' keys
     */
    public static function getOptionsForRendering($glossaryId)
    {
        $options = self::getGlossaryOptions($glossaryId);
        $formatted = [];
        
        foreach ($options as $value => $label) {
            $formatted[] = [
                'value' => $value,
                'label' => $label,
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Check if JetEngine is available
     * 
     * @return bool
     */
    public static function isJetEngineAvailable()
    {
        return function_exists('jet_engine');
    }
}

