<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo View::escape($title); ?></title>
    <style>
<?php echo $styles; ?>
    </style>
</head>
<body>
    <div id="article-body">
        <?php if ($isPdfMode && !empty($coverHtml)): ?>
            <?php echo $coverHtml; ?>
        <?php endif; ?>

        <div class="report-content">
            <?php if (!empty($disclaimerHtml)): ?>
                <?php echo $disclaimerHtml; ?>
            <?php endif; ?>

            <div class="pagebreak">
                <h1><?php echo View::escape($title); ?></h1>
                <?php echo $articleImageHtml; ?>
                <?php if (!empty($introHtml)): ?>
                    <?php echo $introHtml; ?>
                <?php endif; ?>
            </div>

            <?php foreach ($stockBlocks as $stockBlock): ?>
                <?php echo $stockBlock; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
