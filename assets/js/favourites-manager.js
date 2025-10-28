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
       console.log(
         "removePropertyFromFavourites called for property:",
         propertyId
       );

       // Find the specific loop that contains this property
       const $propertyCard = $(`.kcpf-favourites-loop [data-property-id="${propertyId}"]`);
       console.log("Found property card:", $propertyCard.length);

       if ($propertyCard.length === 0) {
         console.log("Property card not found, returning");
         return;
       }

       // Get the parent loop container
       const $loop = $propertyCard.closest('.kcpf-favourites-loop');
       console.log("Found parent loop:", $loop.length);
       console.log("Loop classes:", $loop.attr('class'));

       if ($loop.length === 0) {
         console.log("No parent loop found, returning");
         return;
       }

       // Remove the property card with a smooth animation
       $propertyCard.fadeOut(300, function () {
         $(this).remove();

         // Check if this was the last property in THIS specific loop
         const remainingCards = $loop.find(".kcpf-property-card").length;

         console.log("Property removed, remaining cards in this loop:", remainingCards);
         console.log("Loop HTML after removal:", $loop.html());

         if (remainingCards === 0) {
           // Show empty state
           console.log("Showing empty state for this loop");
           FavouritesManager.showEmptyState($loop);
         }
       });
     },

    /**
     * Show empty state when no favourites remain
     */
    showEmptyState: function ($loop) {
      console.log("showEmptyState called");
      console.log("Loop element:", $loop);
      console.log("Loop classes:", $loop.attr("class"));

      // Determine purpose from the loop class
      const purpose = $loop.hasClass("kcpf-favourites-loop-rent")
        ? "rent"
        : "sale";

      console.log("Purpose determined as:", purpose);

      // Create empty state HTML
      const emptyStateHtml = `
         <div class="kcpf-favourites-empty">
           <h3>No favourites yet</h3>
           <p>Tap the star on any property to save it here.</p>
         </div>
       `;

      console.log("Empty state HTML:", emptyStateHtml);

      // Replace loop content with empty state
      $loop.html(emptyStateHtml);

      console.log("Loop HTML after setting empty state:", $loop.html());
    },
  };

  /**
   * Initialize when document is ready
   */
  $(document).ready(function () {
    FavouritesManager.init();
  });
})(jQuery);
