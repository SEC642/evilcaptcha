<?php
error_reporting('E_ALL');
require('utils.php');

function produceimage($text) {
    // constant values
    $backgroundSizeX = 1000;
    $backgroundSizeY = 350;
    $sizeX = 200;
    $sizeY = 50;
    $fontFile = "captcha/verdana.ttf";
    $textLength = strlen($text);

    // generate random security values
    $backgroundOffsetX = rand(0, $backgroundSizeX - $sizeX - 1);
    $backgroundOffsetY = rand(0, $backgroundSizeY - $sizeY - 1);
    $angle = rand(-5, 5);
    #$fontColorR = rand(0, 127);
    #$fontColorG = rand(0, 127);
    #$fontColorB = rand(0, 127);
    $fontColorR = 0;
    $fontColorG = 0;
    $fontColorB = 0;

    $fontSize = rand(14, 24);
    $textX = rand(0, (int)($sizeX - 0.9 * $textLength * $fontSize)); // these coefficients are empiric
    $textY = rand((int)(1.25 * $fontSize), (int)($sizeY - 0.2 * $fontSize)); // don't try to learn how they were taken out

    $gdInfoArray = gd_info();
    if (! $gdInfoArray['PNG Support'])
        return IMAGE_ERROR_GD_TYPE_NOT_SUPPORTED;

    // create image with background
    $src_im = imagecreatefrompng( "captcha/background.png");
    if (function_exists('imagecreatetruecolor')) {
        // this is more qualitative function, but it doesn't exist in old GD
        $dst_im = imagecreatetruecolor($sizeX, $sizeY);
        $resizeResult = imagecopyresampled($dst_im, $src_im, 0, 0, $backgroundOffsetX, $backgroundOffsetY, $sizeX, $sizeY, $sizeX, $sizeY);
    } else {
        // this is for old GD versions
        $dst_im = imagecreate( $sizeX, $sizeY );
        $resizeResult = imagecopyresized($dst_im, $src_im, 0, 0, $backgroundOffsetX, $backgroundOffsetY, $sizeX, $sizeY, $sizeX, $sizeY);
    }

    if (! $resizeResult)
        return IMAGE_ERROR_GD_RESIZE_ERROR;

    // write text on image
    if (! function_exists('imagettftext'))
        return IMAGE_ERROR_GD_TTF_NOT_SUPPORTED;
    $color = imagecolorallocate($dst_im, $fontColorR, $fontColorG, $fontColorB);
    imagettftext($dst_im, $fontSize, -$angle, $textX, $textY, $color, $fontFile, $text);

    // output header
    header("Content-Type: image/png");

    // output image
    imagepng($dst_im);

    // free memory
    imagedestroy($src_im);
    imagedestroy($dst_im);

    return IMAGE_ERROR_SUCCESS;
}

function randomfile($folder='', $extensions='.*'){
 
    // fix path:
    $folder = trim($folder);
    $folder = ($folder == '') ? './' : $folder;
 
    // check folder:
    if (!is_dir($folder)){ die('invalid folder given!'); }
 
    // create files array
    $files = array();
 
    // open directory
    if ($dir = @opendir($folder)){
 
        // go trough all files:
        while($file = readdir($dir)){
 
            if (!preg_match('/^\.+$/', $file) and 
                preg_match('/\.('.$extensions.')$/', $file)){
 
                // feed the array:
                $files[] = $file;                
            }            
        }        
        // close directory
        closedir($dir);    
    }
    else {
        die('Could not open the folder "'.$folder.'"');
    }
 
    if (count($files) == 0){
        die('No files were found :-(');
    }
 
    // seed random function:
    mt_srand((double)microtime()*1000000);
 
    // get an random index:
    $rand = mt_rand(0, count($files)-1);
 
    // check again:
    if (!isset($files[$rand])){
        die('Array index was not found! very strange!');
    }
 
    // return the random file:
    return $folder . $files[$rand];
 
}



$key = md5("lenovo");

if (isset($_GET['enc']) and !isset($_GET['captcha'])) {
	// Decrypt enc, get the string and produce the image 
    $iv = hex2bin(substr($_GET['enc'], 0, 16));
    $enc = hex2bin(substr($_GET['enc'], 16));

	list ($status, $plaintext) = decrypt($enc, $key, $iv);
    if ($status == 0) {
	    produceimage($plaintext);
	} else {
		print("ERROR: padding invalid.");
	}
} else if (isset($_GET['enc']) and isset($_GET['captcha'])) {
    // Decrypt and validate string
    $iv = hex2bin(substr($_GET['enc'], 0, 16));
    $enc = hex2bin(substr($_GET['enc'], 16));

	list ($status, $plaintext) = decrypt($enc, $key, $iv);
    if ($status != 0) {
		print("ERROR: padding invalid.");
	}

    if ($plaintext == $_GET['captcha']) {
?>
<html>
<head>
<link rel="stylesheet" href="main.css" type="text/css" />
<title>You Passed the CAPTCSHA Test!</title>
</head>
<body>
<div class="captcha_example">
<h4>You have successfully passed the
"Completely Automated Public Turing test to tell Computers and SUPER Humans Apart" (CAPTCSHA) test.  You must be a computer, or super human.  For your efforts, please enjoy some lolcat.</h4><br>
<?php
        print("<img src=\"".randomfile("captcha-sol-pics/",".*")."\">");
?>
</div>
</body>
</html>

<?php

 	} else {
        print("Invalid CAPTCHA.  No donut.");
	}
}
