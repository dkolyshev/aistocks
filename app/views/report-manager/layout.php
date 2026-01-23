<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Manager | Stock Report Service</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 30px;
            padding-bottom: 50px;
        }

        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .form-group label {
            font-weight: bold;
            color: #495057;
        }

        .html-code-area {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.85rem;
            background-color: #f1f3f5;
        }

        .action-btns {
            width: 160px;
            text-align: right;
        }

        .container {
            max-width: 1100px;
        }

        .section-header {
            border-bottom: 2px solid #007bff;
            margin-bottom: 20px;
            padding-bottom: 10px;
            color: #007bff;
        }
    </style>
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

</body>

</html>