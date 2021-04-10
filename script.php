<?php

if (!is_dir(__DIR__ . '/convert')) {
	mkdir(__DIR__ . '/convert');
}
if (!is_dir(__DIR__ . '/converted')) {
	mkdir(__DIR__ . '/converted');
}

$dir = new FilesystemIterator(__DIR__ . '/convert');
$watermark = imagecreatefrompng('watermark2.png');
	
/*redução para 10% do tamanho original*/
$percent_reduct = 0.1; 

	foreach ($dir as $file) 
	{
		$nomeFile = $file->getFilename();
		$im = imagecreatefromjpeg(__DIR__ . '/convert/'.$nomeFile);

			$num = $file;

			list($width , $height , $type , $attr) = getimagesize($num);	
				$widthINT  = intval($width);
				$heightINT = intval($height);

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
				$watermarkScale = imagescale($watermark , $newWidthMask , $newHeightMask);
			
			/*posicionamento da marca d'agua no centro das imagens*/
				$posicionamento_width = $widthINT / 2 - $newWidthMask / 2;
				$posicionamento_height = $heightINT / 2 - $newHeightMask / 2;
			
			/*redução da imagem após a copia com marca d'agua*/
				imagecopy($im , $watermarkScale , $posicionamento_width , $posicionamento_height , 0 , 0 , $newWidthMask , $newHeightMask);

			/*redução da imagem após a copia com marca d'agua*/
				$im = imagescale($im, $width_reduct , $heigh_reduct);

		imagejpeg($im, __DIR__ . '/converted/'.$file->getFilename(), 100);
		imagedestroy($im);
		echo $file->getFilename() . ' ok'."\n\r";

		unset($num);
			
	}