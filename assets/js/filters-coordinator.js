/**
 * Key CY Properties Filter - Coordinator
 * Main initialization and coordination of all filter modules
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Main Filters Coordinator
   */
  window.KCPF_FiltersCoordinator = {
    /**
     * Initialize all filter modules
     */
    init: function () {
      console.log("[KCPF] Initializing all filter modules...");

      // Initialize Form Manager
      if (window.KCPF_FormManager) {
        KCPF_FormManager.init();
      }

      // Initialize AJAX Handler
      if (window.KCPF_AjaxHandler) {
        KCPF_AjaxHandler.init();
      }

      // Initialize Toggle Handler
      if (window.KCPF_ToggleHandler) {
        KCPF_ToggleHandler.init();
      }

      // Initialize Range Sliders
      if (window.KCPF_RangeSliders) {
        KCPF_RangeSliders.init();
      }

      // Initialize Multiselect Handler
      if (window.KCPF_MultiselectHandler) {
        console.log("[KCPF] Initializing Multiselect Handler...");
        KCPF_MultiselectHandler.init();
      } else {
        console.error("[KCPF] KCPF_MultiselectHandler not found!");
      }

      // Initialize Infinite Scroll
      if (window.KCPF_InfiniteScroll) {
        KCPF_InfiniteScroll.init();
      }

      // Initialize Homepage Manager
      if (window.KCPF_HomepageManager) {
        KCPF_HomepageManager.init();
      }

      console.log("[KCPF] All filter modules initialized successfully");

      // Test AJAX endpoint after initialization
      this.testAjaxEndpoint();
    },

    /**
     * Test AJAX endpoint after initialization
     */
    testAjaxEndpoint: function () {
      setTimeout(function () {
        if (window.KCPF_AjaxHandler) {
          KCPF_AjaxHandler.testEndpoint();
        }
      }, 1000);
    },
  };

  /**
   * Initialize on document ready
   */
  $(document).ready(function () {
    KCPF_FiltersCoordinator.init();
  });
})(jQuery);
