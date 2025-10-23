/**
 * Key CY Properties Filter - Infinite Scroll
 * Handles infinite scroll functionality
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Infinite Scroll Manager
   */
  window.KCPF_InfiniteScroll = {
    /**
     * Initialize infinite scroll
     */
    init: function () {
      this.handleScroll();
    },

    /**
     * Handle scroll events
     */
    handleScroll: function () {
      $(window).on("scroll", function () {
        // Don't trigger if already loading
        if (window.kcpfLoadingNextPage) {
          return;
        }

        // Find the loop that's currently visible (most likely in viewport)
        const $visibleLoops = $(".kcpf-properties-loop").filter(function () {
          const $loop = $(this);
          const offset = $loop.offset();
          if (!offset) return false;

          const windowTop = $(window).scrollTop();
          const windowBottom = windowTop + $(window).height();
          const loopTop = offset.top;
          const loopBottom = loopTop + $loop.outerHeight();

          // Check if loop is in viewport
          return loopTop < windowBottom && loopBottom > windowTop;
        });

        if ($visibleLoops.length === 0) {
          return;
        }

        // Use the first visible loop
        const $loop = $visibleLoops.first();
        const $grid = $loop.find(".kcpf-properties-grid");

        if ($grid.length === 0) {
          return;
        }

        const currentPage = parseInt($grid.data("current-page")) || 1;
        const maxPages = parseInt($grid.data("max-pages")) || 1;

        // Check if we've reached the last page
        if (currentPage >= maxPages) {
          return;
        }

        // Calculate scroll position
        const scrollTop = $(window).scrollTop();
        const windowHeight = $(window).height();
        const documentHeight = $(document).height();
        const distanceFromBottom = documentHeight - (scrollTop + windowHeight);

        // Trigger load when within 300px of bottom
        if (distanceFromBottom < 300) {
          KCPF_InfiniteScroll.loadNextPage($loop);
        }
      });
    },

    /**
     * Load next page of properties
     *
     * @param {jQuery} $loop - Properties loop element
     */
    loadNextPage: function ($loop) {
      // Prevent multiple simultaneous requests
      if (window.kcpfLoadingNextPage) {
        return;
      }
      window.kcpfLoadingNextPage = true;

      // Use provided loop or find the first one
      if (!$loop || $loop.length === 0) {
        $loop = $(".kcpf-properties-loop").first();
      }

      const $grid = $loop.find(".kcpf-properties-grid");
      if ($grid.length === 0) {
        window.kcpfLoadingNextPage = false;
        return;
      }

      const currentPage = parseInt($grid.data("current-page")) || 1;
      const maxPages = parseInt($grid.data("max-pages")) || 1;

      // Check if there are more pages
      if (currentPage >= maxPages) {
        window.kcpfLoadingNextPage = false;
        return;
      }

      const nextPage = currentPage + 1;
      const purpose = $loop.data("purpose") || "";

      // Show loader in the specific loop
      $loop.find(".kcpf-infinite-loader").show();

      // Check if kcpfData is available
      if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
        $loop.find(".kcpf-infinite-loader").hide();
        window.kcpfLoadingNextPage = false;
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
          KCPF_InfiniteScroll.handleSuccess(
            response,
            $loop,
            $grid,
            nextPage,
            maxPages
          );
        },
        error: function (xhr, status, error) {
          $loop.find(".kcpf-infinite-loader").hide();
        },
        complete: function () {
          window.kcpfLoadingNextPage = false;
        },
      });
    },

    /**
     * Handle successful page load
     */
    handleSuccess: function (response, $loop, $grid, nextPage, maxPages) {
      if (response.success && response.data.html) {
        const $newContent = $(response.data.html);
        const $newGrid = $newContent.find(".kcpf-properties-grid");

        if ($newGrid.length > 0) {
          // Append new property cards to existing grid
          $newGrid.find(".kcpf-property-card").each(function () {
            $grid.append($(this));
          });

          // Update current page number
          const newCurrentPage =
            parseInt($newGrid.data("current-page")) || nextPage;
          const newMaxPages = parseInt($newGrid.data("max-pages")) || maxPages;
          $grid.attr("data-current-page", newCurrentPage);
          $grid.attr("data-max-pages", newMaxPages);

          // Update loader or remove if no more pages
          if (newCurrentPage >= newMaxPages) {
            $loop.find(".kcpf-infinite-loader").remove();
          } else {
            $loop.find(".kcpf-infinite-loader").hide();
          }
        }
      } else {
        $loop.find(".kcpf-infinite-loader").hide();
      }
    },
  };
})(jQuery);
