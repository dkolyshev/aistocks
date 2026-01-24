<div class="page">
<?php if ($hasCoverImage): ?>
    <img src="<?php echo $coverImageDataUri; ?>" draggable="false" alt="" height="100%" width="100%">
<?php else: ?>
    <div class="default-cover">
        <h1><?php echo View::escape($title); ?></h1>
    </div>
<?php endif; ?>
</div>
