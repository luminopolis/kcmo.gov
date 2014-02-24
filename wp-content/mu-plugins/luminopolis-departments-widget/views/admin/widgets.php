<div class="kcmo_dept">
	<p>
		<h4><?php _e( 'Department Info', 'departments-widget' ); ?></h4>
		
		<label>Title</label><br/>
		<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>"/>
	</p>
	
	<p>
		<label>Contact</label><br/>
		<textarea name="<?php echo $this->get_field_name( 'contact' ); ?>"><?php echo esc_attr( $contact ); ?></textarea>
	</p>
	
	<p>
		<h4><?php _e( 'Social Networks', 'departments-widget' ); ?></h4>
	</p>
	
	<ul class="social_networks">
		<?php foreach( $social as $k=>$v ): ?>
		<li>
			<h5>
				<img src="<?php echo esc_attr( $v['img'] ); ?>"/>
				<span class="title"><?php echo esc_attr( $v['title'] ); ?></span>
				<input name="<?php echo $this->get_field_name( 'social' ); ?>[<?php echo esc_attr( $v['id'] ); ?>][order]" type="hidden" value="<?php echo $k; ?>"/>
				&nbsp;
				
				<span class="action">
					<a class="edit">edit</a>
					&nbsp;|&nbsp;
					<a class="delete">delete</a>
				</span>
			</h5>
			
			<div class="<?php if( trim($v['title']) ) echo 'hide-if-js'; ?>">
				<label class="social">Icon:</label>
				<input class="social" type="text" name="<?php echo $this->get_field_name( 'social' ); ?>[<?php echo esc_attr( $v['id'] ); ?>][img]" value="<?php echo esc_attr( $v['img'] ); ?>"/>
				
				<label class="social">Title:</label>
				<input class="social" type="text" name="<?php echo $this->get_field_name( 'social' ); ?>[<?php echo esc_attr( $v['id'] ); ?>][title]" value="<?php echo esc_attr( $v['title'] ); ?>"/>
			</div>
			
			<label class="social">URL:</label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'social' ); ?>[<?php echo esc_attr( $v['id'] ); ?>][href]" value="<?php echo esc_attr( $v['href'] ); ?>"/>
			
			<label class="social">Alt:</label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'social' ); ?>[<?php echo esc_attr( $v['id'] ); ?>][alt]" value="<?php echo esc_attr( $v['alt'] ); ?>"/>
		</li>
		<?php endforeach; ?>
	</ul>
	
	<a class="addnew" href="#"><?php _e( 'Add Social Network', 'departments-widget' ); ?></a>
</div>