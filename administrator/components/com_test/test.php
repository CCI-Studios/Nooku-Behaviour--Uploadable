<?php

echo KFactory::get('admin::com.test.dispatcher')
	->dispatch(KRequest::get('get.view', 'cmd', 'items'));