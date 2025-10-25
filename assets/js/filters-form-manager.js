/**
 * Key CY Properties Filter - Form Manager
 * Handles form wrapping and submission
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Form Manager
   */
  window.KCPF_FormManager = {
    /**
     * Initialize form management
     */
    init: function () {
      this.captureInitialState();
      this.wrapFiltersInForm();
      this.handleFormSubmission();
      this.handleResetButton();
      this.handleApplyButton();
      this.handleBrowserNavigation();
      this.initAccordion();
    },

    /**
     * Capture the initial state of all filters when the page loads
     */
    captureInitialState: function () {
      const $form = $(".kcpf-filters-form").first();
      if ($form.length === 0) {
        return;
      }

      // Store initial state as data attribute on the form
      const initialState = {
        checkboxes: {},
        radios: {},
        selects: {},
        inputs: {},
        multiselects: {},
        ranges: {},
      };

      // Capture checkbox states
      $form.find("input[type='checkbox']").each(function () {
        const name = $(this).attr("name");
        if (name) {
          if (!initialState.checkboxes[name]) {
            initialState.checkboxes[name] = [];
          }
          if ($(this).is(":checked")) {
            initialState.checkboxes[name].push($(this).val());
          }
        }
      });

      // Capture radio states
      $form.find("input[type='radio']:checked").each(function () {
        const name = $(this).attr("name");
        if (name) {
          initialState.radios[name] = $(this).val();
        }
      });

      // Capture select values
      $form.find("select").each(function () {
        const name = $(this).attr("name");
        if (name) {
          initialState.selects[name] = $(this).val();
        }
      });

      // Capture text/number input values
      $form.find("input[type='text'], input[type='number']").each(function () {
        const name = $(this).attr("name");
        if (name) {
          initialState.inputs[name] = $(this).val();
        }
      });

      // Capture multiselect states
      $form.find(".kcpf-multiselect").each(function () {
        const name = $(this).data("name");
        if (name) {
          const selectedValues = [];
          $(this)
            .find("input[type='checkbox']:checked")
            .each(function () {
              selectedValues.push($(this).val());
            });
          initialState.multiselects[name] = selectedValues;
        }
      });

      // Capture range slider positions
      $form.find(".kcpf-range-slider-container").each(function () {
        const $container = $(this);
        const $minInput = $container.find(".kcpf-range-min");
        const $maxInput = $container.find(".kcpf-range-max");
        const name = $minInput.attr("name");

        if (name && $minInput.length && $maxInput.length) {
          const minName = name.replace("_max", "_min");
          initialState.ranges[minName] = {
            min: $minInput.val(),
            max: $maxInput.val(),
          };
        }
      });

      // Store the initial state
      $form.data("initialState", initialState);
    },

    /**
     * Update multiselect display for a given multiselect element
     */
    updateMultiselectDisplay: function ($dropdown) {
      const filterName = $dropdown.data("filter-name");
      const $selected = $dropdown.find(".kcpf-multiselect-selected");
      const checkedValues = [];

      // Get all checked values
      $dropdown.find('input[type="checkbox"]:checked').each(function () {
        checkedValues.push($(this).val());
      });

      // Update selected chips
      if (checkedValues.length === 0) {
        const originalPlaceholder = $selected.data("original-placeholder");
        $selected.html(
          '<span class="kcpf-placeholder">' + originalPlaceholder + "</span>"
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
    },

    /**
     * Wrap all filter shortcodes in a form
     */
    wrapFiltersInForm: function () {
      const filters = $(".kcpf-filter");

      if (filters.length === 0) {
        return;
      }

      // Check if already wrapped
      if (filters.first().closest("form").length > 0) {
        return;
      }

      // Wrap all filters together in a form
      if (filters.length > 0) {
        filters.wrapAll('<form class="kcpf-filters-form" method="get"></form>');
      }
    },

    /**
     * Handle form submission
     */
    handleFormSubmission: function () {
      // Check if debug mode is enabled before binding AJAX
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get("debug_filters") === "1") {
        return; // Don't bind AJAX handlers in debug mode
      }

      // Always use AJAX for form submission (except in debug mode)
      $(document).on("submit", ".kcpf-filters-form", function (e) {
        e.preventDefault();
        KCPF_FormManager.processFormSubmission($(this));
      });
    },

    /**
     * Handle apply button clicks
     */
    handleApplyButton: function () {
      // Check if debug mode is enabled before binding
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get("debug_filters") === "1") {
        return; // Don't bind AJAX handlers in debug mode
      }

      $(document).on("click", ".kcpf-apply-button", function (e) {
        e.preventDefault();

        const form = $(this).closest("form");

        // Homepage composite redirect behavior
        const $homepage = $(this).closest(".kcpf-homepage-filters");
        const submitType = $(this).data("type");
        if ($homepage.length > 0 && submitType === "redirect") {
          KCPF_FormManager.handleHomepageRedirect(form, $homepage, $(this));
          return;
        }

        if (form.length > 0) {
          form.submit();
        } else {
          // Fallback: find form on page
          const $form = $(".kcpf-filters-form").first();
          if ($form.length > 0) {
            $form.submit();
          } else {
          }
        }
      });
    },

    /**
     * Handle homepage redirect (composite filters)
     */
    handleHomepageRedirect: function (form, $homepage, $button) {
      const params = new URLSearchParams();
      const data = {};
      (form.length ? form : $(".kcpf-filters-form").first())
        .serializeArray()
        .forEach(function (item) {
          if (item.value !== "" && item.value !== null) {
            if (data.hasOwnProperty(item.name)) {
              if (!Array.isArray(data[item.name])) {
                data[item.name] = [data[item.name]];
              }
              data[item.name].push(item.value);
            } else {
              data[item.name] = item.value;
            }
          }
        });

      Object.keys(data).forEach(function (key) {
        const value = data[key];
        if (Array.isArray(value)) {
          const cleanKey = key.replace(/\[\]$/, "");
          if (value.length === 1) {
            params.set(cleanKey, value[0]);
          } else {
            value.forEach(function (v) {
              params.append(cleanKey + "[]", v);
            });
          }
        } else {
          params.set(key, value);
        }
      });

      // Ensure purpose present
      let purpose = params.get("purpose");
      if (!purpose) {
        const $purposeInput = $homepage.find('input[name="purpose"]:checked');
        if ($purposeInput.length) {
          purpose = $purposeInput.val();
          params.set("purpose", purpose);
        }
      }

      const saleUrl =
        $button.data("sale-url") ||
        $homepage.data("sale-url") ||
        "/test-sale-archive";
      const rentUrl =
        $button.data("rent-url") ||
        $homepage.data("rent-url") ||
        "/test-rent-page";
      const target = purpose === "rent" ? rentUrl : saleUrl;
      const url = target + (params.toString() ? "?" + params.toString() : "");
      window.location.href = url;
    },

    /**
     * Handle reset button clicks
     */
    handleResetButton: function () {
      $(document).on("click", ".kcpf-reset-button", function (e) {
        e.preventDefault();

        // Get the form
        const $form = $(this).closest("form");
        if ($form.length === 0) {
          // Fallback: find any form on page
          $form = $(".kcpf-filters-form").first();
        }

        if ($form.length > 0) {
          // Get the initial state
          const initialState = $form.data("initialState");
          if (initialState) {
            // Restore checkboxes
            $form.find("input[type='checkbox']").prop("checked", false);
            Object.keys(initialState.checkboxes).forEach(function (name) {
              initialState.checkboxes[name].forEach(function (value) {
                $form
                  .find(
                    `input[type='checkbox'][name='${name}'][value='${value}']`
                  )
                  .prop("checked", true);
              });
            });

            // Restore radios
            Object.keys(initialState.radios).forEach(function (name) {
              $form
                .find(`input[type='radio'][name='${name}']`)
                .prop("checked", false);
              $form
                .find(
                  `input[type='radio'][name='${name}'][value='${initialState.radios[name]}']`
                )
                .prop("checked", true);
            });

            // Restore selects
            Object.keys(initialState.selects).forEach(function (name) {
              $form
                .find(`select[name='${name}']`)
                .val(initialState.selects[name]);
            });

            // Restore text/number inputs
            Object.keys(initialState.inputs).forEach(function (name) {
              $form
                .find(`input[name='${name}']`)
                .val(initialState.inputs[name]);
            });

            // Restore multiselects
            Object.keys(initialState.multiselects).forEach(function (name) {
              // First clear all
              $form
                .find(
                  `.kcpf-multiselect[data-name='${name}'] input[type='checkbox']`
                )
                .prop("checked", false);
              // Then check the initial ones
              initialState.multiselects[name].forEach(function (value) {
                $form
                  .find(
                    `.kcpf-multiselect[data-name='${name}'] input[type='checkbox'][value='${value}']`
                  )
                  .prop("checked", true);
              });

              // Update the display
              KCPF_FormManager.updateMultiselectDisplay(
                $form.find(`.kcpf-multiselect[data-name='${name}']`)
              );
            });

            // Restore range sliders
            Object.keys(initialState.ranges).forEach(function (name) {
              const rangeData = initialState.ranges[name];
              const $minInput = $form.find(`input[name='${name}']`);
              const $maxInput = $form.find(
                `input[name='${name.replace("_min", "_max")}']`
              );

              if ($minInput.length && $maxInput.length) {
                $minInput.val(rangeData.min);
                $maxInput.val(rangeData.max);

                // Update the slider if it exists
                const $container = $minInput.closest(
                  ".kcpf-range-slider-container"
                );
                if ($container.length) {
                  const $slider = $container.find(".kcpf-range-slider");
                  if ($slider.length && $slider[0].noUiSlider) {
                    $slider[0].noUiSlider.set([rangeData.min, rangeData.max]);
                  }
                }
              }
            });
          } else {
            // Fallback to clearing if no initial state (shouldn't happen)
            $form
              .find("input[type='checkbox'], input[type='radio']")
              .prop("checked", false);
            $form
              .find("input[type='text'], input[type='number'], select")
              .val("");
            $form.find("input[type='hidden']").val("");
          }
        }

        // Clear URL and reload properties
        const params = new URLSearchParams();

        // For mega filters, detect purpose from the current loop on the page
        // since it's auto-detected from page content
        const $megaFilters = $(this).closest(".kcpf-mega-filters");
        if ($megaFilters.length > 0) {
          // For mega filters, detect purpose from current loop
          const $currentLoop = $(".kcpf-properties-loop").first();
          if ($currentLoop.length > 0) {
            const loopPurpose = $currentLoop.data("purpose");
            if (loopPurpose) {
              params.set("purpose", loopPurpose);
            }
          }
        } else {
          // Not mega filters, keep purpose if it exists in URL
          const currentPurpose = new URLSearchParams(
            window.location.search
          ).get("purpose");
          if (currentPurpose) {
            params.set("purpose", currentPurpose);
          }
        }

        // Update URL and load properties
        const newUrl =
          window.location.pathname +
          (params.toString() ? "?" + params.toString() : "");
        window.history.pushState({ kcpfFilters: true }, "", newUrl);

        // Use AJAX handler to load properties
        if (window.KCPF_AjaxHandler) {
          KCPF_AjaxHandler.loadProperties(params, false);
        }
      });
    },

    /**
     * Handle browser back/forward buttons
     */
    handleBrowserNavigation: function () {
      // Initial URL sync
      KCPF_FormManager.syncFormWithURL();

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
          // Sync form with URL parameters
          KCPF_FormManager.syncFormWithURL();
          // Use AJAX handler to load properties
          if (window.KCPF_AjaxHandler) {
            KCPF_AjaxHandler.loadProperties(params, false);
          }
        }
      });
    },

    /**
     * Sync form values with URL parameters
     */
    syncFormWithURL: function () {
      const params = new URLSearchParams(window.location.search);
      const $form = $(".kcpf-filters-form").first();

      if ($form.length === 0) {
        return;
      }

      // Clear existing selections first
      $form
        .find("input[type='checkbox'], input[type='radio']")
        .prop("checked", false);
      $form.find("select").val("");

      // Handle each parameter
      for (const [key, value] of params.entries()) {
        // Handle comma-separated values (bedrooms, bathrooms)
        if (
          (key === "bedrooms" || key === "bathrooms") &&
          value.includes(",")
        ) {
          const values = value.split(",");
          values.forEach((val) => {
            $form
              .find(`input[name="${key}[]"][value="${val}"]`)
              .prop("checked", true);
            $form.find(`select[name="${key}[]"]`).val(val);
          });
        }
        // Handle array parameters (key[])
        else if (key.endsWith("[]")) {
          const baseKey = key.slice(0, -2);
          $form
            .find(`input[name="${key}"][value="${value}"]`)
            .prop("checked", true);
          $form.find(`select[name="${key}"]`).val(value);
        }
        // Handle single values
        else {
          $form
            .find(`input[name="${key}"][value="${value}"]`)
            .prop("checked", true);
          $form.find(`select[name="${key}"]`).val(value);
        }
      }
    },

    /**
     * Process form submission
     */
    processFormSubmission: function (form) {
      // Check if debug mode is enabled - if so, don't use AJAX
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get("debug_filters") === "1") {
        return true; // Allow normal form submission for debugging
      }

      // Group form values by field name
      const formData = {};
      form.serializeArray().forEach(function (item) {
        if (item.value !== "" && item.value !== null) {
          // Remove [] from name if present
          const cleanName = item.name.replace(/\[\]$/, "");

          if (formData.hasOwnProperty(cleanName)) {
            if (!Array.isArray(formData[cleanName])) {
              formData[cleanName] = [formData[cleanName]];
            }
            formData[cleanName].push(item.value);
          } else {
            formData[cleanName] = item.value;
          }
        }
      });

      const params = new URLSearchParams();
      Object.keys(formData).forEach(function (key) {
        const value = formData[key];
        const cleanKey = key.replace(/\[\]$/, "");

        if (Array.isArray(value)) {
          // Use comma-separated format for bedrooms and bathrooms
          if (cleanKey === "bedrooms" || cleanKey === "bathrooms") {
            params.set(cleanKey, value.join(","));
          } else {
            // For other fields, use array format
            value.forEach((v) => params.append(cleanKey + "[]", v));
          }
        } else {
          // Single value
          params.set(cleanKey, value);
        }
      });

      // Get purpose from form or find closest loop/map
      let purpose = params.get("purpose");
      if (!purpose) {
        // Try to find the purpose from the closest loop
        const $closestLoop = form.closest(".kcpf-properties-loop");
        if ($closestLoop.length > 0) {
          purpose = $closestLoop.data("purpose");
        } else {
          // Try to find the purpose from the closest map view
          const $closestMap = form.closest(".kcpf-map-view");
          if ($closestMap.length > 0) {
            purpose = $closestMap.data("purpose");
          } else {
            // Fallback: find first loop on page
            const $firstLoop = $(".kcpf-properties-loop").first();
            if ($firstLoop.length > 0) {
              purpose = $firstLoop.data("purpose");
            } else {
              // Fallback: find first map view on page
              const $firstMap = $(".kcpf-map-view").first();
              if ($firstMap.length > 0) {
                purpose = $firstMap.data("purpose");
              }
            }
          }
        }
      }

      // Add purpose to params if found
      if (purpose) {
        params.set("purpose", purpose);
      }

      // Use AJAX handler to load properties
      if (window.KCPF_AjaxHandler) {
        KCPF_AjaxHandler.loadProperties(params);
      }
    },

    /**
     * Initialize accordion functionality
     */
    initAccordion: function () {
      const $accordion = $(".kcpf-accordion");
      if ($accordion.length === 0) {
        return;
      }

      // Set initial state based on screen size
      this.setInitialAccordionState($accordion);

      // Handle accordion toggle
      $accordion.on("click", ".kcpf-accordion-header", function () {
        const $thisAccordion = $(this).closest(".kcpf-accordion");
        KCPF_FormManager.toggleAccordion($thisAccordion);
      });

      // Handle window resize to update accordion state
      $(window).on("resize", function () {
        KCPF_FormManager.updateAccordionState($accordion);
      });
    },

    /**
     * Set initial accordion state based on screen size
     */
    setInitialAccordionState: function ($accordion) {
      const screenWidth = window.innerWidth;

      if (screenWidth >= 1024) {
        // Expanded by default on large screens
        $accordion.addClass("expanded");
      } else {
        // Collapsed by default on small screens
        $accordion.removeClass("expanded");
      }
    },

    /**
     * Toggle accordion state
     */
    toggleAccordion: function ($accordion) {
      if ($accordion.hasClass("expanded")) {
        $accordion.removeClass("expanded");
      } else {
        $accordion.addClass("expanded");
      }
    },

    /**
     * Update accordion state on window resize
     */
    updateAccordionState: function ($accordion) {
      const screenWidth = window.innerWidth;

      if (screenWidth >= 1024) {
        // Ensure expanded on large screens
        $accordion.addClass("expanded");
      } else {
        // Ensure collapsed on small screens
        $accordion.removeClass("expanded");
      }
    },
  };
})(jQuery);
