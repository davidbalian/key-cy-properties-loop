/**
 * Favourites Manager JavaScript
 *
 * Handles favourites toggle functionality
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Favourites Manager
   */
  const FavouritesManager = {
    /**
     * Initialize
     */
    init: function () {
      this.bindEvents();
    },

    /**
     * Bind event handlers
     */
    bindEvents: function () {
      $(document).on("click", ".kcpf-favourite-btn", this.handleToggleClick);
    },

    /**
     * Handle toggle click
     */
    handleToggleClick: function (e) {
      e.preventDefault();
      e.stopPropagation();

      const $btn = $(this);

      // Don't proceed if disabled (guest user)
      if ($btn.hasClass("is-disabled") || $btn.prop("disabled")) {
        return;
      }

      const propertyId = $btn.data("property-id");
      const purpose = $btn.data("purpose");

      if (!propertyId || !purpose) {
        console.error("Missing property ID or purpose");
        return;
      }

      // Show loading state
      $btn.addClass("is-loading");

      // Make AJAX request
      $.ajax({
        url: kcpfFavouritesData.ajaxUrl,
        type: "POST",
        data: {
          action: "kcpf_toggle_favourite",
          property_id: propertyId,
          purpose: purpose,
          nonce: kcpfFavouritesData.nonce,
        },
        success: function (response) {
          if (response.success) {
            // Update button with new HTML
            $btn.replaceWith(response.data.html);

            // If property was removed from favourites, refresh the favourites loop
            if (!response.data.favourited) {
              FavouritesManager.refreshFavouritesLoop();
            }
          } else {
            console.error("Toggle failed:", response.data.message);
            // Remove loading state
            $btn.removeClass("is-loading");
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX error:", error);
          // Remove loading state
          $btn.removeClass("is-loading");
        },
      });
    },

    /**
     * Refresh the favourites loop by reloading the page
     * This ensures removed properties disappear from the favourites page
     */
    refreshFavouritesLoop: function () {
      // Check if we're on a favourites page by looking for the favourites loop container
      if ($(".kcpf-favourites-loop").length > 0) {
        // Reload the page to refresh the favourites loop
        window.location.reload();
      }
    },
  };

  /**
   * Initialize when document is ready
   */
  $(document).ready(function () {
    FavouritesManager.init();
  });
})(jQuery);
