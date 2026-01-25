<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Manager | Stock Report Service</title>
    <link rel="icon" href="/assets/favicon/favicon.ico">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/report-manager.css">
    <link rel="stylesheet" href="/assets/css/report-manager-modern.css">
</head>

<body>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-5">Report Manager</h1>
            <form action="<?php echo View::escape($formAction); ?>" method="POST">
                <?php echo View::csrfField(); ?>
                <input type="hidden" name="action" value="generate">
                <button type="submit" class="btn btn-success btn-lg shadow-sm">&#128640; Run Report Generation Service</button>
            </form>
        </div>

        <?php if (!empty($message)): ?>
            <?php echo View::render("report-manager/alert", ["message" => $message, "messageType" => $messageType]); ?>
        <?php endif; ?>

        <?php echo $content; ?>
    </div>

    <footer class="text-center mt-5 mb-5 text-muted">
        <hr>
        <div class="footer-meta">
            <small>
                System Date: <?php echo date(DATE_FORMAT); ?> | 
                <a href="/phpinfo" target="_blank">PHP <?php echo PHP_VERSION; ?></a> Compatibility Mode
            </small>
        </div>
        <div class="footer-theme">
            <label for="theme-select" class="mb-0">Theme</label>
            <select id="theme-select" class="custom-select custom-select-sm ml-2">
                <option value="default">Classic</option>
                <option value="modern">Some new colors</option>
            </select>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="/assets/js/report-manager.js"></script>

</body>

</html>
