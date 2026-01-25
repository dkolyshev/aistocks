<?php echo View::render("report-manager/form", [
    "editMode" => $editMode,
    "editData" => $editData,
    "availableShortcodes" => $availableShortcodes,
    "availableDataSources" => $availableDataSources,
]); ?>

<?php echo View::render("report-manager/active-config-table", ["allSettings" => $allSettings]); ?>

<?php echo View::render("report-manager/reports-table", ["reportFiles" => $reportFiles]); ?>
