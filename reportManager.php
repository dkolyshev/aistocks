<?php
/**
 * Report Manager - CRUD Interface for Stock Report Settings
 * PHP 5.5 compatible
 */

// Load configuration and classes
require_once __DIR__ . '/app/config/config.php';
require_once APP_DIR . '/models/SettingsManager.php';
require_once APP_DIR . '/services/FileUploadHandler.php';
require_once APP_DIR . '/controllers/ReportController.php';

// Initialize controller
$controller = new ReportController();

// Handle form submissions
$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $result = $controller->handleDelete();
    } else {
        $result = $controller->handleSettingsSubmission();
    }

    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'danger';
}

// Handle GET messages (from redirects)
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
    $messageType = 'success';
}

// Get all settings for display
$allSettings = $controller->getAllSettings();

// Get setting for editing if edit parameter is present
$editMode = false;
$editData = null;
if (isset($_GET['edit'])) {
    $editData = $controller->getSettingByFileName($_GET['edit']);
    if ($editData !== null) {
        $editMode = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Manager | Stock Report Service</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; padding-top: 30px; padding-bottom: 50px; }
        .card { box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: none; }
        .form-group label { font-weight: bold; color: #495057; }
        .html-code-area { font-family: 'Courier New', Courier, monospace; font-size: 0.85rem; background-color: #f1f3f5; }
        .action-btns { width: 160px; text-align: right; }
        .container { max-width: 1100px; }
        .section-header { border-bottom: 2px solid #007bff; margin-bottom: 20px; padding-bottom: 10px; color: #007bff; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-5">Report Manager</h1>
        <form action="generateReports.php" method="POST">
            <button type="submit" class="btn btn-success btn-lg shadow-sm">&#128640; Run Report Generation Service</button>
        </form>
    </div>

    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>

    <div class="card mb-5">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><?php echo $editMode ? 'Edit' : 'Create'; ?> Report Settings</h5>
        </div>
        <div class="card-body">
            <form action="reportManager.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editMode ? 'update' : 'add'; ?>">
                <?php if ($editMode): ?>
                <input type="hidden" name="original_file_name" value="<?php echo htmlspecialchars($editData['file_name']); ?>">
                <input type="hidden" name="existing_article_image" value="<?php echo isset($editData['article_image']) ? htmlspecialchars($editData['article_image']) : ''; ?>">
                <input type="hidden" name="existing_pdf_cover" value="<?php echo isset($editData['pdf_cover_image']) ? htmlspecialchars($editData['pdf_cover_image']) : ''; ?>">
                <input type="hidden" name="existing_manual_pdf" value="<?php echo isset($editData['manual_pdf_path']) ? htmlspecialchars($editData['manual_pdf_path']) : ''; ?>">
                <?php endif; ?>

                <h6 class="section-header">Basic Information</h6>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Report File Name</label>
                        <input type="text" name="file_name" class="form-control" placeholder="6AIStocks"
                               value="<?php echo $editMode ? htmlspecialchars($editData['file_name']) : ''; ?>"
                               <?php echo $editMode ? 'readonly' : 'required'; ?>>
                        <small class="text-muted">Used for output URL (e.g. 6AIStocks.html)</small>
                    </div>
                    <div class="col-md-5 form-group">
                        <label>Report Title</label>
                        <input type="text" name="report_title" class="form-control" placeholder="Today's Top 6 AI Stocks"
                               value="<?php echo $editMode ? htmlspecialchars($editData['report_title']) : ''; ?>" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Author Name</label>
                        <input type="text" name="author_name" class="form-control" placeholder="Today's Top Stocks"
                               value="<?php echo $editMode ? htmlspecialchars($editData['author_name']) : ''; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Number of Stocks</label>
                        <input type="number" name="stock_count" class="form-control"
                               value="<?php echo $editMode ? htmlspecialchars($editData['stock_count']) : '6'; ?>" min="1">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Data Source</label>
                        <input type="text" name="api_placeholder" class="form-control" value="data.csv" readonly>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Article Image (180x180)</label>
                        <input type="file" name="article_image" class="form-control-file" accept="image/*">
                        <?php if ($editMode && !empty($editData['article_image'])): ?>
                        <small class="text-muted">Current: <?php echo basename($editData['article_image']); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>PDF Cover Image</label>
                        <input type="file" name="pdf_cover" class="form-control-file" accept="image/*">
                        <?php if ($editMode && !empty($editData['pdf_cover_image'])): ?>
                        <small class="text-muted">Current: <?php echo basename($editData['pdf_cover_image']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <h6 class="section-header mt-4">Report Content (HTML Templates)</h6>
                <p class="small text-muted">Use [Company], [Ticker], [Price], [Chart], and [Current Date] as shortcodes.</p>

                <div class="form-group">
                    <label>Report Intro HTML</label>
                    <textarea name="report_intro_html" class="form-control html-code-area" rows="4"
                              placeholder="<p>Welcome to our daily stock analysis...</p>"><?php echo $editMode ? htmlspecialchars($editData['report_intro_html']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Stock Block HTML</label>
                    <textarea name="stock_block_html" class="form-control html-code-area" rows="6"
                              placeholder="<div class='stock-card'><h3>[Company] ([Ticker])</h3><p>Price: [Price]</p><div>[Chart]</div></div>"><?php echo $editMode ? htmlspecialchars($editData['stock_block_html']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Disclaimer HTML</label>
                    <textarea name="disclaimer_html" class="form-control html-code-area" rows="3"
                              placeholder="<p><i>Disclaimer: Investment carries risk...</i></p>"><?php echo $editMode ? htmlspecialchars($editData['disclaimer_html']) : ''; ?></textarea>
                </div>

                <div class="mt-4 p-3 bg-light border rounded">
                    <label><strong>Manual PDF Override</strong></label>
                    <input type="file" name="manual_pdf" class="form-control-file" accept="application/pdf">
                    <small class="text-danger">Uploading a file here will overwrite the generated PDF in /reports/</small>
                    <?php if ($editMode && !empty($editData['manual_pdf_path'])): ?>
                    <br><small class="text-muted">Current: <?php echo basename($editData['manual_pdf_path']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-block">
                        <?php echo $editMode ? 'Update' : 'Save'; ?> Report Configuration
                    </button>
                    <?php if ($editMode): ?>
                    <a href="reportManager.php" class="btn btn-secondary btn-block">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Active Configurations</h5>
            <span class="badge badge-light">Stored in reportSettings.json</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($allSettings)): ?>
            <div class="p-4 text-center text-muted">
                <p>No configurations yet. Create your first report configuration above.</p>
            </div>
            <?php else: ?>
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Filename</th>
                        <th>Title</th>
                        <th>Stocks</th>
                        <th class="action-btns">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allSettings as $setting): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($setting['file_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($setting['report_title']); ?></td>
                        <td><?php echo htmlspecialchars($setting['stock_count']); ?></td>
                        <td class="action-btns">
                            <a href="reportManager.php?edit=<?php echo urlencode($setting['file_name']); ?>"
                               class="btn btn-sm btn-outline-info">Edit</a>
                            <form method="POST" style="display: inline;"
                                  onsubmit="return confirm('Delete this configuration?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="file_name" value="<?php echo htmlspecialchars($setting['file_name']); ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="text-center mt-5 mb-5 text-muted">
    <hr>
    <small>System Date: <?php echo date(DATE_FORMAT); ?> | PHP <?php echo PHP_VERSION; ?> Compatibility Mode</small>
</footer>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>
