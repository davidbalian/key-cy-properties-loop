<?php
/**
 * Style Preview Class
 * 
 * Renders filter previews for the dashboard editor
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Style_Preview
{
    /**
     * Render all filter previews
     * 
     * @return string
     */
    public static function render()
    {
        ob_start();
        ?>
        <div class="kcpf-style-preview">
            <h3>Filter Previews</h3>
            
            <form class="kcpf-filters-form">
            <div class="kcpf-preview-section">
                <h4>Select Dropdown</h4>
                <div class="kcpf-filter kcpf-filter-location">
                    <label>Location</label>
                    <select class="kcpf-filter-select">
                        <option value="">Select Location</option>
                        <option value="limassol">Limassol</option>
                        <option value="nicosia">Nicosia</option>
                        <option value="paphos">Paphos</option>
                    </select>
                </div>
            </div>
            
            <div class="kcpf-preview-section">
                <h4>Multi-Select Dropdown</h4>
                <div class="kcpf-filter kcpf-filter-bedrooms">
                    <label>Bedrooms</label>
                    <div class="kcpf-multiselect-dropdown">
                        <div class="kcpf-multiselect-trigger">
                            <div class="kcpf-multiselect-selected">
                                <span class="kcpf-chip">
                                    2 Bedrooms
                                    <button type="button" class="kcpf-chip-remove">&times;</button>
                                </span>
                                <span class="kcpf-chip">
                                    3 Bedrooms
                                    <button type="button" class="kcpf-chip-remove">&times;</button>
                                </span>
                            </div>
                            <span class="kcpf-multiselect-arrow">▼</span>
                        </div>
                        <div class="kcpf-multiselect-dropdown-menu">
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox" checked>
                                <span>1 Bedroom</span>
                            </label>
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox" checked>
                                <span>2 Bedrooms</span>
                            </label>
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox" checked>
                                <span>3 Bedrooms</span>
                            </label>
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox">
                                <span>4 Bedrooms</span>
                            </label>
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox">
                                <span>5+ Bedrooms</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kcpf-preview-section">
                <h4>Multi-Select Dropdown (Empty)</h4>
                <div class="kcpf-filter kcpf-filter-bathrooms">
                    <label>Bathrooms</label>
                    <div class="kcpf-multiselect-dropdown">
                        <div class="kcpf-multiselect-trigger">
                            <div class="kcpf-multiselect-selected">
                                <span class="kcpf-placeholder">Select Bathrooms</span>
                            </div>
                            <span class="kcpf-multiselect-arrow">▼</span>
                        </div>
                        <div class="kcpf-multiselect-dropdown-menu">
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox">
                                <span>1 Bathroom</span>
                            </label>
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox">
                                <span>2 Bathrooms</span>
                            </label>
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox">
                                <span>3 Bathrooms</span>
                            </label>
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox">
                                <span>4+ Bathrooms</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kcpf-preview-section">
                <h4>Text Input</h4>
                <div class="kcpf-filter kcpf-filter-property-id">
                    <label>Property ID</label>
                    <input type="text" class="kcpf-input" placeholder="Search by Property ID" value="">
                </div>
            </div>
            
            <div class="kcpf-preview-section">
                <h4>Range Slider</h4>
                <div class="kcpf-filter kcpf-filter-price">
                    <label>Price Range</label>
                    <div class="kcpf-range-slider-container">
                        <div class="kcpf-range-slider"></div>
                        <div class="kcpf-range-inputs">
                            <input type="number" class="kcpf-input kcpf-range-min" placeholder="Min Price" value="100000">
                            <span class="kcpf-range-separator">-</span>
                            <input type="number" class="kcpf-input kcpf-range-max" placeholder="Max Price" value="500000">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kcpf-preview-section">
                <h4>Toggle Buttons</h4>
                <div class="kcpf-filter kcpf-filter-purpose">
                    <label>Purpose</label>
                    <div class="kcpf-toggle-buttons">
                        <label class="kcpf-toggle-label active">
                            <input type="radio" name="preview_purpose" value="sale" checked>
                            <span>Sale</span>
                        </label>
                        <label class="kcpf-toggle-label">
                            <input type="radio" name="preview_purpose" value="rent">
                            <span>Rent</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="kcpf-preview-section">
                <h4>Action Buttons</h4>
                <div class="kcpf-filter kcpf-filter-apply">
                    <button type="button" class="kcpf-apply-button">Apply Filters</button>
                </div>
                <div class="kcpf-filter kcpf-filter-reset">
                    <a href="#" class="kcpf-reset-button">Reset Filters</a>
                </div>
            </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}

