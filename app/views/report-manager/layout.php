<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Manager | Stock Report Service</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/report-manager.css">
</head>

<body>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-5">Report Manager</h1>
            <form action="reportManager.html" method="POST">
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
        <small>System Date: <?php echo date(DATE_FORMAT); ?> | PHP <?php echo PHP_VERSION; ?> Compatibility Mode</small>
    </footer>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="/assets/js/report-manager.js"></script>

</body>

</html>