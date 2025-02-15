<?php
/* @var float $rate*/
?>
<?php for ($i = 0; $i < 5; $i++): ?>
    <?php if ($i < floor($rate)): ?>
        <iconify-icon class="pl-1" icon="mdi:star" style="color: #fe8c00"></iconify-icon>
    <?php elseif ((floor($rate) != $rate) && (floor($rate) == $i)): ?>
        <iconify-icon class="" icon="mdi:star-half" style="color: #fe8c00"></iconify-icon>
    <?php else: ?>
        <iconify-icon class="color-gray" icon="mdi:star-outline"></iconify-icon>
    <?php endif; ?>
<?php endfor; ?>