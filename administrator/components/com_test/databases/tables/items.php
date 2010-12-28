<?php
defined('KOOWA') or die;

class ComTestDatabaseTableItems extends KDatabaseTableDefault {
	
	protected function _initialize(KConfig $config) {
		$uploadable	= KFactory::get('com.test.database.behavior.uploadable', array(
			'thumbs' => array(
				array('prefix' => 'large', 'suffix'=> '', 'width'=>420, 'height'=>450),
				array('prefix' => 'thumb', 'suffix'=> '', 'width'=>100, 'height'=>100),
			),
		));
		$config->behaviors = array($uploadable);
		parent::_initialize($config);
	}
}