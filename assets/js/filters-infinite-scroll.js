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
    // Track last scroll time to prevent rapid-fire events
    lastScrollTime: 0,

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
        const now = Date.now();

        // Throttle scroll events to prevent rapid-fire triggers
        if (now - window.KCPF_InfiniteScroll.lastScrollTime < 200) {
          return;
        }
        window.KCPF_InfiniteScroll.lastScrollTime = now;

        // Don't trigger if already loading
        if (window.kcpfLoadingNextPage) {
          console.log(
            "KCPF Infinite Scroll: Already loading, skipping scroll event"
          );
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

        console.log(
          "KCPF Infinite Scroll: Found",
          $visibleLoops.length,
          "visible loops"
        );
        console.log(
          "KCPF Infinite Scroll: Using loop with purpose:",
          $loop.data("purpose")
        );
        console.log("KCPF Infinite Scroll: Grid element:", $grid.get(0));
        console.log(
          "KCPF Infinite Scroll: All loops on page:",
          $(".kcpf-properties-loop")
            .map(function () {
              return $(this).data("purpose") || "no-purpose";
            })
            .get()
        );

        if ($grid.length === 0) {
          console.log("KCPF Infinite Scroll: No grid found in selected loop");
          return;
        }

        const currentPage = parseInt($grid.data("current-page")) || 1;
        const maxPages = parseInt($grid.data("max-pages")) || 1;

        console.log(
          "KCPF Infinite Scroll: Grid data attributes - current-page:",
          $grid.attr("data-current-page"),
          "max-pages:",
          $grid.attr("data-max-pages")
        );

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
        console.log("KCPF Infinite Scroll: Already loading, skipping");
        return;
      }
      window.kcpfLoadingNextPage = true;

      // Use provided loop or find the first one
      if (!$loop || $loop.length === 0) {
        $loop = $(".kcpf-properties-loop").first();
      }

      const $grid = $loop.find(".kcpf-properties-grid");
      if ($grid.length === 0) {
        console.log("KCPF Infinite Scroll: No grid found");
        window.kcpfLoadingNextPage = false;
        return;
      }

      const currentPage = parseInt($grid.attr("data-current-page")) || 1;
      const maxPages = parseInt($grid.attr("data-max-pages")) || 1;

      console.log(
        "KCPF Infinite Scroll: Current page:",
        currentPage,
        "Max pages:",
        maxPages
      );

      // Check if there are more pages
      if (currentPage >= maxPages) {
        console.log("KCPF Infinite Scroll: Reached max pages, stopping");
        window.kcpfLoadingNextPage = false;
        return;
      }

      const nextPage = currentPage + 1;
      const purpose = $loop.data("purpose") || "";

      console.log(
        "KCPF Infinite Scroll: Loading page",
        nextPage,
        "for purpose:",
        purpose
      );

      // Show loader in the specific loop
      $loop.find(".kcpf-infinite-loader").show();

      // Check if kcpfData is available
      if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
        $loop.find(".kcpf-infinite-loader").hide();
        window.kcpfLoadingNextPage = false;
        return;
      }

      // Get current URL parameters
      const currentUrlParams = new URLSearchParams(window.location.search);
      console.log(
        "KCPF Infinite Scroll: Original URL params:",
        Object.fromEntries(currentUrlParams)
      );

      const params = new URLSearchParams(window.location.search);
      params.set("paged", nextPage);

      // Add purpose if available
      if (purpose) {
        params.set("purpose", purpose);
      }

      // Build AJAX URL
      const ajaxUrl =
        kcpfData.ajaxUrl + "?action=kcpf_load_properties&" + params.toString();

      console.log("KCPF Infinite Scroll: AJAX URL:", ajaxUrl);
      console.log(
        "KCPF Infinite Scroll: Final request params:",
        Object.fromEntries(params)
      );

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
      console.log("KCPF Infinite Scroll: Handle success for page", nextPage);

      if (response.success && response.data.html) {
        const $newContent = $(response.data.html);
        const $newGrid = $newContent.find(".kcpf-properties-grid");

        if ($newGrid.length > 0) {
          const $newCards = $newGrid.find(".kcpf-property-card");
          console.log(
            "KCPF Infinite Scroll: Found",
            $newCards.length,
            "new property cards"
          );

          // Log existing property IDs for duplicate detection
          const existingIds = [];
          $grid.find(".kcpf-property-card").each(function () {
            const cardId = $(this).data("property-id") || $(this).attr("id");
            if (cardId) existingIds.push(cardId);
          });
          console.log(
            "KCPF Infinite Scroll: Existing property IDs:",
            existingIds
          );

          // Append new property cards to existing grid and track duplicates
          const newIds = [];
          let duplicatesSkipped = 0;
          $newCards.each(function () {
            const $card = $(this);
            const cardId = $card.data("property-id") || $card.attr("id");

            if (cardId) {
              if (existingIds.includes(cardId)) {
                console.warn(
                  "KCPF Infinite Scroll: SKIPPING DUPLICATE PROPERTY - ID:",
                  cardId
                );
                duplicatesSkipped++;
                return; // Skip this duplicate card
              }
              newIds.push(cardId);
            }

            $grid.append($card);
          });

          console.log(
            "KCPF Infinite Scroll: Skipped",
            duplicatesSkipped,
            "duplicate cards"
          );

          console.log("KCPF Infinite Scroll: New property IDs:", newIds);
          console.log(
            "KCPF Infinite Scroll: Total properties after append:",
            $grid.find(".kcpf-property-card").length
          );

          // Update current page number
          const newCurrentPage =
            parseInt($newGrid.attr("data-current-page")) || nextPage;
          const newMaxPages =
            parseInt($newGrid.attr("data-max-pages")) || maxPages;

          console.log(
            "KCPF Infinite Scroll: Updating grid element:",
            $grid.get(0)
          );
          console.log(
            "KCPF Infinite Scroll: Setting data-current-page to:",
            newCurrentPage,
            "data-max-pages to:",
            newMaxPages
          );

          $grid.attr("data-current-page", newCurrentPage);
          $grid.attr("data-max-pages", newMaxPages);

          // Verify the attributes were set
          console.log(
            "KCPF Infinite Scroll: After update - data-current-page:",
            $grid.attr("data-current-page"),
            "data-max-pages:",
            $grid.attr("data-max-pages")
          );

          // Force DOM update to ensure attributes are written immediately
          $grid
            .get(0)
            .setAttribute("data-current-page", newCurrentPage.toString());
          $grid.get(0).setAttribute("data-max-pages", newMaxPages.toString());

          console.log(
            "KCPF Infinite Scroll: Updated to page",
            newCurrentPage,
            "of",
            newMaxPages
          );

          // Update loader or remove if no more pages
          if (newCurrentPage >= newMaxPages) {
            console.log("KCPF Infinite Scroll: No more pages, removing loader");
            $loop.find(".kcpf-infinite-loader").remove();
          } else {
            console.log(
              "KCPF Infinite Scroll: More pages available, hiding loader"
            );
            $loop.find(".kcpf-infinite-loader").hide();
          }
        } else {
          console.log("KCPF Infinite Scroll: No grid found in response");
        }
      } else {
        console.log("KCPF Infinite Scroll: Response failed or no HTML");
        $loop.find(".kcpf-infinite-loader").hide();
      }
    },
  };
})(jQuery);
