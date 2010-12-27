<?php
jimport('joomla.filesystem.file');

class ComTestDatabaseBehaviorUploadable extends KDatabaseBehaviorAbstract {
	
	protected $_location;
	//protected $_column;
	protected $_thumbs;
	protected $_prefix;
	protected $_suffix;
	protected $_filter;
	
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
			'location'	=>	'media/'.
							$this->getIdentifier()->type.'_'.
							$this->getIdentifier()->package.'/uploads/',
			//'column'	=> 'filename',
			'thumbs'	=> array(array(100, 200)),
			'prefix'	=> 'pre',
			'suffix'	=> 'suf',
			'filter'	=> '/jpg|gif|png/'
		));
		
		parent::_initialize($config);
	}
	
	
	protected function _beforeTableInsert(KCommandContext $context)
	{
		return $this->_beforeTableUpdate($context);
	}
	
	protected function _beforeTableUpdate(KCommandContext $context)
	{
		$post = $context->data;
		$file = KRequest::get('FILES.filename_upload', 'raw');
		
		// cancel if there is an error and there is a file
		if ($file['error'] !== 0 && $file['error'] !== 4) {
			JError::raiseWarning('300', 'Error uploading image.');
			return false;
		}
		
		// delete images if requested or if a new file is uploaded
		if (isset($post->filename_delete) || $file['error'] != 4) {
			$this->deleteAllImages($post->filename);
			$post->filename = null;
		}
		
		if ($file['error'] === 4)
			return true;
		
		list($filename, $extension) = $this->storeNewImage($file);
		if ($filename === false) {
			JError::raiseWarning('300', 'Error moving image into media folder');
			return false;
		}
		$this->createThumbs($filename, $extension);
		$post->filename = $filename.$extension;
		
		return true;
	}
	
	protected function thumbName($filename, $thumb) {
		return	"{$this->_prefix}_".
				"{$filename}_".
				"{$this->_suffix}_".
				"$thumb[0]x$thumb[1]";
	}
	
	protected function imagePath()
	{
		return JPATH_SITE.'/'.$this->_location;
	}
	
	
	protected function deleteAllImages($filename)
	{
		$this->deleteImage($filename);
		
		$ext	= JFile::getExt($filename);
		$file	= substr($filename, 0, -strlen($ext)-1);
		foreach($this->_thumbs as $thumb) {
			$this->deleteImage($this->thumbName($file, $thumb).'.'.$ext);
		}
	}
	
	protected function deleteImage($name)
	{
		if (JFile::exists($this->imagePath().$name)) {
			JFile::delete($this->imagePath().$name);
		}
	}
	
	protected function storeNewImage($fileinfo)
	{
		$extension 	= JFile::getExt($fileinfo['name']);
		$src		= $fileinfo['tmp_name'];
		
		do {
			$dest = time().rand(0,100);
		} while (JFile::exists($this->imagePath().$dest.'.'.$extension));
		
		if (!JFile::upload($src, $this->imagePath().$dest.'.'.$extension)) {
			return false;
		}
		return array($dest,'.'.$extension);
	}
	
	protected function createThumbs($filename, $extension)
	{
		ini_set('memory_limit', '64M');
		list($src_width, $src_height, $src_type) = getimagesize($this->imagePath().$filename.$extension);
		
		$original = new stdClass();
		$original->width	= $src_width;
		$original->height	= $src_height;
		$original->type		= $src_type;
		$original->ratio	= $src_width/$src_height;
		
		$fullpath = $this->imagePath().$filename.$extension;
		switch($original->type) {
			case IMAGETYPE_GIF:
				$original->image = imagecreatefromgif($fullpath);
				break;
			case IMAGETYPE_PNG:
				$original->image = imagecreatefrompng($fullpath);
				break;
			case IMAGETYPE_JPEG:
				$original->image = imagecreatefromjpeg($fullpath);
				break;
		}
			
		foreach ($this->_thumbs as $thumb) {
			$this->createThumb($filename, $extension, $thumb, $original);
		}
		
		imagedestroy($original->image);
		$original = null;
	}
	
	protected function createThumb($filename, $extension, $thumbinfo, &$original)
	{
		$width	= $thumbinfo[0];
		$height	= $thumbinfo[1];
		$name	= $this->thumbName($filename, $thumbinfo);
		$ratio	= $width/$height;
		
		if ($original->ratio >= 1) {
			$temp_height 	= $height;
			$temp_width		= (int)($height * $original->ratio);
		} else {
			$temp_height	= (int)($original->width / $original->ratio);
			$temp_width		= $width;
		}
		
		$temp_image = imagecreatetruecolor($temp_width, $temp_height);
		imagecopyresampled($temp_image, $original->image,
							0,0,0,0,
							$temp_width, $temp_height,
							$original->width, $original->height);
		
		$final_image = imagecreatetruecolor($width, $height);
		imagecopy($final_image, $temp_image, 0,0, 
					($temp_width-$width)/2, ($temp_height-$height)/2,
					$width, $height);
		imagedestroy($temp_image);
		
		$fullpath = $this->imagePath().$this->thumbName($filename, $thumbinfo).$extension;
		switch ($original->type) {
			case IMAGETYPE_GIF:
				imageGif($final_image, $fullpath);
				break;
			case IMAGETYPE_PNG:
				imagePng($final_image, $fullpath);
				break;
			case IMAGETYPE_JPEG:
				imageJpeg($final_image, $fullpath);
				break;
		}
		
		imagedestroy($final_image);
		echo $fullpath."\n";
	}
	
}