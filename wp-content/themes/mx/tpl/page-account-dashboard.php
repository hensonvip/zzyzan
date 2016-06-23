<div class="row">
	<?php 
	$dashboards = array(
		'left' => 'g-tablet-1-3',
		'right' => 'g-tablet-2-3',
		
	);
	foreach($dashboards as $k => $v){ ?>
		<div class="account-dashboard account-dashboard-<?= $k;?> <?= $v;?>">
			<?php do_action("account_dashboard_{$k}");?>
		</div>
	<?php } ?>
</div>