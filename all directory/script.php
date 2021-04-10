<?php

date_default_timezone_set('America/Sao_Paulo');
$data =  date('d-m-Y-H-i-s');
$file_report = 'relatorios/relatorio-watermark-' . $data . '.txt';
$fp = fopen($file_report , "w+",0);

// Turn off all error reporting
error_reporting(0);

if (!is_dir(__DIR__ . '/convert')) {
	mkdir(__DIR__ . '/convert');
}
if (!is_dir(__DIR__ . '/converted')) {
	mkdir(__DIR__ . '/converted');
}

$watermark = imagecreatefrompng('watermark.png');

/*redução para 10% do tamanho original*/
$percent_reduct = 0.1; 
$listFiles = [];
$root = __DIR__ . '/convert';

function recursiveListDir($root){
	$iter = new RecursiveIteratorIterator(
	    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
	    RecursiveIteratorIterator::SELF_FIRST,
	    RecursiveIteratorIterator::CATCH_GET_CHILD 
	);

	$paths = array($root);
	foreach ($iter as $path => $dir) {
	    if ($dir->isDir()) {
	        $paths[] = $path;
	    }
	}

	$array = [];
	foreach ($paths as $path) {
		$actualDir = new FilesystemIterator($path);
		

		foreach ($actualDir as $file) { 
			$array[] = [
				'type' 	=> $file->getType(),
				'name'	=> $file->getFilename(),
				'path'	=> $path,
			];
		}		
	}

	return $array;
};

function resizeImgByURL($path, $nameFile, $watermark, $percent_reduct) {

$extension_file = new SplFileInfo($nameFile);
$extension_return = ($extension_file->getExtension());

	list($width, $height, $type, $attr) = getimagesize($path.DIRECTORY_SEPARATOR.$nameFile);	
		$widthINT  = intval($width);
		$heightINT = intval($height);

	switch ($extension_return) {
		case 'JPG':
			$callback_extension = imagecreatefromjpeg ($path.DIRECTORY_SEPARATOR.$nameFile);
		break;

		case 'jpg':
			$callback_extension = imagecreatefromjpeg ($path.DIRECTORY_SEPARATOR.$nameFile);
		break;

		case 'jpeg':
			$callback_extension = imagecreatefromjpeg ($path.DIRECTORY_SEPARATOR.$nameFile);
		break;

		case 'png':
			$callback_extension = imagecreatefrompng ($path.DIRECTORY_SEPARATOR.$nameFile);
		break;

		case 'gif':
			$callback_extension = imagecreatefromgif ($path.DIRECTORY_SEPARATOR.$nameFile);
		break;

	}

	echo $extension_return;
	$im = $callback_extension;
	
	/*calculo redução percentual*/	
	$width_reduct = $widthINT * $percent_reduct;
	$heigh_reduct = $heightINT * $percent_reduct;

	$watermarkX = imagesx($watermark);
	$watermarkY = imagesy($watermark);

	/*tamanho da marca d'agua proporcional a width da imagem*/
	$pixelMask = 65.0 / 100.0;
	$newWidthMask 	= 	$widthINT - ($widthINT * $pixelMask);
	$newHeightMask  = 	$newWidthMask;

	/*criação da marca d'agua com proporção a imagem*/
	$watermarkScale = imagescale($watermark, $newWidthMask, $newHeightMask);

	/*posicionamento da marca d'agua no centro das imagens*/
	$posicionamento_width = $widthINT / 2 - $newWidthMask / 2;
	$posicionamento_height = $heightINT / 2 - $newHeightMask / 2;

	/*redução da imagem após a copia com marca d'agua*/
	imagecopy($im, $watermarkScale, $posicionamento_width , $posicionamento_height , 0 , 0 , $newWidthMask, $newHeightMask);

	/*redução da imagem após a copia com marca d'agua*/
	$im = imagescale($im, $width_reduct, $heigh_reduct);

	$newPath = str_replace('convert', 'converted', $path);

	switch ($extension_return) {
		case 'JPG':
		imagejpeg ($im, $newPath.DIRECTORY_SEPARATOR.$nameFile);
		break;

		case 'jpg':
		imagejpeg ($im, $newPath.DIRECTORY_SEPARATOR.$nameFile);
		break;

		case 'jpeg':
		imagejpeg ($im, $newPath.DIRECTORY_SEPARATOR.$nameFile);
		break;

		case 'png':
		imagepng ($im, $newPath.DIRECTORY_SEPARATOR.$nameFile);
		break;

		case 'gif':
		imagegif ($im, $newPath.DIRECTORY_SEPARATOR.$nameFile);
		break;

	}

	imagedestroy($im);
	return true;
}
foreach (recursiveListDir($root) as $item) {
	// var_dump($dir);

	switch ($item['type']) {
	
		case 'file':
			
			if (!resizeImgByURL($item['path'], $item['name'], $watermark, $percent_reduct)) {
				echo $item['path'].DIRECTORY_SEPARATOR.$item['name'].' houve um erro'."\r\n";
				$info_report = "\r\n".$item['name'].' houve um erro';
				
			continue;
			}

			echo $item['path'].DIRECTORY_SEPARATOR.$item['name'].' convertido'."\r\n"; 
			$info_report = "\r\n  --- thumbnail ".$item['name'].' criada com sucesso'; 
			break;

		case 'dir':
			$item['path'] = str_replace('convert', 'converted', $item['path']);
			if (!is_dir($item['path'].DIRECTORY_SEPARATOR.$item['name'])) {
				mkdir($item['path'].DIRECTORY_SEPARATOR.$item['name']);
			}
			echo $item['path'].DIRECTORY_SEPARATOR.$item['name'].' criado com sucesso'."\r\n";
			$info_report = "\r\n  diretório ".$item['name'].' criado com sucesso';
			break;
		
		default:
		
			echo $item['path'].DIRECTORY_SEPARATOR.$item['name'].' sem ação a ser tomada'."\r\n";
			break;
	}
	
	echo fwrite($fp,  $info_report);
}
fclose($fp);
