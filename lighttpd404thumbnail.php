<?php

class CLighttpd404Thumbnail
{
	public $BASE_PATH = "/shared/uesp/wikiimages";
	public $OUTPUT_EXTRA_INFO = false;
	
	
	public function escape($str)
	{
		return htmlspecialchars($str);
	}
	
	
	public function outputDefault404()
	{
		$origLink = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$origUri = $_SERVER['REQUEST_URI'];
		
		http_response_code(404);
		
		print("<h1>404 Not Found</h1>\n");
		
		if ($this->OUTPUT_EXTRA_INFO)
		{
			print($this->escape($origLink) . "\n");
		
			$date = date('m/d/Y h:i:s a', time());
			print("<br/>Time: $date\n");
		}
		
		return false;
	}
	
	
	public function isValidExtension($ext)
	{
		$ext = strtolower($ext);
		
		if ($ext == "jpg") return true;
		if ($ext == "jpeg") return true;
		if ($ext == "gif") return true;
		if ($ext == "png") return true;
		if ($ext == "svg") return true;
		if ($ext == "pcx") return true;
		if ($ext == "tga") return true;
		if ($ext == "apng") return true;
		if ($ext == "webp") return true;
		if ($ext == "ogg") return true;
		
		return false;
	}
	
	
	public function outputImage($imageFile, $imageSize, $baseImageFile)
	{
		$img = new Imagick($imageFile);
		
		header('Content-Type: image/' . $img->getImageFormat());
		
		if ($imageSize <= 0 || $img->getImageHeight() <= $imageSize)
		{
			echo $img;
		}
		else
		{
			$width = $img->getImageWidth();
			$height = $img->getImageHeight();
			
			$newWidth = floor($width * $imageSize / $height);
			$newHeight = $imageSize;
			
			$img->scaleImage($newWidth, $newHeight);
			
			//error_log("Resized Image to: $newWidth x $newHeight");
			
			echo $img;
			
			$imageName = preg_replace('#/[0-9a-fA-F]+/[0-9a-fA-F]+/#', '', $baseImageFile); 
			$newThumbFile = $this->BASE_PATH . "/thumb" . $baseImageFile . "/" . $newHeight . "px-" . $imageName;
			
			//error_log("New Thumb: $newThumbFile");
			
			if (!file_exists($newThumbFile))
			{
				$img->writeImage($newThumbFile);
			}
		}
		
		return true;
	}
	
	
	public function handleThumbnail($thumbPath)
	{
		$imageFile = preg_replace('#^/thumb#', '', $thumbPath);
		$imageFile = preg_replace('#/[^/]*$#', '', $imageFile);
		$baseImageFile = $imageFile;
		$imageFile = $this->BASE_PATH . $imageFile;
		
		if (!file_exists($imageFile)) return $this->outputDefault404();
		
		$extMatched = preg_match('#\.([^.]+)$#', $imageFile, $extMatches);
		
		if (!$extMatched || $extMatches == null || $extMatches[1] == null) return $this->outputDefault404();
		if (!$this->isValidExtension($extMatches[1])) return $this->outputDefault404();
		
		$imageSize = 0;
		$sizeMatched = preg_match('#/([0-9]+)px-[^/]+$#', $thumbPath, $sizeMatches);
		
		if ($sizeMatched && $sizeMatches && $sizeMatches[1])
		{
			$imageSize = intval($sizeMatches[1]);
			if ($imageSize < 0) $imageSize = 0;
		}
		
		//$this->outputDefault404();
		//print("<br/>Handling thumbnail: $imageFile at size $imageSize px\n");
		
		return $this->outputImage($imageFile, $imageSize, $baseImageFile);
	}
	
	
	public function handle404()
	{
		$origLink = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$origUri = $_SERVER['REQUEST_URI'];
		
		if (!preg_match('#^/thumb/#', $origUri)) return $this->outputDefault404();
		
		$this->handleThumbnail($origUri);
		
		return true;
	}
	
};

$thumbnail = new CLighttpd404Thumbnail();
$thumbnail->handle404();



