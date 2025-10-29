/**
 * Single Property Map JavaScript
 *
 * Handles Google Maps integration for single property pages with radius circle
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Single Property Map Controller
   */
  const KCPFSinglePropertyMap = {
    map: null,
    circle: null,
    propertyId: null,
    propertyData: null,

    /**
     * Initialize single property map
     */
    init: function () {
      // Check if single property map container exists
      const mapContainer = $(".kcpf-single-property-map");
      if (!mapContainer.length) {
        return;
      }

      // Get property data from container
      this.propertyId = mapContainer.data("property-id");
      const lat = mapContainer.data("lat");
      const lng = mapContainer.data("lng");
      const zoom = mapContainer.data("zoom") || 15;
      const purpose = mapContainer.data("purpose") || "sale";

      if (!lat || !lng) {
        console.error("[KCPF Single Map] No coordinates found");
        return;
      }

      this.propertyData = {
        id: this.propertyId,
        lat: lat,
        lng: lng,
        zoom: zoom,
        purpose: purpose,
      };

      // Initialize Google Map when API is ready
      if (typeof google !== "undefined" && google.maps) {
        this.initializeMap();
      } else {
        // Wait for Google Maps to load
        this.waitForGoogleMaps();
      }
    },

    /**
     * Wait for Google Maps API to load
     */
    waitForGoogleMaps: function () {
      const self = this;
      let attempts = 0;
      const maxAttempts = 50; // 5 seconds max

      const checkGoogleMaps = function () {
        attempts++;
        if (typeof google !== "undefined" && google.maps) {
          self.initializeMap();
        } else if (attempts < maxAttempts) {
          setTimeout(checkGoogleMaps, 100);
        } else {
          console.error("[KCPF Single Map] Google Maps failed to load");
          self.showError("Map failed to load. Please refresh the page.");
        }
      };

      checkGoogleMaps();
    },

    /**
     * Initialize Google Map
     */
    initializeMap: function () {
      console.log("[KCPF Single Map] Initializing Google Map");

      const mapElement = document.getElementById(
        "kcpf-single-property-map-" + this.propertyId
      );

      if (!mapElement) {
        console.error("[KCPF Single Map] Map element not found");
        return;
      }

      // Create map
      this.map = new google.maps.Map(mapElement, {
        zoom: this.propertyData.zoom,
        center: {
          lat: this.propertyData.lat,
          lng: this.propertyData.lng,
        },
        mapTypeControl: false,
        streetViewControl: true,
        fullscreenControl: true,
        zoomControl: true,
        maxZoom: 17,
        mapId: "2fbf45e46e78d50c9d90cd84",
        styles: [
          {
            featureType: "poi",
            elementType: "labels",
            stylers: [{ visibility: "off" }],
          },
        ],
      });

      // Add property circle
      this.addPropertyCircle();

      // Show loading complete
      this.hideLoading();
    },

    /**
     * Add property circle with radius
     */
    addPropertyCircle: function () {
      console.log("[KCPF Single Map] Adding property circle");

      // Create 200m radius circle (black) - same as archive maps
      this.circle = new google.maps.Circle({
        strokeColor: "#000000",
        strokeOpacity: 0.6,
        strokeWeight: 2,
        fillColor: "#000000",
        fillOpacity: 0.15,
        map: this.map,
        center: {
          lat: this.propertyData.lat,
          lng: this.propertyData.lng,
        },
        radius: 200, // 200 meters
        clickable: false,
      });

      console.log("[KCPF Single Map] Property circle added");
    },

    /**
     * Hide loading state
     */
    hideLoading: function () {
      $(".kcpf-single-map-loading").hide();
    },

    /**
     * Show error message
     */
    showError: function (message) {
      $(".kcpf-single-map-loading").html(
        '<div class="kcpf-single-map-error"><p>' + message + "</p></div>"
      );
    },
  };

  /**
   * Initialize when document is ready
   */
  $(document).ready(function () {
    // Wait a bit for Google Maps to load
    setTimeout(() => {
      KCPFSinglePropertyMap.init();
    }, 100);
  });

  // Also try to initialize when Google Maps callback is triggered
  window.kcpfInitSinglePropertyMap = function () {
    console.log("[KCPF Single Map] Google Maps API loaded via callback");
    KCPFSinglePropertyMap.init();
  };

  // Make KCPFSinglePropertyMap globally available
  window.KCPFSinglePropertyMap = KCPFSinglePropertyMap;
})(jQuery);
