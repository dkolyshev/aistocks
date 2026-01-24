<!DOCTYPE html>
<html>
<head>
    <title><?php echo View::escape($title); ?></title>
    <meta charset="UTF-8">
    <script src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="https://go.trendadvisor.net/tools/flipbook/js/turn.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://go.trendadvisor.net/tools/flipbook/css/flipbook.css">
    <style>
<?php echo $styles; ?>
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="flipbook-viewport">
            <div class="container">
                <div class="flipbook" id="flipbook">
                    <?php echo $coverHtml; ?>
                    <?php echo $disclaimerHtml; ?>
                    <?php echo $introHtml; ?>
                    <?php foreach ($stockPages as $stockPage): ?>
                        <?php echo $stockPage; ?>
                    <?php endforeach; ?>
                </div>
                <?php echo $controlsHtml; ?>
            </div>
        </div>
    </div>
    <script type="text/javascript">
<?php echo $script; ?>
    </script>
</body>
</html>
