<?php
defined('KOOWA') or die('Restricted Access');

echo KFactory::get('admin::com.test.dispatcher')
	->dispatch(KRequest::get('get.view', 'cmd', 'items'));
