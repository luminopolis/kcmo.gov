<?php

class SampleTest extends WP_UnitTestCase {
	public function testGetPermalink(){
		$this->expectOutputString( 'http://example.org' );
		print dynamic_nav_parse( 'wp://get_home_url' );
	}
	
	public function testParseFail(){
		$this->expectOutputString( 'http://fail/' );
		print dynamic_nav_parse( 'wp://fail' );
	}
}

