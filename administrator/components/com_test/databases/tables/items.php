<?php
defined('KOOWA') or die;

class ComTestDatabaseTableItems extends KDatabaseTableDefault {
	
	protected function _initialize(KConfig $config) {
		$uploadable	= KDatabaseBehavior::factory('uploadable', array(
			'location'=>'/media/com_test/uploads/',
			'thumbs' => array(
				array('prefix' => 't', 'width' => 400, 'height' => 400),
				array('prefix' => 'p', 'width' => 100, 'height' => 100)
			)
		));
		$config->behaviors = array($uploadable);
		parent::_initialize($config);
	}
}