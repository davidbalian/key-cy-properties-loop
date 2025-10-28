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

            // If property was removed from favourites, remove it from DOM
            if (!response.data.favourited) {
              FavouritesManager.removePropertyFromFavourites(propertyId);
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
     * Remove a specific property from the favourites loop DOM
     * This is more efficient than refetching the entire loop
     */
    removePropertyFromFavourites: function (propertyId) {
      // Check if we're on a favourites page
      const $loop = $(".kcpf-favourites-loop");
      if ($loop.length === 0) {
        return;
      }

      // Find the property card by data-property-id
      const $propertyCard = $loop.find(`[data-property-id="${propertyId}"]`);

      if ($propertyCard.length > 0) {
        // Remove the property card with a smooth animation
        $propertyCard.fadeOut(300, function () {
          $(this).remove();

          // Check if this was the last property
          const remainingCards = $loop.find(".kcpf-property-card").length;

          if (remainingCards === 0) {
            // Show empty state
            FavouritesManager.showEmptyState($loop);
          }
        });
      }
    },

    /**
     * Show empty state when no favourites remain
     */
    showEmptyState: function ($loop) {
      // Determine purpose from the loop class
      const purpose = $loop.hasClass("kcpf-favourites-loop-rent")
        ? "rent"
        : "sale";

      // Create empty state HTML
      const emptyStateHtml = `
         <div class="kcpf-favourites-empty">
           <h3>No favourites yet</h3>
           <p>Tap the star on any property to save it here.</p>
         </div>
       `;

      // Replace loop content with empty state
      $loop.html(emptyStateHtml);
    },
  };

  /**
   * Initialize when document is ready
   */
  $(document).ready(function () {
    FavouritesManager.init();
  });
})(jQuery);
