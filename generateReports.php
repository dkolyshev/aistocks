<?php
/**
 * Generate Reports Service
 * Generates HTML, PDF, and Flipbook reports based on settings
 * PHP 5.5 compatible
 */

// Load configuration and classes
require_once __DIR__ . '/app/config/config.php';
require_once APP_DIR . '/models/SettingsManager.php';
require_once APP_DIR . '/models/CsvDataReader.php';
require_once APP_DIR . '/services/ShortcodeProcessor.php';
require_once APP_DIR . '/services/HtmlReportGenerator.php';
require_once APP_DIR . '/services/PdfReportGenerator.php';
require_once APP_DIR . '/services/FlipbookGenerator.php';

/**
 * Generate reports for all settings
 */
function generateAllReports()
{
    $results = array();
    $settingsManager = new SettingsManager(SETTINGS_FILE);
    $allSettings = $settingsManager->getAllSettings();

    if (empty($allSettings)) {
        return array(
            'success' => false,
            'message' => 'No report settings found'
        );
    }

    foreach ($allSettings as $settings) {
        $result = generateReportForSetting($settings);
        $results[] = $result;
    }

    return array(
        'success' => true,
        'message' => 'Report generation completed',
        'results' => $results
    );
}

/**
 * Generate reports for a single setting
 * @param array $settings Report settings
 * @return array Result with status and messages
 */
function generateReportForSetting($settings)
{
    $fileName = $settings['file_name'];
    $result = array(
        'file_name' => $fileName,
        'html' => false,
        'pdf' => false,
        'flipbook' => false,
        'errors' => array()
    );

    try {
        // Load CSV data
        $csvReader = new CsvDataReader(DATA_CSV_FILE);
        if (!$csvReader->load()) {
            $result['errors'][] = 'Failed to load CSV data';
            return $result;
        }

        $stockCount = isset($settings['stock_count']) ? intval($settings['stock_count']) : 6;
        $stocks = $csvReader->getLimitedData($stockCount);

        if (empty($stocks)) {
            $result['errors'][] = 'No stock data available';
            return $result;
        }

        // Initialize shortcode processor
        $shortcodeProcessor = new ShortcodeProcessor();

        // Generate HTML report
        $htmlGenerator = new HtmlReportGenerator($settings, $stocks, $shortcodeProcessor);
        $htmlPath = REPORTS_DIR . '/' . $fileName . '.html';
        $result['html'] = $htmlGenerator->saveToFile($htmlPath);

        if (!$result['html']) {
            $result['errors'][] = 'Failed to generate HTML report';
        }

        // Generate PDF report
        $pdfGenerator = new PdfReportGenerator($settings, $htmlGenerator);
        $pdfPath = REPORTS_DIR . '/' . $fileName . '.pdf';
        $result['pdf'] = $pdfGenerator->generate($pdfPath);

        if (!$result['pdf']) {
            $result['errors'][] = 'Failed to generate PDF report (install wkhtmltopdf or upload manual PDF)';
        }

        // Generate Flipbook report
        $flipbookGenerator = new FlipbookGenerator($settings, $stocks, $shortcodeProcessor);
        $flipbookPath = REPORTS_DIR . '/' . $fileName . ' flipbook.html';
        $result['flipbook'] = $flipbookGenerator->saveToFile($flipbookPath);

        if (!$result['flipbook']) {
            $result['errors'][] = 'Failed to generate Flipbook report';
        }

    } catch (Exception $e) {
        $result['errors'][] = 'Exception: ' . $e->getMessage();
    }

    return $result;
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $generationResult = generateAllReports();

    // Redirect back to report manager with status
    $successCount = 0;
    $totalCount = count($generationResult['results']);

    foreach ($generationResult['results'] as $result) {
        if ($result['html'] || $result['pdf'] || $result['flipbook']) {
            $successCount++;
        }
    }

    $message = urlencode("Generated reports for {$successCount} of {$totalCount} configurations");
    header("Location: reportManager.php?message=" . $message);
    exit;
}

// If accessed directly (not POST), redirect to report manager
header("Location: reportManager.php");
exit;
