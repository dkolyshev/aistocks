<?php echo View::render("report-manager/form", [
    "editMode" => $editMode,
    "editData" => $editData,
    "fieldStates" => $fieldStates,
    "availableShortcodes" => $availableShortcodes,
    "availableDataSources" => $availableDataSources,
    "formAction" => $formAction,
]); ?>

<?php echo View::render("report-manager/active-config-table", [
    "allSettings" => $allSettings,
    "formAction" => $formAction,
]); ?>

<?php echo View::render("report-manager/reports-table", ["reportFiles" => $reportFiles]); ?>
