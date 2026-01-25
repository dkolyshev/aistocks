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
                            <td><strong><?php echo View::escape($setting["file_name"]); ?></strong></td>
                            <td><?php echo View::escape($setting["report_title"]); ?></td>
                            <td><?php echo View::escape($setting["stock_count"]); ?></td>
                            <td class="action-btns">
                                <a href="<?php echo View::escape($formAction); ?>?edit=<?php echo urlencode($setting["file_name"]); ?>"
                                    class="btn btn-sm btn-outline-info">Edit</a>
                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirm('Delete this configuration?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="file_name" value="<?php echo View::escape($setting["file_name"]); ?>">
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