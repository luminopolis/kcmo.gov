<div class="approval-history-wrap">
	<?php foreach( $approvals as $k=>$v ): ?>
		<div class="approval-history">
			<p><?php echo luminopolis_approval\display_date_and_name( $v ); ?></p>
		</div>
	<?php endforeach; ?>
</div>

<br style="clear:both"/>