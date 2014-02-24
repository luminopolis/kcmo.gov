<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>Problem Reports</h2>
	
	<table class="wp-list-table widefat">
		<thead>
			<tr>
				<th>Date Reported</th>
				<th>URL</th>
				<th>Activity</th>
				<th>Issue</th>
				<th>Resolved</th>
			</tr>
		</thead>
		
		<tbody>
			<?php foreach( $problems as $k=>$row ): ?>
			<tr class="<?php echo $k % 2 ? 'alternate' : ''; ?>">
				<td><?php echo $row->report_date; ?></td>
				<td><a href="<?php echo $row->URL; ?>" target="_blank"><?php echo esc_html( $row->URL ); ?></a></td>
				<td><?php echo esc_html( wp_trim_words($row->activity, 20) ); ?></td>
				<td><?php echo esc_html( wp_trim_words($row->issue, 20) ); ?></td>
				<td><a href="<?php echo add_query_arg( array('key_id' => $row->key_id) ); ?>"><?php echo $row->resolution_date ? $row->resolution_date : 'No' ; ?></a></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

</div>