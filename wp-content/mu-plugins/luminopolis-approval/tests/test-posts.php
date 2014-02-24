<?php

class PostsTest extends WP_UnitTestCase {
	
	/*
	*	creates a post as an author, and is saved as pending
	*	checks publish status and taxonomies
	*/
	function test_creation() {
		$author_id = $this->factory->user->create( array(
			'role' => 'author'	
		) );
		$this->assertTrue( is_numeric($author_id) );
		
		wp_set_current_user( $author_id );
		
		$post_id = wp_insert_post( array(
			'post_author' => $author_id,
			'post_type' => 'page',
			'tax_input' => array(
				'category' => array('Unit Tests', 'Approval', 'Plugins'),
				'post_tag' => 'tag_1, tag_2'
			)
		), TRUE );
		$this->assertTrue( is_numeric($post_id), "Is not numeric: $post_id" );
		$this->assertTrue( $post_id > 0, "Is not greater than zero: $post_id" );
			
		$post = get_post( $post_id );
		$this->assertInstanceOf( 'WP_Post', $post );
		$this->assertEquals( $post_id, $post->ID );
		$this->assertEquals( 'pending', $post->post_status );
	}
	
	/*
	*
	*/
	function test_rejection(){
		
	}
	
	/*
	*
	*/
	function test_accept_level_01(){
	
	}
	
	/*
	*
	*/
	function test_accept_level_02(){
	
	}
	
	/*
	*
	*/
	function test_resubmittion(){
	
	}
}

