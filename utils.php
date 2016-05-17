<?php
error_reporting('E_ALL');

function randstring($len)
{
    $chars = array("a","A","b","B","c","C","d","D","e","E","f","F","g","G","h",
                   "H","i","I","j","J","k","K","L","m","M","n","N","o","p","P",
                   "q","Q","r","R","s","S","t","T","u","U","v","V","w","W","x",
                   "X","y","Y","z","Z","2","3","4","5","6","7","8","9");
    $textstr = '';
    for ($i = 0; $i < $len; $i++) {
       $textstr .= $chars[rand(0, count($chars) - 1)];
    }
    return($textstr);
}

function encrypt($str, $key, $iv)
{
    $block = mcrypt_get_block_size('des', 'cbc');
    $pad = $block - (strlen($str) % $block);
    $str .= str_repeat(chr($pad), $pad);

    return mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_CBC, $iv);
}

function decrypt($str, $key, $iv)
{
    $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_CBC, $iv);

    $block = mcrypt_get_block_size('des', 'cbc');
    $pad = ord($str[($len = strlen($str)) - 1]);

    $padding = substr($str, $pad*-1, $pad);
    if ($pad > mcrypt_get_block_size('des', 'cbc')) {
        return array(-1, '');
    }
    if (strlen($padding) != $pad) {
        return array(-2, '');
    }
    if (substr_count($padding, chr($pad)) != $pad) {
        return array(-3, '');
    }

    return array (0, substr($str, 0, strlen($str) - $pad));
}

function randval($len) {
    if (@is_readable('/dev/urandom')) {
        $f=fopen('/dev/urandom', 'r');
        $urandom=fread($f, $len);
        fclose($f);
    }

    $return='';
    for ($i=0;$i<$len;++$i) {
        if (!isset($urandom)) {
            if ($i%2==0) mt_srand(time()%2147 * 1000000 + (double)microtime() * 1000000);
            $rand=48+mt_rand()%64;
        } else $rand=48+ord($urandom[$i])%64;

        if ($rand>57)
            $rand+=7;
        if ($rand>90)
            $rand+=6;

        if ($rand==123) $rand=45;
        if ($rand==124) $rand=46;
        $return.=chr($rand);
    }
    return $return;
}

function hex22bin($hexstr) {
	return pack("H*", $hexstr);
}
