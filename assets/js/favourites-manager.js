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

            // Update single property page button text if it exists
            FavouritesManager.updateSinglePropertyButton(propertyId, response.data.favourited);
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
      // Find the specific loop that contains this property
      const $propertyCard = $(
        `.kcpf-favourites-loop [data-property-id="${propertyId}"]`
      );

      if ($propertyCard.length === 0) {
        return;
      }

      // Get the parent loop container
      const $loop = $propertyCard.closest(".kcpf-favourites-loop");

      if ($loop.length === 0) {
        return;
      }

      // Remove the property card with a smooth animation
      $propertyCard.fadeOut(300, function () {
        $(this).remove();

        // Check if this was the last property in THIS specific loop
        const remainingCards = $loop.find(".kcpf-property-card").length;

        if (remainingCards === 0) {
          // Show empty state
          FavouritesManager.showEmptyState($loop);
        }
      });
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

    /**
     * Update single property page button text and state
     */
    updateSinglePropertyButton: function (propertyId, isFavourited) {
      const $singleBtn = $(`.kcpf-single-property-favourite-btn[data-property-id="${propertyId}"]`);
      
      if ($singleBtn.length === 0) {
        return;
      }

      // Update button state
      if (isFavourited) {
        $singleBtn.addClass("is-active").attr("aria-pressed", "true");
        $singleBtn.find(".kcpf-single-property-favourite-text").text("Remove from Favourites");
      } else {
        $singleBtn.removeClass("is-active").attr("aria-pressed", "false");
        $singleBtn.find(".kcpf-single-property-favourite-text").text("Save to Favourites");
      }

      // Update icon
      const $icon = $singleBtn.find(".kcpf-favourite-icon");
      if (isFavourited) {
        $icon.html(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20">
          <path d="M394 480a16 16 0 01-9.39-3L256 383.76 127.39 477a16 16 0 01-24.55-18.08L153 310.35 23 221.2a16 16 0 019-29.2h160.38l48.4-148.95a16 16 0 0130.44 0l48.4 149H480a16 16 0 019.05 29.2L359 310.35l50.13 148.53A16 16 0 01394 480z" fill="currentColor"></path>
        </svg>`);
      } else {
        $icon.html(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20">
          <path d="M480 208H308L256 48l-52 160H32l140 96-54 160 138-100 138 100-54-160z" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"></path>
        </svg>`);
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
