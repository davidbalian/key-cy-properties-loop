/**
 * Key CY Properties Filter JavaScript
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Initialize filters
   */
  function initFilters() {
    // Wrap all filters in a form
    wrapFiltersInForm();

    // Handle form submission
    handleFormSubmission();

    // Handle toggle/button interactions
    handleToggleButtons();

    // Initialize range sliders
    initRangeSliders();
  }

  /**
   * Wrap all filter shortcodes in a form
   */
  function wrapFiltersInForm() {
    const filters = $(".kcpf-filter");

    console.log("[KCPF] Found " + filters.length + " filter elements");

    if (filters.length === 0) {
      console.warn("[KCPF] No filter elements found");
      return;
    }

    // Check if already wrapped
    if (filters.first().closest("form").length > 0) {
      console.log("[KCPF] Filters already wrapped in form");
      return;
    }

    // Wrap all filters together in a form
    console.log("[KCPF] Wrapping " + filters.length + " filters in form");

    if (filters.length > 0) {
      filters.wrapAll('<form class="kcpf-filters-form" method="get"></form>');
      console.log("[KCPF] Filters wrapped successfully");
    }
  }

  /**
   * Handle form submission
   */
  function handleFormSubmission() {
    // Always use AJAX for form submission
    $(document).on("submit", ".kcpf-filters-form", function (e) {
      e.preventDefault();
      console.log("[KCPF] Form submit event triggered");
      handleAjaxSubmission($(this));
    });

    // Handle apply button clicks
    $(document).on("click", ".kcpf-apply-button", function (e) {
      e.preventDefault();
      console.log("[KCPF] Apply button clicked");

      const form = $(this).closest("form");
      console.log("[KCPF] Form found:", form.length);

      if (form.length > 0) {
        form.submit();
      } else {
        // Fallback: find form on page
        const $form = $(".kcpf-filters-form").first();
        console.log("[KCPF] Fallback form found:", $form.length);
        if ($form.length > 0) {
          $form.submit();
        } else {
          console.error("[KCPF] No form found - cannot submit filters");
        }
      }
    });

    // Handle reset button clicks
    $(document).on("click", ".kcpf-reset-button", function (e) {
      e.preventDefault();
      console.log("[KCPF] Reset button clicked");

      // Clear all form fields
      const $form = $(".kcpf-filters-form").first();
      if ($form.length > 0) {
        // Reset all inputs
        $form.find("input[type='text']").val("");
        $form.find("select").val("");
        $form.find("input[type='checkbox']").prop("checked", false);
        $form.find("input[type='radio']").prop("checked", false);

        // Clear multiselect chips
        $(".kcpf-chip").remove();

        // Clear range sliders and inputs
        $form.find(".kcpf-range-dropdown").removeClass("active");
        $form.find(".kcpf-range-dropdown-menu").css({
          display: "none",
          visibility: "hidden",
          opacity: "0",
        });

        // Submit form to reload with empty filters
        $form.submit();
      } else {
        console.error("[KCPF] No form found - cannot reset filters");
      }
    });

    // Initialize infinite scroll
    initInfiniteScroll();

    // Handle browser back/forward buttons
    window.addEventListener("popstate", function (e) {
      if (e.state && e.state.kcpfFilters) {
        const params = new URLSearchParams(window.location.search);
        // Detect purpose from URL params or current page
        if (!params.get("purpose")) {
          const $loop = $(".kcpf-properties-loop").first();
          if ($loop.length > 0) {
            const purpose = $loop.data("purpose");
            if (purpose) {
              params.set("purpose", purpose);
            }
          }
        }
        loadPropertiesAjax(params, false);
      }
    });
  }

  /**
   * Handle AJAX form submission
   */
  function handleAjaxSubmission(form) {
    console.log("[KCPF] Form submitted, processing...");

    // Clean up empty values
    const formData = {};
    form.serializeArray().forEach(function (item) {
      if (item.value !== "" && item.value !== null) {
        // Check if this field name already exists (means multiple values)
        if (formData.hasOwnProperty(item.name)) {
          // Convert to array if not already
          if (!Array.isArray(formData[item.name])) {
            formData[item.name] = [formData[item.name]];
          }
          formData[item.name].push(item.value);
        } else {
          // First occurrence - store as single value
          formData[item.name] = item.value;
        }
      }
    });

    console.log("[KCPF] Form data:", formData);

    const params = new URLSearchParams();
    Object.keys(formData).forEach(function (key) {
      const value = formData[key];

      if (Array.isArray(value)) {
        // Remove [] from key name if present
        const cleanKey = key.replace(/\[\]$/, "");

        if (value.length === 1) {
          // Single value - use simple parameter
          params.set(cleanKey, value[0]);
        } else {
          // Multiple values - use array syntax
          value.forEach(function (v) {
            params.append(cleanKey + "[]", v);
          });
        }
      } else {
        // Single value
        params.set(key, value);
      }
    });

    // Get purpose from form or find closest loop
    let purpose = params.get("purpose");
    if (!purpose) {
      // Try to find the purpose from the closest loop
      const $closestLoop = form.closest(".kcpf-properties-loop");
      if ($closestLoop.length > 0) {
        purpose = $closestLoop.data("purpose");
      } else {
        // Fallback: find first loop on page
        const $firstLoop = $(".kcpf-properties-loop").first();
        if ($firstLoop.length > 0) {
          purpose = $firstLoop.data("purpose");
        }
      }
    }

    // Add purpose to params if found
    if (purpose) {
      params.set("purpose", purpose);
      console.log("[KCPF] Detected purpose:", purpose);
    }

    console.log("[KCPF] URL params:", params.toString());

    loadPropertiesAjax(params);
  }

  /**
   * Load properties via AJAX
   */
  function loadPropertiesAjax(params, updateHistory) {
    if (updateHistory === undefined) {
      updateHistory = true;
    }

    console.log("[KCPF] Loading filtered results...");

    // Check if kcpfData is available
    if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
      console.error("[KCPF] kcpfData not found - AJAX URL not available");
      return;
    }

    const ajaxUrl =
      kcpfData.ajaxUrl + "?action=kcpf_load_properties&" + params.toString();
    const newUrl =
      window.location.pathname +
      (params.toString() ? "?" + params.toString() : "");

    console.log("[KCPF] === Starting AJAX Request ===");
    console.log("[KCPF] AJAX URL:", ajaxUrl);
    console.log("[KCPF] URL Parameters:", params.toString());
    console.log("[KCPF] Parameter count:", params.toString().split("&").length);
    console.log("[KCPF] New URL:", newUrl);
    console.log("[KCPF] Request timestamp:", new Date().toISOString());

    $.ajax({
      url: ajaxUrl,
      type: "GET",
      dataType: "json",
      timeout: 60000, // 60 second timeout
      beforeSend: function () {
        console.log("[KCPF] Sending AJAX request...");
        // Get purpose from params to target correct loop
        const purpose = params.get("purpose");
        if (purpose) {
          $('.kcpf-properties-loop[data-purpose="' + purpose + '"]').addClass(
            "kcpf-loading"
          );
        } else {
          $(".kcpf-properties-loop").addClass("kcpf-loading");
        }
      },
      success: function (response) {
        console.log("[KCPF] AJAX response received:", response);
        console.log("[KCPF] Response timestamp:", new Date().toISOString());

        if (response.success && response.data.html) {
          // Get purpose from params to find matching loop
          const purpose = params.get("purpose");

          // Find the matching loop by purpose
          let $targetLoop = null;
          if (purpose) {
            $targetLoop = $(
              '.kcpf-properties-loop[data-purpose="' + purpose + '"]'
            );
            console.log("[KCPF] Found matching loop for purpose:", purpose);
          }

          // Fallback to first loop if no purpose match found
          if (!$targetLoop || $targetLoop.length === 0) {
            $targetLoop = $(".kcpf-properties-loop").first();
            console.log("[KCPF] Using first loop as fallback");
          }

          // Replace the matching properties loop content
          const $newContent = $(response.data.html);
          $targetLoop.replaceWith($newContent);

          // Reset infinite scroll state
          window.kcpfLoadingNextPage = false;

          // Update URL without reload
          if (updateHistory) {
            console.log("[KCPF] Updating URL to:", newUrl);
            history.pushState({ kcpfFilters: true }, "", newUrl);
          }

          // Scroll to results if exists
          const $loop = $(".kcpf-properties-loop");
          if ($loop.length > 0) {
            $("html, body").animate(
              {
                scrollTop: $loop.offset().top - 100,
              },
              400
            );
          }

          console.log("[KCPF] Results updated successfully");
        } else {
          console.error("[KCPF] Invalid response format:", response);
          const $loop = $(".kcpf-properties-loop").first();
          if ($loop.length > 0) {
            $loop.html(
              '<div class="kcpf-error"><p>Invalid response from server</p></div>'
            );
          }
        }
      },
      complete: function () {
        console.log("[KCPF] AJAX request complete");
        // Get purpose from params to target correct loop
        const purpose = params.get("purpose");
        if (purpose) {
          $(
            '.kcpf-properties-loop[data-purpose="' + purpose + '"]'
          ).removeClass("kcpf-loading");
        } else {
          $(".kcpf-properties-loop").removeClass("kcpf-loading");
        }
      },
      error: function (xhr, status, error) {
        console.error("[KCPF] ============ AJAX ERROR ============");
        console.error("[KCPF] Status:", status);
        console.error("[KCPF] Error:", error);
        console.error("[KCPF] XHR Status:", xhr.status);
        console.error("[KCPF] Response Headers:", xhr.getAllResponseHeaders());
        console.error("[KCPF] Response Text:", xhr.responseText);
        console.error("[KCPF] Ready State:", xhr.readyState);
        console.error("[KCPF] Error timestamp:", new Date().toISOString());
        console.error("[KCPF] Full XHR object:", xhr);

        // Show error message to user
        if (status === "timeout") {
          console.error("[KCPF] Request timed out after 60 seconds");
          $(".kcpf-properties-loop").html(
            '<div class="kcpf-error"><p>Request timed out. Please try again.</p></div>'
          );
        } else if (status === "error" && xhr.status === 0) {
          console.error("[KCPF] Network error - request may have been blocked");
          $(".kcpf-properties-loop").html(
            '<div class="kcpf-error"><p>Network error. Please check your connection and try again.</p></div>'
          );
        } else {
          console.error("[KCPF] Server error - HTTP Status:", xhr.status);
          $(".kcpf-properties-loop").html(
            '<div class="kcpf-error"><p>An error occurred while loading properties (HTTP ' +
              xhr.status +
              "). Please try again.</p></div>"
          );
        }
      },
    });
  }

  /**
   * Handle toggle/button group interactions
   */
  function handleToggleButtons() {
    // Toggle button styling
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

    // Initialize active states
    $(
      ".kcpf-toggle-buttons input:checked, .kcpf-button-group input:checked"
    ).each(function () {
      $(this).closest("label").addClass("active");
    });
  }

  /**
   * Initialize range sliders
   */
  function initRangeSliders() {
    if (typeof noUiSlider === "undefined") {
      console.warn("[KCPF] noUiSlider library not loaded");
      return;
    }

    $(".kcpf-range-slider").each(function () {
      const $slider = $(this);
      const $container = $slider.closest(".kcpf-range-slider-container");
      const $minInput = $container.find(".kcpf-range-min");
      const $maxInput = $container.find(".kcpf-range-max");

      const min = parseFloat($slider.data("min"));
      const max = parseFloat($slider.data("max"));
      const step = parseFloat($slider.data("step")) || 1;
      const valueMin = parseFloat($slider.data("value-min")) || min;
      const valueMax = parseFloat($slider.data("value-max")) || max;
      const format = $slider.data("format");

      // Create slider
      const slider = this;
      noUiSlider.create(slider, {
        start: [valueMin, valueMax],
        connect: true,
        range: {
          min: min,
          max: max,
        },
        step: step,
        format: {
          to: function (value) {
            return Math.round(value);
          },
          from: function (value) {
            return Number(value);
          },
        },
      });

      // Update inputs when slider changes
      slider.noUiSlider.on("update", function (values, handle) {
        if (handle === 0) {
          $minInput.val(values[0]);
        } else {
          $maxInput.val(values[1]);
        }
      });

      // Update slider when inputs change
      $minInput.on("change", function () {
        const value = parseFloat($(this).val()) || min;
        slider.noUiSlider.set([value, null]);
      });

      $maxInput.on("change", function () {
        const value = parseFloat($(this).val()) || max;
        slider.noUiSlider.set([null, value]);
      });
    });
  }

  /**
   * Initialize multiselect dropdowns
   */
  function initMultiselectDropdowns() {
    console.log("[KCPF] Initializing multiselect dropdowns");

    // Handle dropdown toggle
    $(document).on("click", ".kcpf-multiselect-trigger", function (e) {
      e.stopPropagation();
      console.log("[KCPF] Dropdown trigger clicked");
      const $dropdown = $(this).closest(".kcpf-multiselect-dropdown");
      const isActive = $dropdown.hasClass("active");

      // Close all dropdowns
      $(".kcpf-multiselect-dropdown").removeClass("active");
      $(".kcpf-range-dropdown").removeClass("active");

      // Toggle current dropdown
      if (!isActive) {
        $dropdown.addClass("active");
        console.log("[KCPF] Dropdown opened");
      } else {
        console.log("[KCPF] Dropdown closed");
      }
    });

    // Handle range dropdown toggle
    $(document).on("click", ".kcpf-range-trigger", function (e) {
      e.stopPropagation();
      console.log("[KCPF] Range dropdown trigger clicked");
      const $dropdown = $(this).closest(".kcpf-range-dropdown");
      const isActive = $dropdown.hasClass("active");

      // Close all dropdowns
      $(".kcpf-multiselect-dropdown").removeClass("active");
      $(".kcpf-range-dropdown").removeClass("active");

      // Toggle current dropdown
      if (!isActive) {
        $dropdown.addClass("active");
        console.log("[KCPF] Range dropdown opened");
      } else {
        console.log("[KCPF] Range dropdown closed");
      }
    });

    // Close dropdown when clicking outside
    $(document).on("click", function (e) {
      if (!$(e.target).closest(".kcpf-multiselect-dropdown").length) {
        $(".kcpf-multiselect-dropdown").removeClass("active");
      }
      if (!$(e.target).closest(".kcpf-range-dropdown").length) {
        $(".kcpf-range-dropdown").removeClass("active");
      }
    });

    // Handle chip removal
    $(document).on("click", ".kcpf-chip-remove", function (e) {
      e.stopPropagation();
      e.preventDefault();
      console.log("[KCPF] Chip remove clicked");
      const $chip = $(this).closest(".kcpf-chip");
      const value = $(this).data("value");
      const $dropdown = $(this).closest(".kcpf-multiselect-dropdown");
      const filterName = $dropdown.data("filter-name");

      // Uncheck the checkbox
      $dropdown
        .find('input[type="checkbox"][value="' + value + '"]')
        .prop("checked", false);

      // Remove the chip
      $chip.remove();

      // Show placeholder if no chips remain
      const $selected = $dropdown.find(".kcpf-multiselect-selected");
      if ($selected.find(".kcpf-chip").length === 0) {
        const placeholder =
          "Select " + filterName.charAt(0).toUpperCase() + filterName.slice(1);
        $selected.html(
          '<span class="kcpf-placeholder">' + placeholder + "</span>"
        );
      }
    });

    // Handle checkbox changes
    $(document).on(
      "change",
      ".kcpf-multiselect-option input[type='checkbox']",
      function () {
        console.log("[KCPF] Checkbox changed");
        const $dropdown = $(this).closest(".kcpf-multiselect-dropdown");
        const filterName = $dropdown.data("filter-name");
        const $selected = $dropdown.find(".kcpf-multiselect-selected");
        const checkedValues = [];

        // Get all checked values
        $dropdown.find('input[type="checkbox"]:checked').each(function () {
          checkedValues.push($(this).val());
        });

        // Update selected chips
        if (checkedValues.length === 0) {
          const placeholder =
            "Select " +
            filterName.charAt(0).toUpperCase() +
            filterName.slice(1);
          $selected.html(
            '<span class="kcpf-placeholder">' + placeholder + "</span>"
          );
        } else {
          let chipsHtml = "";
          $dropdown.find(".kcpf-multiselect-option").each(function () {
            const $checkbox = $(this).find('input[type="checkbox"]');
            const value = $checkbox.val();
            // Get label without the count span
            const label = $(this).find("span").not(".kcpf-count").text();

            if ($checkbox.is(":checked")) {
              chipsHtml +=
                '<span class="kcpf-chip">' +
                label +
                '<span class="kcpf-chip-remove" data-value="' +
                value +
                '">&times;</span></span>';
            }
          });
          $selected.html(chipsHtml);
        }
      }
    );
  }

  /**
   * Initialize infinite scroll
   */
  function initInfiniteScroll() {
    // Check if user is near bottom of page
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
        loadNextPage($loop);
      }
    });
  }

  /**
   * Load next page of properties
   */
  function loadNextPage($loop) {
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

    console.log(
      "[KCPF] Loading page " +
        nextPage +
        " of " +
        maxPages +
        " for purpose: " +
        purpose
    );

    // Show loader in the specific loop
    $loop.find(".kcpf-infinite-loader").show();

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

    console.log("[KCPF] Infinite scroll AJAX URL:", ajaxUrl);

    $.ajax({
      url: ajaxUrl,
      type: "GET",
      dataType: "json",
      timeout: 60000,
      success: function (response) {
        console.log("[KCPF] Page " + nextPage + " loaded successfully");

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
            const newMaxPages =
              parseInt($newGrid.data("max-pages")) || maxPages;
            $grid.attr("data-current-page", newCurrentPage);
            $grid.attr("data-max-pages", newMaxPages);

            // Update loader or remove if no more pages
            if (newCurrentPage >= newMaxPages) {
              $loop.find(".kcpf-infinite-loader").remove();
            } else {
              $loop.find(".kcpf-infinite-loader").hide();
            }

            console.log(
              "[KCPF] Updated to page " + newCurrentPage + " of " + newMaxPages
            );
          }
        } else {
          console.error("[KCPF] Invalid response format:", response);
          $loop.find(".kcpf-infinite-loader").hide();
        }
      },
      error: function (xhr, status, error) {
        console.error("[KCPF] Infinite scroll error:", status, error);
        $loop.find(".kcpf-infinite-loader").hide();
      },
      complete: function () {
        window.kcpfLoadingNextPage = false;
      },
    });
  }

  /**
   * Test AJAX endpoint
   */
  function testAjax() {
    console.log("[KCPF] Testing AJAX endpoint...");
    const testUrl = kcpfData.ajaxUrl + "?action=kcpf_test";

    $.ajax({
      url: testUrl,
      type: "GET",
      dataType: "json",
      timeout: 10000,
      success: function (response) {
        console.log("[KCPF] AJAX test SUCCESS:", response);
      },
      error: function (xhr, status, error) {
        console.error("[KCPF] AJAX test FAILED:", status, error);
        console.error("[KCPF] XHR Status:", xhr.status);
        console.error("[KCPF] Response:", xhr.responseText);
      },
    });
  }

  /**
   * Initialize on document ready
   */
  $(document).ready(function () {
    initFilters();
    initMultiselectDropdowns();
    console.log("[KCPF] Filters initialized");

    // Test AJAX after a short delay
    setTimeout(function () {
      testAjax();
    }, 1000);
  });
})(jQuery);
