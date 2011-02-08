<?php
/**
 * @version		$Id $
 * @category	Koowa
 * @package 	Koowa_Database
 * @subpackage 	Behavior
 * @copyright	Copyright (C) 2010-2011 CCI Studios. All rights reserved.
 * @license		GNU GPLv3 <http://www.gnu.org/license/gpl.html>
 * @link		http://ccistudis.com
 *
 */
class KDatabaseBehaviorUploadable extends KDatabaseBehaviorAbstract {
	
	protected $_location;
	protected $_uploads;
	protected $_filter;
	protected $_thumbs;
	protected $_fieldname;
	protected $_memory_limit;
	
	public function __construct(KConfig $config = null)
	{
		jimport('joomla.filesystem.file'); // replace with nooku file options
		parent::__construct($config);
		
		foreach($config as $key=>$value) {
			$this->{'_'.$key} = $value;
		}
	}
	
	protected function _initialize(KConfig $config)
	{			
		$config->append(array(
			'location'	=> 'media/uploads/',
			'filter'	=> '/jpg|gif|png/',
			'fieldname'	=> 'filename',
			'thumbs'	=> array(),
			
			'memory_limit'	=> '64M'
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
		
		// no file to save
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
		$name  = (isset($thumb['prefix']) && $thumb['prefix'] !== '')? "{$thumb['prefix']}_": "";
		$name .= "{$filename}";
		$name .= (isset($thumb['suffix']) && $thumb['suffix'] !== '')? "_{$thumb['suffix']}" : "";
		return $name;
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
		ini_set('memory_limit', $this->_memory_limit);
		list($src_width, $src_height, $src_type) = getimagesize($this->imagePath().$filename.$extension);
		
		$original = new stdClass();
		$original->width	= $src_width;
		$original->height	= $src_height;
		$original->type		= $src_type;
		$original->ratio	= $src_width/$src_height;
		
		// get the fullsize image
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
		$width	= $thumbinfo->width;
		$height	= $thumbinfo->height;
		$name	= $this->thumbName($filename, $thumbinfo);
		$ratio	= $width/$height;
		
		// fill dimensions, crop as needed
		if ($original->ratio >= 1) {
			$temp_height 	= $height;
			$temp_width		= (int)($height * $original->ratio);
		} else {
			$temp_height	= (int)($width / $original->ratio);
			$temp_width		= $width;
		}
		
		// temp_image is larger than the requested dimensions
		$temp_image = imagecreatetruecolor($temp_width, $temp_height);
		imagecopyresampled($temp_image, $original->image,
							0,0,0,0,
							$temp_width, $temp_height,
							$original->width, $original->height);
		
		// crop to fit output dimensions
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
	}
	
}