<div class="card mt-4" id="reports-table">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Generated Reports</h5>
        <?php if (!empty($reportFiles)): ?>
            <form method="POST" style="display: inline;"
                onsubmit="return confirm('Delete ALL report files? This cannot be undone!');">
                <?php echo View::csrfField(); ?>
                <input type="hidden" name="action" value="delete_all_reports">
                <button type="submit" class="btn btn-sm btn-danger">Delete All Reports</button>
            </form>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <?php if (empty($reportFiles)): ?>
            <div class="p-4 text-center text-muted">
                <p>No report files found. Generate reports using the button above.</p>
            </div>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Filename</th>
                        <th>Created</th>
                        <th class="action-btns">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportFiles as $file): ?>
                        <tr>
                            <td><strong><?php echo View::escape($file["filename"]); ?></strong></td>
                            <td><?php echo View::escape($file["created"]); ?></td>
                            <td class="action-btns">
                                <a href="<?php echo View::url('/reports/' . urlencode($file["filename"])); ?>"
                                    target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="<?php echo View::url('/reports/' . urlencode($file["filename"])); ?>"
                                    download class="btn btn-sm btn-outline-secondary">Download</a>
                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirm('Delete this report file?');">
                                    <?php echo View::csrfField(); ?>
                                    <input type="hidden" name="action" value="delete_report">
                                    <input type="hidden" name="report_filename" value="<?php echo View::escape($file["filename"]); ?>">
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
