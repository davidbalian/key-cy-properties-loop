/**
 * Key CY Properties Filter - Multiselect Handler
 * Handles multiselect dropdown interactions
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Multiselect Handler
   */
  window.KCPF_MultiselectHandler = {
    /**
     * Initialize multiselect dropdowns
     */
    init: function () {
      console.log("[KCPF] Multiselect Handler starting initialization...");
      console.log("[KCPF] jQuery version:", $.fn.jquery);
      console.log("[KCPF] Document ready state:", document.readyState);

      const multiselectTriggers = $(".kcpf-multiselect-trigger").length;
      const multiselectDropdowns = $(".kcpf-multiselect-dropdown").length;
      const rangeTriggers = $(".kcpf-range-trigger").length;
      const rangeDropdowns = $(".kcpf-range-dropdown").length;

      console.log("[KCPF] Found multiselect triggers:", multiselectTriggers);
      console.log("[KCPF] Found multiselect dropdowns:", multiselectDropdowns);
      console.log("[KCPF] Found range triggers:", rangeTriggers);
      console.log("[KCPF] Found range dropdowns:", rangeDropdowns);

      // Setup handlers (delegated events work even if elements don't exist yet)
      this.handleDropdownToggle();
      this.handleRangeDropdownToggle();
      this.handleOutsideClick();
      this.handleChipRemoval();
      this.handleCheckboxChange();

      console.log("[KCPF] Multiselect Handler initialized successfully");
    },

    /**
     * Handle dropdown toggle
     */
    handleDropdownToggle: function () {
      console.log("[KCPF] Setting up dropdown toggle handler");

      // Use delegated event handler for trigger clicks
      $(document).on(
        "click",
        ".kcpf-multiselect-trigger, .kcpf-multiselect-trigger *",
        function (e) {
          // Don't toggle if clicking on a chip remove button
          if (
            $(e.target).hasClass("kcpf-chip-remove") ||
            $(e.target).closest(".kcpf-chip-remove").length
          ) {
            return;
          }

          e.stopPropagation();
          console.log("[KCPF] Dropdown trigger clicked");
          console.log("[KCPF] Clicked element:", e.target);

          const $trigger = $(e.target).closest(".kcpf-multiselect-trigger");
          console.log("[KCPF] Found trigger:", $trigger[0]);

          const $dropdown = $trigger.closest(".kcpf-multiselect-dropdown");
          console.log("[KCPF] Found dropdown:", $dropdown[0]);
          console.log(
            "[KCPF] Dropdown classes before:",
            $dropdown.attr("class")
          );

          if ($dropdown.length === 0) {
            console.error("[KCPF] No dropdown found!");
            return;
          }

          const isActive = $dropdown.hasClass("active");
          console.log("[KCPF] Is active:", isActive);

          // If current dropdown is active, close it and return
          if (isActive) {
            $dropdown.removeClass("active");
            console.log("[KCPF] Dropdown closed");
            console.log(
              "[KCPF] Dropdown classes after close:",
              $dropdown.attr("class")
            );
            return;
          }

          // Close all other dropdowns
          $(".kcpf-multiselect-dropdown").removeClass("active");
          $(".kcpf-range-dropdown").removeClass("active");

          // Open current dropdown
          $dropdown.addClass("active");
          console.log(
            "[KCPF] Dropdown opened for:",
            $dropdown.data("filter-name")
          );
          console.log(
            "[KCPF] Dropdown classes after open:",
            $dropdown.attr("class")
          );

          // Monitor for class changes
          setTimeout(() => {
            console.log("[KCPF] Classes after 100ms:", $dropdown.attr("class"));
          }, 100);

          setTimeout(() => {
            console.log("[KCPF] Classes after 500ms:", $dropdown.attr("class"));
          }, 500);
        }
      );
    },

    /**
     * Handle range dropdown toggle
     */
    handleRangeDropdownToggle: function () {
      $(document).on(
        "click",
        ".kcpf-range-trigger, .kcpf-range-trigger *",
        function (e) {
          e.stopPropagation();
          console.log("[KCPF] Range dropdown trigger clicked");

          const $trigger = $(e.target).closest(".kcpf-range-trigger");
          const $dropdown = $trigger.closest(".kcpf-range-dropdown");

          if ($dropdown.length === 0) {
            console.error("[KCPF] No range dropdown found!");
            return;
          }

          const isActive = $dropdown.hasClass("active");

          // If current dropdown is active, close it and return
          if (isActive) {
            $dropdown.removeClass("active");
            console.log("[KCPF] Range dropdown closed");
            return;
          }

          // Close all other dropdowns
          $(".kcpf-multiselect-dropdown").removeClass("active");
          $(".kcpf-range-dropdown").removeClass("active");

          // Open current dropdown
          $dropdown.addClass("active");
          console.log("[KCPF] Range dropdown opened");
        }
      );
    },

    /**
     * Close dropdowns when clicking outside
     */
    handleOutsideClick: function () {
      $(document).on("click", function (e) {
        // Don't close if clicking on a dropdown element or its children
        const $clicked = $(e.target);
        const isMultiselectDropdown =
          $clicked.closest(".kcpf-multiselect-dropdown").length > 0;
        const isRangeDropdown =
          $clicked.closest(".kcpf-range-dropdown").length > 0;

        if (!isMultiselectDropdown && !isRangeDropdown) {
          console.log("[KCPF] Outside click - closing all dropdowns");
          $(".kcpf-multiselect-dropdown").removeClass("active");
          $(".kcpf-range-dropdown").removeClass("active");
        }
      });
    },

    /**
     * Handle chip removal
     */
    handleChipRemoval: function () {
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
            "Select " +
            filterName.charAt(0).toUpperCase() +
            filterName.slice(1);
          $selected.html(
            '<span class="kcpf-placeholder">' + placeholder + "</span>"
          );
        }
      });
    },

    /**
     * Handle checkbox changes
     */
    handleCheckboxChange: function () {
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
    },
  };
})(jQuery);
