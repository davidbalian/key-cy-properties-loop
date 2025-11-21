/**
 * Language Detector JavaScript
 *
 * Detects browser language and sets language preference cookie
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Language Detector
   */
  const KCPFLanguageDetector = {
    /**
     * Initialize language detection
     */
    init: function () {
      // Check if language preference cookie already exists
      if (this.getCookie("kcpf_language")) {
        return; // Already set, don't override
      }

      // Detect browser language
      const browserLang = this.detectBrowserLanguage();
      if (browserLang && browserLang.startsWith("ru")) {
        // Set language preference via AJAX
        this.setLanguage("ru_RU");
      }
    },

    /**
     * Detect browser language from navigator
     *
     * @return {string|null} Language code or null
     */
    detectBrowserLanguage: function () {
      // Check navigator.languages (preferred method)
      if (navigator.languages && navigator.languages.length > 0) {
        for (let i = 0; i < navigator.languages.length; i++) {
          const lang = navigator.languages[i].toLowerCase();
          if (lang.startsWith("ru")) {
            return lang;
          }
        }
      }

      // Fallback to navigator.language
      if (navigator.language) {
        const lang = navigator.language.toLowerCase();
        if (lang.startsWith("ru")) {
          return lang;
        }
      }

      // Fallback to navigator.userLanguage (IE)
      if (navigator.userLanguage) {
        const lang = navigator.userLanguage.toLowerCase();
        if (lang.startsWith("ru")) {
          return lang;
        }
      }

      return null;
    },

    /**
     * Set language preference via AJAX
     *
     * @param {string} language Language code (e.g., 'ru_RU')
     */
    setLanguage: function (language) {
      if (!kcpfLanguageData) {
        console.warn("[KCPF Language] Language data not available");
        return;
      }

      $.ajax({
        url: kcpfLanguageData.ajaxUrl,
        type: "POST",
        data: {
          action: "kcpf_set_language",
          language: language,
          nonce: kcpfLanguageData.nonce,
        },
        success: function (response) {
          if (response.success) {
            console.log("[KCPF Language] Language preference set:", language);
            // Optionally reload page to apply translations
            // window.location.reload();
          }
        },
        error: function (xhr, status, error) {
          console.warn("[KCPF Language] Failed to set language:", error);
        },
      });
    },

    /**
     * Get cookie value
     *
     * @param {string} name Cookie name
     * @return {string|null} Cookie value or null
     */
    getCookie: function (name) {
      const value = "; " + document.cookie;
      const parts = value.split("; " + name + "=");
      if (parts.length === 2) {
        return parts.pop().split(";").shift();
      }
      return null;
    },
  };

  /**
   * Initialize when document is ready
   */
  $(document).ready(function () {
    KCPFLanguageDetector.init();
  });

  // Make globally available
  window.KCPFLanguageDetector = KCPFLanguageDetector;
})(jQuery);

