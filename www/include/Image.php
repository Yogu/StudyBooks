<?php

/**
 * An image loaded and modified by the Graphics Draw library  
 */
class Image {
	/**
	 * @var resource
	 */
	public $resource;
	/**
	 * @var int
	 */
	public $width;
	/**
	 * @var int
	 */
	public $height;
	
	// ===========================================================================
	
	/**
	 * Creates a new GDImage using a resource
	 * 
	 * @param resource $resource the resource of the GDImage
	 */
	public function __construct($resource) {
		if (!is_resource($resource)) {
			$info = GetImageSize($resource);     
			$imageType = $info[2];
			$fileName = $resource;
			switch ($imageType) {
				case \IMAGETYPE_GIF:
					$resource = imagecreatefromgif($fileName);
					break;
					
				case \IMAGETYPE_JPEG:
				case \IMAGETYPE_JPEG2000:
					$resource = imagecreatefromjpeg($fileName);
					break;
					
				case \IMAGETYPE_PNG:
					$resource = imagecreatefrompng($fileName);
					break;
					
				case \IMAGETYPE_WBMP:
					$resource = imagecreatefromwbmp($fileName);
					break;
					
				case \IMAGETYPE_XBM:
					$resource = imagecreatefromwxpm($fileName);
					break;
			}
		}
		
		if (!is_resource($resource))
			throw new RuntimeException('Unable to create Image object from '.$fileName);
		
		$this->resource = $resource;
		imagesavealpha($this->resource, true);
		$this->width = imagesx($this->resource);
		$this->height = imagesy($this->resource);
	}
	
	// ===========================================================================
	
	/**
	 * Gets the width of this image in pixels
	 *
	 * @return int
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * Gets the height of this image in pixels
	 *
	 * @return int
	 */
	public function getHeight() {
		return $this->height;
	}       
	    
	/**
	 * Resizes to a exact size
	 *
	 * @param int $width the new width in pixels
	 * @param int $height the new height in pixels
	 * @param int $cropLeft the x-coordinate of the left-most pixel column to use
	 *   (in old scale)
	 * @param int $cropTop the y-coordinate of the top-most pixel row to use
	 *   (in old scale)
	 * @param int $cropWidth the count of pixel columns to use (in old scale)
	 * @param int $cropHeight the count of pixel rows to use (in old scale)
	 */
	public function resize($width, $height, $cropLeft = 0, $cropTop = 0,
		$cropWidth = -1, $cropHeight = -1)
	{
		$gdVersion = self::getGDVersion();
		
		if ($cropWidth < 0)
			$cropWidth = $this->width - $cropLeft;
		if ($cropHeight < 0)
			$cropHeight = $this->height - $cropHeight;
		
		if ($gdVersion >=2 ) {
			$resource = imagecreatetruecolor($width, $height);   
			imagealphablending($resource, false);
			$color = imagecolortransparent($resource,
				imagecolorallocatealpha($resource, 0, 0, 0, 127));
			imagefill($resource, 0, 0, $color);       
			imagesavealpha($resource, true);
		} else
			$resource = imagecreate($width, $height);

		if ($gdVersion >=2) 
			imagecopyresampled($resource, $this->resource, 0, 0, $cropLeft, $cropTop,
				$width, $height, $cropWidth, $cropHeight);
		else
			imagecopyresized($resource, $this->resource, 0, 0, $cropLeft, $cropTop,
				$width, $height, $cropWidth, $cropHeight);
		      
     imagedestroy($this->resource);
     $this->resource = $resource;
     $this->width = $width;
     $this->height = $height;
	}

	/**
	 * Makes sure that the image is not larger than the specified size
	 *
	 * Scales the image down, if it is larger than $width or $height.
	 *
	 * @param int $width the maximum width
	 * @param int $height the maximum height
	 */
	public function resizeProportionally($width, $height) {
		$newWidth = $this->width;
		$newHeight = $this->height;
		
		if ($width && $newWidth > $width) {
			$newHeight = $width * $newHeight / $newWidth;
			$newWidth = $width;
   		$resize = true;
		}

		if ($height && $newHeight > $height) {
			$newWidth = $height * $newWidth / $newHeight;
			$newHeight = $height;
   		$resize = true;
		}

		if ($resize) {
			$this->resize($newWidth, $newHeight);
			$this->width = $newWidth;
			$this->height = $newHeight;
		}
	}

	/**
	 * Scales the image down and crops its sides if the proportions do not match
	 *
	 * @param int $width the width
	 * @param int $height the height
	 */
	public function resizeAndCrop($width, $height) {
		if ($this->width > $this->height) { // landscape
			$scale = $height / $this->height; // scale for downsizing
			$cropWidth = $width / $scale; // scale up
			$cropHeight = $this->height;
		} else { // portrait
			$scale = $width / $this->width; // scale for downsizing
			$cropHeight = $height / $scale; // scale up
			$cropWidth = $this->width;
		}
		
		$cropLeft = ($this->width - $cropWidth) / 2;
		$cropTop = ($this->height - $cropHeight) / 2;
		
		$this->resize($width, $height, $cropLeft, $cropTop, $cropWidth, $cropHeight);
		$this->width = $newWidth;
		$this->height = $newHeight;
	}
	      
	/**
	 * Creates a file and writes this image into it
	 *
	 * @param string $fileName the path to the file to write in
	 * @param string $type one of 'image/png' or 'image/jpeg'
	 * @param int $quality on image/jpeg, the quality of the output file in percent
	 */
	public function saveToFile($fileName, $type = 'image/png', $quality = 95) {
		switch ($type) {
			case 'image/jpeg':
				ImageJPEG($this->resource, $fileName, $quality);
				break;
				
			default:
				ImagePNG($this->resource, $fileName);	
		}
	}

	// ===========================================================================
	
	private static function getGDVersion() {
		static $gdversion;
		if ($gdversion === null) {
			$ver_info = gd_info();
			preg_match('/\d/', $ver_info['GD Version'], $match);
			$gdversion = $match[0];
		}
		return $gdversion;
	}
}