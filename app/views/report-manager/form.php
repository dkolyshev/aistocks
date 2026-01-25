<div class="card mb-5">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><?php echo $editMode ? "Edit" : "Create"; ?> Report Settings</h5>
    </div>
    <div class="card-body">
        <form action="<?php echo View::escape($formAction); ?>" method="POST" enctype="multipart/form-data">
            <?php echo View::csrfField(); ?>
            <input type="hidden" name="action" value="<?php echo $editMode ? "update" : "add"; ?>">
            <?php if ($editMode): ?>
                <input type="hidden" name="original_file_name" value="<?php echo View::escape($editData["file_name"]); ?>">
                <input type="hidden" name="existing_article_image" value="<?php echo isset($editData["article_image"])
                    ? View::escape($editData["article_image"])
                    : ""; ?>">
                <input type="hidden" name="existing_pdf_cover" value="<?php echo isset($editData["pdf_cover_image"])
                    ? View::escape($editData["pdf_cover_image"])
                    : ""; ?>">
                <input type="hidden" name="existing_manual_pdf" value="<?php echo isset($editData["manual_pdf_path"])
                    ? View::escape($editData["manual_pdf_path"])
                    : ""; ?>">
            <?php endif; ?>

            <h6 class="section-header">Basic Information</h6>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Report File Name</label>
                    <input type="text" name="file_name" class="form-control" value="<?php echo $editMode ? View::escape($editData["file_name"]) : ""; ?>"
                        <?php echo $editMode ? "readonly" : "required"; ?>>
                    <small class="text-muted">Used for output URL (e.g. 6AIStocks.html)</small>
                </div>
                <div class="col-md-5 form-group">
                    <label>Report Title</label>
                    <input type="text" name="report_title" class="form-control" value="<?php echo $editMode
                        ? View::escape($editData["report_title"])
                        : ""; ?>" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Author Name</label>
                    <input type="text" name="author_name" class="form-control" value="<?php echo $editMode ? View::escape($editData["author_name"]) : ""; ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Number of Stocks</label>
                    <input type="number" name="stock_count" class="form-control"
                        value="<?php echo $editMode ? View::escape($editData["stock_count"]) : "6"; ?>" min="1">
                </div>
                <div class="col-md-3 form-group">
                    <label>Data Source</label>
                    <select name="api_placeholder" class="form-control" required>
                        <option value="">-- Select data source --</option>
                        <?php
                        $currentDataSource = $editMode && isset($editData["api_placeholder"]) ? $editData["api_placeholder"] : "";
                        foreach ($availableDataSources as $dataSource):
                            $selected = $dataSource === $currentDataSource ? "selected" : ""; ?>
                            <option value="<?php echo View::escape($dataSource); ?>" <?php echo $selected; ?>>
                                <?php echo View::escape($dataSource); ?>
                            </option>
                        <?php
                        endforeach;
                        ?>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Article Image (180x180)</label>
                    <input type="file" name="article_image" class="form-control-file" accept="image/*">
                    <?php if ($editMode && !empty($editData["article_image"])): ?>
                        <small class="text-muted">Current: <?php echo basename($editData["article_image"]); ?></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-3 form-group">
                    <label>PDF Cover Image</label>
                    <input type="file" name="pdf_cover" class="form-control-file" accept="image/*">
                    <?php if ($editMode && !empty($editData["pdf_cover_image"])): ?>
                        <small class="text-muted">Current: <?php echo basename($editData["pdf_cover_image"]); ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <h6 class="section-header mt-4">Report Content (HTML Templates)</h6>
            <div class="small text-muted mb-3">
                <strong>Available shortcodes:</strong> <span class="text-info shortcode-hint">(click to copy)</span>
                <div class="shortcode-list mt-2">
                    <div class="mb-1">
                        <em>Special:</em>
                        <?php foreach ($availableShortcodes["special"] as $code): ?>
                            <span class="shortcode-copy shortcode-badge badge-secondary" data-shortcode="<?php echo View::escape(
                                $code
                            ); ?>"><?php echo View::escape($code); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($availableShortcodes["data"])): ?>
                        <div>
                            <em>Data columns:</em>
                            <?php foreach ($availableShortcodes["data"] as $code): ?>
                                <span class="shortcode-copy shortcode-badge badge-light" data-shortcode="<?php echo View::escape(
                                    $code
                                ); ?>"><?php echo View::escape($code); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group html-template-field" data-field="report_intro_html" data-default-template="<?php echo View::escape(
                DEFAULT_REPORT_INTRO_HTML
            ); ?>">
                <label>Report Intro HTML</label>
                <div class="field-state-selector btn-group btn-group-sm mb-2" role="group">
                    <input type="radio" class="btn-check" name="report_intro_html_state" id="report_intro_html_default" value="default" <?php echo $fieldStates[
                        "report_intro_html"
                    ] === "default"
                        ? "checked"
                        : ""; ?>>
                    <label class="btn btn-outline-primary" for="report_intro_html_default">Default</label>
                    <input type="radio" class="btn-check" name="report_intro_html_state" id="report_intro_html_custom" value="custom" <?php echo $fieldStates[
                        "report_intro_html"
                    ] === "custom"
                        ? "checked"
                        : ""; ?>>
                    <label class="btn btn-outline-primary" for="report_intro_html_custom">Custom</label>
                    <input type="radio" class="btn-check" name="report_intro_html_state" id="report_intro_html_empty" value="empty" <?php echo $fieldStates[
                        "report_intro_html"
                    ] === "empty"
                        ? "checked"
                        : ""; ?>>
                    <label class="btn btn-outline-primary" for="report_intro_html_empty">Empty</label>
                </div>
                <textarea name="report_intro_html" class="form-control html-code-area" rows="4"
                    placeholder="<p>Welcome to our daily stock analysis...</p>"
                    <?php echo $fieldStates["report_intro_html"] === "empty" ? "readonly" : ""; ?>><?php echo $editMode
    ? View::escape($editData["report_intro_html"])
    : ""; ?></textarea>
            </div>

            <div class="form-group html-template-field" data-field="stock_block_html" data-default-template="<?php echo View::escape(
                DEFAULT_REPORT_STOCK_BLOCK_HTML
            ); ?>">
                <label>Stock Block HTML</label>
                <div class="field-state-selector btn-group btn-group-sm mb-2" role="group">
                    <input type="radio" class="btn-check" name="stock_block_html_state" id="stock_block_html_default" value="default" <?php echo $fieldStates[
                        "stock_block_html"
                    ] === "default"
                        ? "checked"
                        : ""; ?>>
                    <label class="btn btn-outline-primary" for="stock_block_html_default">Default</label>
                    <input type="radio" class="btn-check" name="stock_block_html_state" id="stock_block_html_custom" value="custom" <?php echo $fieldStates[
                        "stock_block_html"
                    ] === "custom"
                        ? "checked"
                        : ""; ?>>
                    <label class="btn btn-outline-primary" for="stock_block_html_custom">Custom</label>
                </div>
                <textarea name="stock_block_html" class="form-control html-code-area" rows="6"
                    placeholder="<div class='stock-card'><h3>[Company] ([Ticker])</h3><p>Price: [Price]</p><div>[Chart]</div></div>"
                    <?php echo $fieldStates["stock_block_html"] === "empty" ? "readonly" : ""; ?>><?php echo $editMode
    ? View::escape($editData["stock_block_html"])
    : ""; ?></textarea>
            </div>

            <div class="form-group html-template-field" data-field="disclaimer_html" data-default-template="<?php echo View::escape(
                DEFAULT_REPORT_DISCLAIMER_HTML
            ); ?>">
                <label>Disclaimer HTML</label>
                <div class="field-state-selector btn-group btn-group-sm mb-2" role="group">
                    <input type="radio" class="btn-check" name="disclaimer_html_state" id="disclaimer_html_default" value="default" <?php echo $fieldStates[
                        "disclaimer_html"
                    ] === "default"
                        ? "checked"
                        : ""; ?>>
                    <label class="btn btn-outline-primary" for="disclaimer_html_default">Default</label>
                    <input type="radio" class="btn-check" name="disclaimer_html_state" id="disclaimer_html_custom" value="custom" <?php echo $fieldStates[
                        "disclaimer_html"
                    ] === "custom"
                        ? "checked"
                        : ""; ?>>
                    <label class="btn btn-outline-primary" for="disclaimer_html_custom">Custom</label>
                    <input type="radio" class="btn-check" name="disclaimer_html_state" id="disclaimer_html_empty" value="empty" <?php echo $fieldStates[
                        "disclaimer_html"
                    ] === "empty"
                        ? "checked"
                        : ""; ?>>
                    <label class="btn btn-outline-primary" for="disclaimer_html_empty">Empty</label>
                </div>
                <textarea name="disclaimer_html" class="form-control html-code-area" rows="3"
                    placeholder="<p><i>Disclaimer: Investment carries risk...</i></p>"
                    <?php echo $fieldStates["disclaimer_html"] === "empty" ? "readonly" : ""; ?>><?php echo $editMode
    ? View::escape($editData["disclaimer_html"])
    : ""; ?></textarea>
            </div>

            <div class="mt-4 p-3 bg-light border rounded">
                <label><strong>Manual PDF Override</strong></label>
                <input type="file" name="manual_pdf" class="form-control-file" accept="application/pdf">
                <small class="text-danger">Uploading a file here will overwrite the generated PDF in /reports/</small>
                <?php if ($editMode && !empty($editData["manual_pdf_path"])): ?>
                    <br><small class="text-muted">Current: <?php echo basename($editData["manual_pdf_path"]); ?></small>
                <?php endif; ?>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-block">
                    <?php echo $editMode ? "Update" : "Save"; ?> Report Configuration
                </button>
                <?php if ($editMode): ?>
                    <a href="<?php echo View::escape($formAction); ?>" class="btn btn-secondary btn-block">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>