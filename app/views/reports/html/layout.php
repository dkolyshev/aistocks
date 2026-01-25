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
        <div class="report-content">
            <div class="pagebreak margin-bottom">
                <h1><?php echo View::escape($title); ?></h1>
                <?php if (!empty($disclaimerHtml)): ?>
                    <?php echo $disclaimerHtml; ?>
                <?php endif; ?>
            </div>

            <hr/>

            <div class="pagebreak">
                <?php if (!empty($introHtml)): ?>
                    <?php echo $articleImageHtml; ?>
                    <?php echo $introHtml; ?>
                <?php else: ?>
                    <div class="display-inline-block"><?php echo $articleImageHtml; ?></div>
                <?php endif; ?>
            </div>

            <?php foreach ($stockBlocks as $stockBlock): ?>
                <?php echo $stockBlock; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
