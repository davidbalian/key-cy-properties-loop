<?php
/**
 * Language Detector Class
 * 
 * Detects browser language and applies Russian translations when appropriate
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Language_Detector
{
    /**
     * Cookie name for language preference
     */
    const COOKIE_NAME = 'kcpf_language';
    
    /**
     * Initialize language detection
     */
    public static function init()
    {
        // Hook into locale filter early (priority 1 to run before other plugins)
        add_filter('locale', [__CLASS__, 'detectLanguage'], 1);
        
        // Register AJAX handler for setting language preference
        add_action('wp_ajax_kcpf_set_language', [__CLASS__, 'ajaxSetLanguage']);
        add_action('wp_ajax_nopriv_kcpf_set_language', [__CLASS__, 'ajaxSetLanguage']);
    }
    
    /**
     * Detect language and return locale
     * 
     * Priority: Cookie > Browser Header > JavaScript (via cookie) > WordPress locale
     * 
     * @param string $locale Current WordPress locale
     * @return string Detected locale (ru_RU if Russian detected, otherwise original)
     */
    public static function detectLanguage($locale)
    {
        // Check cookie preference first (highest priority)
        $cookie_lang = self::getCookieLanguage();
        if ($cookie_lang === 'ru_RU') {
            return 'ru_RU';
        }
        
        // Check Accept-Language header
        $browser_lang = self::getBrowserLanguage();
        if ($browser_lang === 'ru_RU') {
            return 'ru_RU';
        }
        
        // Check JavaScript-set cookie (set by client-side detection)
        $js_lang = self::getJSLanguage();
        if ($js_lang === 'ru_RU') {
            return 'ru_RU';
        }
        
        // Return original WordPress locale
        return $locale;
    }
    
    /**
     * Get language from cookie preference
     * 
     * @return string|null Language code or null
     */
    private static function getCookieLanguage()
    {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            $lang = sanitize_text_field($_COOKIE[self::COOKIE_NAME]);
            if ($lang === 'ru_RU' || $lang === 'ru') {
                return 'ru_RU';
            }
        }
        return null;
    }
    
    /**
     * Get language from Accept-Language HTTP header
     * 
     * @return string|null Language code or null
     */
    private static function getBrowserLanguage()
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }
        
        $accept_lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        
        // Parse Accept-Language header
        // Format: "en-US,en;q=0.9,ru;q=0.8"
        $languages = [];
        if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/i', $accept_lang, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $lang = strtolower($matches[1][$i]);
                $q = isset($matches[2][$i]) && $matches[2][$i] !== '' ? floatval($matches[2][$i]) : 1.0;
                $languages[$lang] = $q;
            }
            
            // Sort by quality value (descending)
            arsort($languages);
            
            // Check if Russian is in the list
            foreach ($languages as $lang => $q) {
                // Check for Russian variants: ru, ru-RU, ru-BY, ru-KZ, etc.
                if (strpos($lang, 'ru') === 0) {
                    return 'ru_RU';
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get language from JavaScript-set cookie
     * 
     * JavaScript detection sets a cookie with 'js_' prefix
     * 
     * @return string|null Language code or null
     */
    private static function getJSLanguage()
    {
        $js_cookie_name = 'js_' . self::COOKIE_NAME;
        if (isset($_COOKIE[$js_cookie_name])) {
            $lang = sanitize_text_field($_COOKIE[$js_cookie_name]);
            if ($lang === 'ru_RU' || $lang === 'ru') {
                return 'ru_RU';
            }
        }
        return null;
    }
    
    /**
     * AJAX handler to set language preference cookie
     */
    public static function ajaxSetLanguage()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'kcpf_language_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }
        
        // Get language from request
        $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : '';
        
        // Validate language
        if ($language !== 'ru_RU' && $language !== 'ru' && $language !== 'en_US' && $language !== 'en') {
            wp_send_json_error(['message' => 'Invalid language code']);
            return;
        }
        
        // Normalize to locale format
        if ($language === 'ru') {
            $language = 'ru_RU';
        } elseif ($language === 'en') {
            $language = 'en_US';
        }
        
        // Set cookie (expires in 1 year)
        $cookie_expiry = time() + (365 * 24 * 60 * 60);
        setcookie(self::COOKIE_NAME, $language, $cookie_expiry, '/', '', is_ssl(), true);
        
        // Also set in $_COOKIE for immediate access
        $_COOKIE[self::COOKIE_NAME] = $language;
        
        wp_send_json_success([
            'message' => 'Language preference saved',
            'language' => $language
        ]);
    }
    
    /**
     * Check if current language is Russian
     * 
     * @return bool
     */
    public static function isRussian()
    {
        $locale = apply_filters('locale', get_locale());
        return $locale === 'ru_RU';
    }
}

