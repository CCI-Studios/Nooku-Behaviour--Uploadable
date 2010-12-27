<?php

class ComTestDatabaseBehaviorUploadable extends KDatabaseBehaviorAbstract {
	
	protected $_location;
	protected $_column;
	protected $_thumbs;
	protected $_prefix;
	protected $_suffix;
	
	public function __construct(KConfig $config = null)
	{
		parent::__construct($config);
		
		foreach($config as $key=>$value) {
			$this->{'_'.$key} = $value;
		}
		
		/*echo "<pre>";
		echo htmlentities(print_r($this, 1));
		die;/**/
	}
	
	protected function _initialize(KConfig $config)
	{		
		$config->append(array(
			'location'	=>	'/media/'.
							$this->getIdentifier()->type.'_'.
							$this->getIdentifier()->package.'/uploads/',
			'column'	=> 'filename',
			'thumbs'	=> array(),
			'prefix'	=> '',
			'suffix'	=> '',						
		));
		
		parent::_initialize($config);
	}
	
	
	protected function _beforeTableInsert(KCommandContext $context)
	{
		return $this->_beforeTableUpdate($context);
	}
	
	protected function _beforeTableUpdate(KCommandContext $context)
	{
		echo "<pre>";
		$post = $context->data;
		$file = KRequest::get('FILES.'.$this->_column.'_upload', 'raw');
		
		if (isset($post->filename_delete) || $file->error != 4)
			$this->deleteCurrentImages();
		
		if (!isset($file)) {
			echo "no file\n";
			die;
			return;
		}
		
		echo htmlentities(print_r($file, 1));
		echo "</pre>";
		die;
	}
	
	
	protected function deleteCurrentImages() { echo "delete images\n"; }
	protected function deleteCurrentImage() { echo "delete image\n"; }
	protected function storeNewImage() { echo "store new image\n"; }
	protected function createThumbs() { echo "create thumbs\n"; }
	protected function createThumb() { echo "create thumb\n"; }
	
}