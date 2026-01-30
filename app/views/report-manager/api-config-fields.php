<?php
/**
 * API Configuration Fields Component
 * Displays configuration options for API data sources
 */

// Get current API configuration if in edit mode
$apiEndpoint = '';
$apiFilters = array();

if ($editMode && isset($editData['api_config'])) {
    if (is_array($editData['api_config'])) {
        $apiEndpoint = isset($editData['api_config']['endpoint']) ? $editData['api_config']['endpoint'] : '';
        $apiFilters = isset($editData['api_config']['filters']) ? $editData['api_config']['filters'] : array();
    }
}
?>

<div class="api-config-section">
    <div class="form-group">
        <label>API Endpoint</label>
        <select name="api_endpoint" id="api_endpoint" class="form-control">
            <option value="">-- Select API Endpoint --</option>
            <option value="most-actives" <?php echo $apiEndpoint === 'most-actives' ? 'selected' : ''; ?>>Most Active Stocks</option>
        </select>
        <small class="text-muted">Retrieves the most actively traded stocks</small>
    </div>

    <div class="card bg-light mb-3">
        <div class="card-header">
            <strong>Filter Configuration</strong>
            <small class="text-muted">(Optional - leave blank for no filtering)</small>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Market Cap (Min)</label>
                    <input type="number" name="api_filter_marketcap_min" class="form-control"
                        value="<?php echo isset($apiFilters['marketcap_min']) ? View::escape($apiFilters['marketcap_min']) : ''; ?>"
                        placeholder="e.g., 1000000000" min="0">
                    <small class="text-muted">Minimum market capitalization in USD</small>
                </div>
                <div class="col-md-6 form-group">
                    <label>Market Cap (Max)</label>
                    <input type="number" name="api_filter_marketcap_max" class="form-control"
                        value="<?php echo isset($apiFilters['marketcap_max']) ? View::escape($apiFilters['marketcap_max']) : ''; ?>"
                        placeholder="e.g., 100000000000" min="0">
                    <small class="text-muted">Maximum market capitalization in USD</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Price (Min)</label>
                    <input type="number" name="api_filter_price_min" class="form-control"
                        value="<?php echo isset($apiFilters['price_min']) ? View::escape($apiFilters['price_min']) : ''; ?>"
                        placeholder="e.g., 10" step="0.01" min="0">
                    <small class="text-muted">Minimum stock price in USD</small>
                </div>
                <div class="col-md-6 form-group">
                    <label>Price (Max)</label>
                    <input type="number" name="api_filter_price_max" class="form-control"
                        value="<?php echo isset($apiFilters['price_max']) ? View::escape($apiFilters['price_max']) : ''; ?>"
                        placeholder="e.g., 500" step="0.01" min="0">
                    <small class="text-muted">Maximum stock price in USD</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Exchange</label>
                    <select name="api_filter_exchange" class="form-control">
                        <option value="">-- All Exchanges --</option>
                        <option value="NYSE" <?php echo isset($apiFilters['exchange']) && $apiFilters['exchange'] === 'NYSE' ? 'selected' : ''; ?>>NYSE</option>
                        <option value="NASDAQ" <?php echo isset($apiFilters['exchange']) && $apiFilters['exchange'] === 'NASDAQ' ? 'selected' : ''; ?>>NASDAQ</option>
                        <option value="AMEX" <?php echo isset($apiFilters['exchange']) && $apiFilters['exchange'] === 'AMEX' ? 'selected' : ''; ?>>AMEX</option>
                    </select>
                    <small class="text-muted">Filter by stock exchange</small>
                </div>
                <div class="col-md-6 form-group">
                    <label>Country</label>
                    <input type="text" name="api_filter_country" class="form-control"
                        value="<?php echo isset($apiFilters['country']) ? View::escape($apiFilters['country']) : ''; ?>"
                        placeholder="e.g., US, CA">
                    <small class="text-muted">Filter by country code</small>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <button type="button" id="api-preview-btn" class="btn btn-info">
            <span id="preview-btn-text">Test API Connection & Preview Data</span>
            <span id="preview-btn-spinner" style="display: none;">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Loading...
            </span>
        </button>
        <small class="text-muted d-block mt-2">Click to verify API connectivity and see available data fields</small>
    </div>

    <div id="api-preview-result" class="mt-3" style="display: none;">
        <div class="card">
            <div class="card-header bg-success text-white">
                <strong>API Preview Results</strong>
            </div>
            <div class="card-body">
                <div id="api-preview-content"></div>
            </div>
        </div>
    </div>

    <div id="api-shortcodes-display" class="mt-3" style="display: none;">
        <div class="alert alert-info">
            <strong>Available Shortcodes from API:</strong>
            <div id="api-shortcodes-list" class="mt-2"></div>
        </div>
    </div>
</div>
