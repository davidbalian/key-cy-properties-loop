/**
 * Key CY Properties Filter - AJAX Handler
 * Handles AJAX requests and responses
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * AJAX Handler
   */
  window.KCPF_AjaxHandler = {
    /**
     * Initialize AJAX handling
     */
    init: function () {},

    /**
     * Load properties via AJAX
     *
     * @param {URLSearchParams} params - URL parameters
     * @param {boolean} updateHistory - Whether to update browser history
     */
    loadProperties: function (params, updateHistory) {
      if (updateHistory === undefined) {
        updateHistory = true;
      }

      // Check if kcpfData is available
      if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
        return;
      }

      const ajaxUrl =
        kcpfData.ajaxUrl + "?action=kcpf_load_properties&" + params.toString();
      const newUrl =
        window.location.pathname +
        (params.toString() ? "?" + params.toString() : "");

      $.ajax({
        url: ajaxUrl,
        type: "GET",
        dataType: "json",
        timeout: 60000, // 60 second timeout
        beforeSend: function () {
          // Get purpose from params to target correct loop
          const purpose = params.get("purpose");
          if (purpose) {
            $('.kcpf-properties-loop[data-purpose="' + purpose + '"]').addClass(
              "kcpf-loading"
            );
          } else {
            $(".kcpf-properties-loop").addClass("kcpf-loading");
          }

          // Also show loading for map views
          const $mapView = $(".kcpf-map-view");
          if ($mapView.length > 0) {
            console.log("[KCPF Ajax] Showing map loading state");
            $(".kcpf-map-cards-container").hide();
            $(".kcpf-map-loading").show();
          }
        },
        success: function (response) {
          KCPF_AjaxHandler.handleSuccess(
            response,
            params,
            newUrl,
            updateHistory
          );
        },
        complete: function () {
          // Get purpose from params to target correct loop
          const purpose = params.get("purpose");
          if (purpose) {
            $(
              '.kcpf-properties-loop[data-purpose="' + purpose + '"]'
            ).removeClass("kcpf-loading");
          } else {
            $(".kcpf-properties-loop").removeClass("kcpf-loading");
          }

          // Also hide loading for map views (in case of error)
          console.log("[KCPF Ajax] Hiding map loading state (complete)");
          $(".kcpf-map-loading").hide();
          $(".kcpf-map-cards-container").show();
        },
        error: function (xhr, status, error) {
          KCPF_AjaxHandler.handleError(xhr, status, error);
        },
      });
    },

    /**
     * Handle successful AJAX response
     */
    handleSuccess: function (response, params, newUrl, updateHistory) {
      if (response.success && response.data.html) {
        // Get purpose from params to find matching loop
        const purpose = params.get("purpose");

        // Find the matching loop by purpose
        let $targetLoop = null;
        if (purpose) {
          $targetLoop = $(
            '.kcpf-properties-loop[data-purpose="' + purpose + '"]'
          );
        }

        // Fallback to first loop if no purpose match found
        if (!$targetLoop || $targetLoop.length === 0) {
          $targetLoop = $(".kcpf-properties-loop").first();
        }

        // Replace the matching properties loop content
        if ($targetLoop && $targetLoop.length > 0) {
          const $newContent = $(response.data.html);
          $targetLoop.replaceWith($newContent);
        }

        // Check for map views and update them too
        this.updateMapViews(params);

        // Reset infinite scroll state
        window.kcpfLoadingNextPage = false;

        // Update URL without reload
        if (updateHistory) {
          history.pushState({ kcpfFilters: true }, "", newUrl);
        }

        // Scroll to results if exists (only for regular loops)
        const $loop = $(".kcpf-properties-loop");
        if ($loop.length > 0) {
          $("html, body").animate(
            {
              scrollTop: $loop.offset().top - 100,
            },
            400
          );
        }
      } else {
        const $loop = $(".kcpf-properties-loop").first();
        if ($loop.length > 0) {
          $loop.html(
            '<div class="kcpf-error"><p>Invalid response from server</p></div>'
          );
        }
      }
    },

    /**
     * Update map views with filtered results
     *
     * @param {URLSearchParams} params - Filter parameters
     */
    updateMapViews: function (params) {
      console.log("[KCPF Ajax] updateMapViews called");

      // Check if map view exists on the page
      const $mapView = $(".kcpf-map-view");
      console.log("[KCPF Ajax] Map view elements found:", $mapView.length);

      if ($mapView.length === 0) {
        console.log("[KCPF Ajax] No map view found, skipping update");
        return; // No map view found
      }

      // Convert URLSearchParams to object for map AJAX
      // The map AJAX expects parameters in the same format as form.serializeArray()
      const paramsObj = { action: "kcpf_load_map_properties" };

      // Convert URLSearchParams to the format expected by map AJAX
      for (const [key, value] of params) {
        if (key === "bedrooms" || key === "bathrooms") {
          // These come as comma-separated values, need to be sent as arrays
          const values = value.split(",");
          values.forEach((val) => {
            if (!paramsObj[key]) {
              paramsObj[key] = [];
            }
            paramsObj[key].push(val);
          });
        } else if (key.endsWith("[]")) {
          // Array parameters
          const cleanKey = key.slice(0, -2);
          if (!paramsObj[cleanKey]) {
            paramsObj[cleanKey] = [];
          }
          paramsObj[cleanKey].push(value);
        } else {
          // Single value
          paramsObj[key] = value;
        }
      }

      console.log("[KCPF Ajax] Converted params for map AJAX:", paramsObj);

      // Show loading state for map
      $(".kcpf-map-cards-container").hide();
      $(".kcpf-map-loading").show();

      // Check if kcpfData is available
      if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
        console.error("[KCPF Ajax] kcpfData not found - cannot update map");
        return;
      }

      // Make AJAX request to update map
      $.ajax({
        url: kcpfData.ajaxUrl,
        type: "GET",
        data: paramsObj,
        success: function (response) {
          console.log("[KCPF Ajax] Map update response:", response);

          if (response.success) {
            console.log(
              "[KCPF Ajax] Updating map cards with:",
              response.data.cards_html ? "HTML present" : "No HTML"
            );
            console.log("[KCPF Ajax] Cards count:", response.data.count);

            // Update map cards
            const $mapCards = $("#kcpf-map-cards");
            console.log(
              "[KCPF Ajax] Map cards element found:",
              $mapCards.length > 0
            );
            console.log(
              "[KCPF Ajax] Current cards HTML length:",
              $mapCards.html().length
            );
            console.log(
              "[KCPF Ajax] New cards HTML length:",
              response.data.cards_html.length
            );

            $mapCards.html(response.data.cards_html);

            // Force DOM update
            $mapCards.hide().show(0);

            // Update results count
            const $resultsCount = $(".kcpf-map-results-count");
            console.log(
              "[KCPF Ajax] Results count element found:",
              $resultsCount.length > 0
            );

            $resultsCount.text(
              response.data.count +
                (response.data.count === 1
                  ? " property found"
                  : " properties found")
            );

            // Update map markers if KCPFMapView is available
            if (window.KCPFMapView && response.data.properties_data) {
              console.log(
                "[KCPF Ajax] Updating map markers, properties count:",
                response.data.properties_data.length
              );
              window.KCPFMapView.properties = response.data.properties_data;
              window.KCPFMapView.addMarkers();
              window.KCPFMapView.fitBoundsToMarkers();
            } else {
              console.log(
                "[KCPF Ajax] KCPFMapView not available or no properties data"
              );
            }
          } else {
            console.error("[KCPF Ajax] Map update error:", response.data);
          }

          // Hide loading state
          console.log("[KCPF Ajax] Hiding map loading state (success)");
          $(".kcpf-map-loading").hide();
          $(".kcpf-map-cards-container").show();
        },
        error: function (xhr, status, error) {
          console.error("[KCPF Ajax] Map update error:", error);
          $(".kcpf-map-loading").hide();
          $(".kcpf-map-cards-container").show();
        },
      });
    },

    /**
     * Handle AJAX error
     */
    handleError: function (xhr, status, error) {
      // Show error message to user
      if (status === "timeout") {
        $(".kcpf-properties-loop").html(
          '<div class="kcpf-error"><p>Request timed out. Please try again.</p></div>'
        );
      } else if (status === "error" && xhr.status === 0) {
        $(".kcpf-properties-loop").html(
          '<div class="kcpf-error"><p>Network error. Please check your connection and try again.</p></div>'
        );
      } else {
        $(".kcpf-properties-loop").html(
          '<div class="kcpf-error"><p>An error occurred while loading properties (HTTP ' +
            xhr.status +
            "). Please try again.</p></div>"
        );
      }
    },

    /**
     * Test AJAX endpoint
     */
    testEndpoint: function () {
      const testUrl = kcpfData.ajaxUrl + "?action=kcpf_test";

      $.ajax({
        url: testUrl,
        type: "GET",
        dataType: "json",
        timeout: 10000,
        success: function (response) {},
        error: function (xhr, status, error) {},
      });
    },
  };
})(jQuery);
