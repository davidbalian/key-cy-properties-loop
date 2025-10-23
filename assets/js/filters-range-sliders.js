/**
 * Key CY Properties Filter - Range Sliders
 * Handles range slider initialization and interactions
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Range Sliders Manager
   */
  window.KCPF_RangeSliders = {
    /**
     * Initialize range sliders
     */
    init: function () {
      this.initializeSliders();
      console.log("[KCPF] Range Sliders initialized");
    },

    /**
     * Initialize all range sliders on page
     */
    initializeSliders: function () {
      if (typeof noUiSlider === "undefined") {
        console.warn("[KCPF] noUiSlider library not loaded");
        return;
      }

      $(".kcpf-range-slider").each(function () {
        const $slider = $(this);
        const slider = this;

        // Skip if already initialized
        if (slider.noUiSlider) {
          console.log("[KCPF] Slider already initialized, skipping");
          return;
        }

        const $container = $slider.closest(".kcpf-range-slider-container");
        const $minInput = $container.find(".kcpf-range-min");
        const $maxInput = $container.find(".kcpf-range-max");

        const min = parseFloat($slider.data("min"));
        const max = parseFloat($slider.data("max"));
        const step = parseFloat($slider.data("step")) || 1;
        const valueMin = parseFloat($slider.data("value-min")) || min;
        const valueMax = parseFloat($slider.data("value-max")) || max;

        console.log("[KCPF] Creating slider with range:", min, "-", max);

        // Create slider
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
    },

    /**
     * Destroy all sliders in a container (used before refresh)
     *
     * @param {jQuery} $container - Container element
     */
    destroySliders: function ($container) {
      $container.find(".kcpf-range-slider").each(function () {
        if (this.noUiSlider) {
          this.noUiSlider.destroy();
        }
      });
    },
  };
})(jQuery);
