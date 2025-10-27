/**
 * Key CY Properties Filter - Load More Button
 * Handles load more button functionality
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Load More Manager
   */
  window.KCPF_LoadMore = {
    /**
     * Initialize load more functionality
     */
    init: function () {
      this.bindEvents();
    },

    /**
     * Bind click events to load more buttons
     */
    bindEvents: function () {
      $(document).on("click", ".kcpf-load-more-btn", function (e) {
        e.preventDefault();
        const $button = $(this);
        KCPF_LoadMore.loadMoreProperties($button);
      });
    },

    /**
     * Load more properties
     *
     * @param {jQuery} $button - Load more button element
     */
    loadMoreProperties: function ($button) {
      // Prevent multiple simultaneous requests
      if ($button.hasClass("loading")) {
        return;
      }

      // Get button data
      const $container = $button.closest(".kcpf-properties-loop");
      const $grid = $container.find(".kcpf-properties-grid");
      const currentPage = parseInt($button.attr("data-current-page")) || 1;
      const maxPages = parseInt($button.attr("data-max-pages")) || 1;
      const purpose = $button.attr("data-purpose") || "";

      // Check if there are more pages
      if (currentPage >= maxPages) {
        return;
      }

      const nextPage = currentPage + 1;

      // Set loading state
      $button.addClass("loading");
      $button.find(".kcpf-load-more-text").text("Loading...");
      $button.find(".kcpf-load-more-spinner").show();

      // Check if kcpfData is available
      if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
        this.resetButton($button);
        return;
      }

      // Get current URL parameters
      const params = new URLSearchParams(window.location.search);
      params.set("paged", nextPage);

      // Add purpose if available
      if (purpose) {
        params.set("purpose", purpose);
      }

      // Build AJAX URL
      const ajaxUrl =
        kcpfData.ajaxUrl + "?action=kcpf_load_properties&" + params.toString();

      $.ajax({
        url: ajaxUrl,
        type: "GET",
        dataType: "json",
        timeout: 60000,
        success: function (response) {
          KCPF_LoadMore.handleSuccess(
            response,
            $container,
            $grid,
            $button,
            nextPage,
            maxPages
          );
        },
        error: function (xhr, status, error) {
          KCPF_LoadMore.resetButton($button);
        },
      });
    },

    /**
     * Handle successful page load
     */
    handleSuccess: function (
      response,
      $container,
      $grid,
      $button,
      nextPage,
      maxPages
    ) {
      if (response.success && response.data.html) {
        const $newContent = $(response.data.html);
        const $newLoop = $newContent.filter(".kcpf-properties-loop");

        if ($newLoop.length > 0) {
          const $newGrid = $newLoop.find(".kcpf-properties-grid");

          if ($newGrid.length > 0) {
            const $newCards = $newGrid.find(".kcpf-property-card");

            // Get existing property IDs for duplicate detection
            const existingIds = [];
            $grid.find(".kcpf-property-card").each(function () {
              const cardId = $(this).data("property-id") || $(this).attr("id");
              if (cardId) existingIds.push(cardId);
            });

            // Append new property cards to existing grid and skip duplicates
            $newCards.each(function () {
              const $card = $(this);
              const cardId = $card.data("property-id") || $card.attr("id");

              if (cardId && existingIds.includes(cardId)) {
                return; // Skip this duplicate card
              }

              $grid.append($card);
            });

            // Update button data attributes
            const newCurrentPage =
              parseInt($newGrid.attr("data-current-page")) || nextPage;
            const newMaxPages =
              parseInt($newGrid.attr("data-max-pages")) || maxPages;

            $button.attr("data-current-page", newCurrentPage);
            $button.attr("data-max-pages", newMaxPages);

            // Remove button if no more pages, otherwise reset
            if (newCurrentPage >= newMaxPages) {
              $button.closest(".kcpf-load-more-container").remove();
            } else {
              this.resetButton($button);
            }
          } else {
            this.resetButton($button);
          }
        } else {
          this.resetButton($button);
        }
      } else {
        this.resetButton($button);
      }
    },

    /**
     * Reset button to normal state
     */
    resetButton: function ($button) {
      $button.removeClass("loading");
      $button.find(".kcpf-load-more-text").text("Load More");
      $button.find(".kcpf-load-more-spinner").hide();
    },
  };
})(jQuery);
