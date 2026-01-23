<?php echo View::render("report-manager/form", [
    "editMode" => $editMode,
    "editData" => $editData,
    "availableShortcodes" => $availableShortcodes,
]); ?>

<?php echo View::render("report-manager/table", ["allSettings" => $allSettings]); ?>
