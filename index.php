<html>
<head>
<title>Evil CAPTCHA Validation</title>
<link rel="stylesheet" href="main.css" type="text/css" />
</head>

<body>
<form method="GET" action="captcha.php">

<?php
require_once('utils.php');
/* Generate and encrypt the string to use for the captcha */

$key = md5("lenovo");
$textstr = randstring(5);
$iv = randval(8);
$cipher = encrypt($textstr, $key, $iv);
$enc = bin2hex($iv) . bin2hex($cipher);
?>

<div class="captcha_example">
<h3>This is an evil captcha, to differentiate computers from humans.  Only a computer (or a super-human) can solve this test.</h3>
        <div style="margin-bottom:10px;">
            <h4>Security image:</h4>

            <img src="captcha.php?enc=<?php echo $enc; ?>" class="form_captcha" />
            <div class="lines">Verification (Type what you see):</div>
            <input type="text" name="captcha" value="" class="captcha" />
            <input type="hidden" name="enc" value="<?php echo $enc; ?>" />
            <input type="submit" value="Submit" class="captcha" />
        </div>
        </p>
</div>
</form>
<html>
