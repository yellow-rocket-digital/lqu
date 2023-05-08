<?php
namespace TheLion\ShareoneDrive;

$loaders = Core::get_setting('loaders');
?>
<div id='ShareoneDrive'>
    <div class='ShareoneDrive list-container noaccess'>
        <div style="max-width:512px; margin: 0 auto; text-align:center;">
            <img src="<?php echo $loaders['protected']; ?>" data-src-retina="<?php echo $loaders['protected']; ?>" style="display:inline-block">
            <?php echo Core::get_setting('userfolder_noaccess'); ?>
        </div>
    </div>
</div>