<?php

/*!
 *  Elberos Framework
 *
 *  (c) Copyright 2016-2020 "Ildar Bikmamatov" <support@elberos.org>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      https://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */
 
namespace Elberos;


class Image
{
	protected $image = null;
	protected $image_type = null;
	protected $width = 0;
	protected $height = 0;
	protected $next_destroy = true;
	
	function setImage($image, $image_type = null)
	{
		$this->image = $image;
		$this->image_type = $image_type;
		$this->width = 0;
		$this->height = 0;
		
		if ($image == null)
			return;
		
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
		$this->next_destroy = true;
	}
	
	function getImage()
	{
		return $this->image;
	}
	
	function getImageType()
	{
		return $this->image_type;
	}
	
	function getWidth()
	{
		return $this->width;
	}
	
	function getHeight()
	{
		return $this->height;
	}
	
	function isLoaded()
	{
		return $this->image != null;
	}
	
	function open($file_name)
	{
		$info = getimagesize($file_name);
		
		$mime = $info['mime'];
		$image = null;
		$image_type = null;
		
		if ($mime == 'image/jpeg')
		{
			$image = imagecreatefromjpeg($file_name);
			$image_type = IMAGETYPE_JPEG;
		}
		elseif ($mime == 'image/gif') 
		{
			$image = imagecreatefromgif($file_name);
			$image_type = IMAGETYPE_GIF;
		}
		elseif ($mime == 'image/png') 
		{
			$image = imagecreatefrompng($file_name);
			$image_type = IMAGETYPE_PNG;
		}
		
		if ($image)
		{
			$this->setImage($image, $image_type);
			return true;
		}
		
		$this->setImage(null);
		return false;
	}
	
	function destroy()
	{
		if ($this->next_destroy) imagedestroy($this->image);
		$this->setImage(null);
	}
	
	function save($filename, $image_type=null, $compression=75)
	{
		if ($image_type == null) $image_type = $this->image_type;
		if ($image_type == IMAGETYPE_JPEG)
		{
			imagejpeg($this->image, $filename, $compression);
		}
		elseif ($image_type == IMAGETYPE_GIF)
		{
			imagegif($this->image, $filename);
		}
		elseif ($image_type == IMAGETYPE_PNG)
		{
			imagealphablending($this->image, false);
			imagesavealpha($this->image, true);
			imagepng($this->image, $filename);
		}
	}
	
	function getImageTypeByExt($ext, $def=IMAGETYPE_JPEG)
	{
		$ext = mb_strtolower($ext);
		if ($ext == 'jpg' || $ext == 'jpeg') return IMAGETYPE_JPEG;
		if ($ext == 'gif') return IMAGETYPE_GIF;
		if ($ext == 'png') return IMAGETYPE_PNG;
		return $def;
	}
	
	
	/**
	 * Copy image as link
	 * 
	 * @result Image
	 */
	function fastCopy()
	{
		$class_name = static::class;
		$obj = new $class_name();
		$obj->setImage($this->image, $this->image_type);
		$obj->next_destroy = false;
		return $obj;
	}
	
	
	
	/**
	 * Copy part of image
	 * 
	 * @param int $x
	 * @param int $y
	 * @param int $w
	 * @param int $h
	 * @result Image
	 */
	function copy($x=0, $y=0, $w=-1, $h=-1, $color=null)
	{
		
		if ($w == -1) $w = $this->getWidth();
		if ($h == -1) $h = $this->getHeight();
		
		$new_width = $w - $x;
		$new_height = $h - $y;
		
		$new_image = imagecreatetruecolor($new_width, $new_height);
		
		if ($color == null)
		{
			$color = imagecolorallocate($new_image, 255, 255, 255);
		}
		imagefill ($new_image, 0, 0, $color);
		
		imagecopyresampled(
			$new_image, 
			$this->image, 
			0, 0, 
			$x, $y,
			$new_width, $new_height,
			$w, $h
		);
		
		$class_name = static::class;
		$obj = new $class_name();
		$obj->setImage($new_image, $this->image_type);
		
		return $obj;
	}
	
	
	
	/**
	 * Crop image
	 * 
	 * @param int $new_width
	 * @param int $new_height
	 * @param array $rect_src
	 * @param array $rect_dest
	 * @param Color $color
	 * @result Image
	 */
	function crop($new_width, $new_height, $rect_src = null, $rect_dest = null, $color=null)
	{
		$new_image = imagecreatetruecolor($new_width, $new_height);
		
		if ($color == null)
			$color = imagecolorallocate($new_image, 255, 255, 255);
		
		imagefill ($new_image, 0, 0, $color);
		
		if ($rect_src == null){
			$rect_src = [0, 0, $this->width, $this->height];
		}
		
		if ($rect_dest == null){
			$rect_dest = [0, 0, $new_width, $new_height];
		}
		
		$src_x = $rect_src[0];
		$src_y = $rect_src[1];
		$src_w = $rect_src[2];
		$src_h = $rect_src[3];
		
		$dest_x = $rect_dest[0];
		$dest_y = $rect_dest[1];
		$dest_w = $rect_dest[2];
		$dest_h = $rect_dest[3];
		
		imagecopyresampled
		(
			$new_image, 
			$this->image, 
			$dest_x, $dest_y, 
			$src_x, $src_y,
			$dest_w, $dest_h,
			$src_w, $src_h
		);
		
		$image_type = $this->image_type;
		$this->destroy();
		$this->setImage($new_image, $image_type);
		
		return true;
	}
	
	
	
	/**
	 * Scale to new image
	 * 
	 * @param int $scale
	 * @result Image
	 */
	function scale($scale)
	{
		$width = $this->getWidth();
		$height = $this->getHeight();
		
		$new_width = $width * $scale;
		$new_height = $height * $scale;
		
		$rect_src = [0, 0, $width, $height];
		$rect_dest = [0, 0, $new_width, $new_height];
		
		return $this->crop($new_width, $new_height, $rect_src, $rect_dest);
	}
	
	
	
	/**
	 * Resize to height
	 *
	 * @param int $new_width
	 * @param int $new_height
	 * @result Image
	 */
	function resizeToHeight($new_width = -1, $new_height = -1, $pos_x = "center", $scale = true)
	{
		$width = $this->getWidth();
		$height = $this->getHeight();
		
		$ratio = $new_height / $height;
		$w = $width * $ratio;
		
		if (!$scale && $ratio > 1)
		{
			$new_height = $height;
			$new_width = $width;
			$w = $width;
		}
		
		if ($new_width == -1) $new_width = $w;
		
		$start_w = 0;
		if ($pos_x == "center") $start_w = ($new_width - $w) / 2;
		else if ($pos_x == "right") $start_w = $new_width - $w;
		
		$rect_src = [0, 0, $width, $height];
		$rect_dest = [$start_w, 0, $w, $new_height];
		
		if ($new_width > 0 && $new_height > 0)
			return $this->crop($new_width, $new_height, $rect_src, $rect_dest);
		
		return false;
	}
	
	
	
	/**
	 * Resize to width
	 *
	 * @param int $new_width
	 * @param int $new_height
	 * @result Image
	 */
	function resizeToWidth($new_width = -1, $new_height = -1, $pos_y = "center", $scale = true)
	{
		$width = $this->getWidth();
		$height = $this->getHeight();
		
		$ratio = $new_width / $width;
		$h = $height * $ratio;
		
		if (!$scale && $ratio > 1)
		{
			$new_width = $width;
			$new_height = $height;
			$h = $height;
		}
		
		if ($new_height == -1) $new_height = $h;
		
		$start_h = 0;
		if ($pos_y == "center") $start_h = ($new_height - $h) / 2;
		else if ($pos_y == "bottom") $start_h = $new_height - $h;
		
		$rect_src = [0, 0, $width, $height];
		$rect_dest = [0, $start_h, $new_width, $h];
		
		if ($new_width > 0 && $new_height > 0)
			return $this->crop($new_width, $new_height, $rect_src, $rect_dest);
		
		return false;
	}
	
	
	
	/**
	 * Resize cover
	 *
	 * @param int $new_width
	 * @param int $new_height
	 * @result Image
	 */
	function resizeCover($new_width, $new_height, $pos_x = "center", $pos_y = "center", $scale = true)
	{
		$width = $this->getWidth();
		$height = $this->getHeight();
		
		if ($new_width / $new_height > $width / $height)
		{
			$this->resizeToWidth($new_width, $new_height, $pos_y, $scale);
		}
		else
		{
			$this->resizeToHeight($new_width, $new_height, $pos_x, $scale);
		}
	}
	
	
	
	/**
	 * Resize cover
	 *
	 * @param int $new_width
	 * @param int $new_height
	 * @result Image
	 */
	function resizeContain($new_width, $new_height, $pos_x = "center", $pos_y = "center", $scale = true)
	{
		$width = $this->getWidth();
		$height = $this->getHeight();
		
		if ($new_width / $new_height < $width / $height)
		{
			$this->resizeToWidth($new_width, $new_height, $pos_y, $scale);
		}
		else
		{
			$this->resizeToHeight($new_width, $new_height, $pos_x, $scale);
		}
	}
	
	
	
	/**
	 * Scale to
	 *
	 * @param int $max_width
	 * @param int $max_height
	 * @result Image
	 */
	function scaleTo($max_width, $max_height)
	{
		$width = $this->getWidth();
		$height = $this->getHeight();
		
		if ($max_width / $max_height < $width / $height)
		{
			$new_height = $height * $max_width / $width;
			$this->resizeToWidth($max_width, $new_height, "center", false);
		}
		else
		{
			$new_width = $width * $max_height / $height;
			$this->resizeToWidth($new_width, $max_height, "center", false);
		}
	}
	
}