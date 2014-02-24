<ul class="men sortable-list" data-sortable='{"items":"li","id":"footer_side"}'>
	<?php
	switch_to_blog( 1 );
	
	add_filter( 'wp_list_bookmarks', 'kcmo_footer_links_callout_colors' );
	add_filter( 'get_bookmarks', 'kcmo_footer_links_callout_targets', 10, 2 );
	
	$category_name = 'Footer Callout';
	$category = get_term_by( 'name', $category_name, 'link_category' );
	
	kcmo_menu_links( $category_name );
	
	remove_filter( 'wp_list_bookmarks', 'kcmo_footer_links_callout_colors' );
	remove_filter( 'get_bookmarks', 'kcmo_footer_links_callout_targets', 10, 2 );
	
	restore_current_blog();
	?>
</ul>

<?php if( current_user_can('manage_links') ): ?>
	<a class="edit_links" href="<?php echo $admin_url_main; ?>link-manager.php?cat_id=<?php echo $category->term_id; ?>">Manage Side Links</a>
<?php endif; ?>