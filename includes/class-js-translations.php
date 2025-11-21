<?php
/**
 * JavaScript Translations Class
 * 
 * Provides translations for JavaScript strings
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_JS_Translations
{
    /**
     * Get all JavaScript translations
     * 
     * @return array Translation strings
     */
    public static function getTranslations()
    {
        return [
            'loadMore' => __('Load More', 'key-cy-properties-filter'),
            'propertyFound' => __('property found', 'key-cy-properties-filter'),
            'propertiesFound' => __('properties found', 'key-cy-properties-filter'),
            'mapFailedToLoad' => __('Map failed to load. Please refresh the page.', 'key-cy-properties-filter'),
            'requestTimedOut' => __('Request timed out. Please try again.', 'key-cy-properties-filter'),
            'networkError' => __('Network error. Please check your connection and try again.', 'key-cy-properties-filter'),
            'errorLoadingProperties' => __('An error occurred while loading properties', 'key-cy-properties-filter'),
            'errorOccurred' => __('An error occurred', 'key-cy-properties-filter'),
        ];
    }
}

