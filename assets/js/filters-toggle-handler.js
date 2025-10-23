/**
 * Key CY Properties Filter - Toggle Handler
 * Handles toggle button and button group interactions
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Toggle Handler
   */
  window.KCPF_ToggleHandler = {
    /**
     * Initialize toggle button handling
     */
    init: function () {
      this.handleToggleButtons();
      this.initializeActiveStates();
      this.handlePurposeChange();
      console.log("[KCPF] Toggle Handler initialized");
    },

    /**
     * Handle toggle button styling
     */
    handleToggleButtons: function () {
      $(document).on(
        "change",
        ".kcpf-toggle-buttons input, .kcpf-button-group input",
        function () {
          const $label = $(this).closest("label");
          const $group = $label.closest(
            ".kcpf-toggle-buttons, .kcpf-button-group"
          );

          // Remove active class from all in group
          $group.find("label").removeClass("active");

          // Add active class to selected
          if ($(this).is(":checked")) {
            $label.addClass("active");
          }
        }
      );
    },

    /**
     * Initialize active states for checked inputs
     */
    initializeActiveStates: function () {
      $(
        ".kcpf-toggle-buttons input:checked, .kcpf-button-group input:checked"
      ).each(function () {
        $(this).closest("label").addClass("active");
      });
    },

    /**
     * Handle live purpose change in homepage composite
     */
    handlePurposeChange: function () {
      $(document).on(
        "change",
        '.kcpf-homepage-filters .kcpf-filter-purpose input[name="purpose"]',
        function () {
          const $root = $(this).closest(".kcpf-homepage-filters");
          const purpose = $(this).val();

          // Use Homepage Manager to refresh filters
          if (window.KCPF_HomepageManager) {
            KCPF_HomepageManager.refreshFilters($root, purpose);
          }
        }
      );
    },
  };
})(jQuery);
