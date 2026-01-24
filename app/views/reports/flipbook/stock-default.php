<div class="order-md-1">
    <h2 class="mt-1">
        <?php echo $stockNumber; ?>) 
        <?php echo View::escape($company); ?> 
        (<?php
        echo View::escape($exchange);
        echo $showExchangeDelimiter ? ":" : "";
        echo View::escape($ticker);
        ?>)
    </h2>
    <?php if (!empty($chartHtml)): ?>
        <?php echo $chartHtml; ?>
    <?php endif; ?>
    <br>
    <?php if (isset($price)): ?>
        <strong>Closing Price: </strong>$<?php echo View::escape($price); ?><br>
    <?php endif; ?>
    <?php if (!empty($marketCap)): ?>
        <strong>Market Cap</strong>: <?php echo $marketCap; ?><br>
    <?php endif; ?>
</div>
<?php if (!empty($description)): ?>
    <div class="w-100 mt-2 order-md-3 stock-description-2"><?php echo View::escape($description); ?></div>
<?php endif; ?>
