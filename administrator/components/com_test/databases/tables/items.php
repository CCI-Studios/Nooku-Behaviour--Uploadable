<?php
defined('KOOWA') or die;

class ComTestDatabaseTableItems extends KDatabaseTableDefault {
	
	protected function _initialize(KConfig $config) {
		$config->behaviors = array('com.test.database.behavior.uploadable');
		parent::_initialize($config);
	}
}