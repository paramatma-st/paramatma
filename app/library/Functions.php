<?php
/**
 * Paramatma (http://paramatma.io)
 *
 * @link      http://github.com/paramatma-io/paramatma for the canonical source repository
 * @copyright Copyright (c) 2015 Paramatma team
 * @license   http://opensource.org/licenses/MIT MIT License
 * @package   Paramatma
 */


/**
 * App environment check
 *
 * @return bool
 */
function production() {
    if (APPLICATION_ENV == 'production') {
        return true;
    }

    return false;
}

/**
 * Debug var
 *
 * @param mixed $data
 * @param bool $exit
 * @param null $action
 */
function debug_var($data, $exit = true, $action = null) {
    print '<pre>';

    switch ($action) {
        case 'dump' :
        	var_dump($data);
            break;
        default :
            print_r($data);
            break;
    }

    print '</pre>';

    if ($exit) {
        exit();
    }
}

/**
 * @param Exception $e
 */
function debug_exception(Exception $e) {
    debug_var($e->getFile() . '(' . $e->getLine() .')', false);
    debug_var($e->getCode() . ': ' . $e->getMessage(), false);
    debug_var($e->getTraceAsString(), false);

    $inc = get_included_files();
    sort($inc);
    debug_var($inc);
}

/**
 * Print dev stats
 */
function debug_print_stats() {
    if (!production()) {
        $_st = array_sum(explode(' ', $_SERVER['REQUEST_TIME_FLOAT']));

        print '<div id="dev-info"><div class="container">
               <span style="font-size: 14px;">⌚</span>&nbsp;' . substr(array_sum(explode(' ', microtime())) - $_st, 0, 7) .
               's&nbsp;&nbsp;&nbsp;❒&nbsp;' . human_readable_size(memory_get_peak_usage(true), 6, 'kb') .
               '&nbsp;&nbsp;&nbsp;⬣&nbsp;' . count(get_included_files()) . '</div></div>';
    }
}

/**
 * Is Post request?
 */
function isPost() {

}

/**
 * Is Get request?
 */
function isGet() {

}

/**
 * Is XML Http request?
 * X_REQUESTED_WITH   XMLHttpRequest
 *
 * @return bool
 */
function isXmlHttpRequest()
{
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        return true;
    }

    return false;
}

/**
 * Convert all <br> tags to new line code
 *
 * @param string $string
 * @return string
 */
function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

/**
 * Функция вывода размера в удобочитаемом для человека виде (c) itsacon, взято с php.net
 *
 * @param $val
 * @param int $round
 * @param string $unit
 * @return string
 */
function human_readable_size($val, $round = 2, $unit = 'b') {
    $units = array(
        'b' => 0,
        'kb' => 1,
        'mb' => 2,
        'gb' => 3,
        'tb' => 4,
        'pb' => 5,
        'eb' => 6,
        'zb' => 7,
        'yb' => 8
    );

    if ($val <= 0) {
        return '0 B';
    }

    if ('auto' == $unit || 'a' == $unit) {
        $dv = floor(log($val, 1024));
        $unit = array_search($dv, $units);
    } else {
        $dv = $units[$unit];
    }

    for ($i = 0; $i < $dv; $i++) {
        $val = $val / 1024;
    }

    return round($val, $round) . ' ' . mb_convert_case($unit, MB_CASE_UPPER);
}

/**
 *
 * @param $item
 */
function mb_itemtolower(&$item) {
    $item = mb_strtolower($item);
}

/**
 *
 *
 * @param $string
 * @param null $encoding
 * @return string
 */
if (!function_exists('mb_ucfirst') && function_exists('mb_substr')) {
    function mb_ucfirst($string, $encoding=null) {
        if (null === $encoding) {
            $encoding = mb_internal_encoding();
        }

        $string = mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding) . mb_substr($string, 1, null, $encoding);
        return $string;
    }
}

/**
 * Get age
 *
 * @param $birthday
 * @return bool|string
 */
function get_age($birthday) {
    if (is_null($birthday) || $birthday == '') {
        return 'Unborne';
    }

    return (date('Y') - substr($birthday, 0, 4) - (intval(date('m') .
           date('d')) < intval(substr($birthday, 5, 2) . substr($birthday, 8, 2))));
}

/**
 * Функция определения пользовательского IP
 *
 * @param string $flag
 * @return bool|int|string
 */
function get_remote_ip($flag='long') {
    if (($ip = $_SERVER['REMOTE_ADDR']) == true) {
        if ($flag=='long') {
        	return ip2long($ip);
        }

        if ($flag=='hostname') {
        	return gethostbyaddr($ip);
        } else {
        	return $ip;
        }
    }

    return false;
}

/**
 * Функция генерации произвольного значения
 * Пример: $pwd = random(6, 'LD'); - пароль длинной 6 символов из букв любого регистра и цифр
 *
 * @param $len
 * @param string $pattern
 * @param null $case
 * @return string
 */
function random($len, $pattern='', $case=null) {
	$rnd = '';
	$cancel = false;

	switch ($pattern) {
		case 'LDS':
			$pattern='[^\S]';
			break;
        case 'LD':
	        $pattern='[^a-zA-Z0-9]';
	        break;
        case 'L':
            $pattern='[^a-zA-Z]';
            break;
		default:
            $pattern='[^0-9]';
            $cancel = true;
            break;
	}

    do {
        $rnd_tmp = crypt(mt_rand(100000000, 999999993), mt_rand(10, 99));
        $rnd = $rnd . preg_replace('/' . $pattern . '/u', '', $rnd_tmp);
        $rnd = mb_substr($rnd, 0, $len);
    }
    while (mb_strlen($rnd) < $len);

    if ($case == 'lower' && !$cancel) {
           $rnd = mb_strtolower($rnd);
    } elseif ($case == 'upper' && !$cancel) {
           $rnd = mb_strtoupper($rnd);
    }

	return $rnd;
}

/**
 * Get file extension
 *
 * @param string $path
 * @param bool $lowercase
 * @return mixed|string
 */
function file_ext($path, $lowercase = true) {
	$ext = pathinfo($path, PATHINFO_EXTENSION);

	if ($lowercase) {
		return strtolower($ext);
	}

    return $ext;
}

/**
 * @param $path
 * @param bool $lowercase
 * @return mixed|string
 */
function file_extension($path, $lowercase = true) {
    return file_ext($path, $lowercase);
}

/**
 * @param $path
 * @return mixed
 */
function filename($path) {
    return pathinfo($path, PATHINFO_FILENAME);
}

/**
 * @param $name
 * @param $ext
 * @return mixed
 */
function mb_basename($name, $ext) {
    return preg_replace('/'. preg_quote($ext) .'/iu', '', $name);
}

/**
 * Remove directory
 * Code by Tim Cooper (http://techietim.ca/), thanks! :)
 *
 * @license BSD
 * @param  string $dir
 * @param  bool   $removeEmpty
 * @throws Exception
 * @return void
 */
function removeDir($dir, $removeEmpty=true) {
    $dir = rtrim($dir, '/');

    $contents = scandir($dir);

    foreach($contents as $item) {
        if ($item != '.' && $item != '..') {
            $item = $dir . '/' . $item;

            if (is_dir($item)) {
                removeDir($item);
            } else {
                if (!(@unlink($item))) {
                    throw new Exception('Unable to remove file: ' . $item);
                }
            }
        }
    }

    if ($removeEmpty) {
	    if (!(@rmdir($dir)) && (is_dir($dir))) {
	        throw new Exception('Unable to remove dir: ' . $dir);
	    }
    }
}

/*
function deleteDir($path) {
	return is_file($path) ?
	@unlink($path) :
	array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
}
*/

/**
 * Calculates directory size recursive
 *
 * @param string $dir
 * @return int
 */
function dirsize($dir) {
    $size = 0;

    if ('/' != substr($dir, -1)) {
    	$dir .= '/';
    }

	$d = dir($dir);

	while (false !== ($entry = $d->read())) {
		if ($entry != '.' && $entry != '..') {
            $size += (is_dir($dir . $entry)) ? dirsize($dir . $entry) : filesize($dir . $entry);
        }
	}

	$d->close();

    return $size;
}

/**
 * Get file permissions
 * Source from php.net
 *
 * @license BSD
 * @param  string $fp
 * @return string
 */
function get_filepermissions($fp) {
    $perms[0] = fileperms($fp);

    if (($perms & 0xC000) == 0xC000) { // Сокет
        $perms[1] = 's';
    } elseif (($perms & 0xA000) == 0xA000) { // Символическая ссылка
        $perms[1] = 'l';
    } elseif (($perms & 0x8000) == 0x8000) { // Обычный
        $perms[1] = '-';
    } elseif (($perms & 0x6000) == 0x6000) { // Специальный блок
        $perms[1] = 'b';
    } elseif (($perms & 0x4000) == 0x4000) { // Директория
        $perms[1] = 'd';
    } elseif (($perms & 0x2000) == 0x2000) { // Специальный символ
        $perms[1] = 'c';
    } elseif (($perms & 0x1000) == 0x1000) { // Поток FIFO
        $perms[1] = 'p';
    } else { // Неизвестный
        $perms[1] = 'u';
    }

    // Владелец
    $perms[1] .= (($perms & 0x0100) ? 'r' : '-');
    $perms[1] .= (($perms & 0x0080) ? 'w' : '-');
    $perms[1] .= (($perms & 0x0040) ?
                (($perms & 0x0800) ? 's' : 'x' ) :
                (($perms & 0x0800) ? 'S' : '-'));

    // Группа
    $perms[1] .= (($perms & 0x0020) ? 'r' : '-');
    $perms[1] .= (($perms & 0x0010) ? 'w' : '-');
    $perms[1] .= (($perms & 0x0008) ?
                (($perms & 0x0400) ? 's' : 'x' ) :
                (($perms & 0x0400) ? 'S' : '-'));

    // Мир
    $perms[1] .= (($perms & 0x0004) ? 'r' : '-');
    $perms[1] .= (($perms & 0x0002) ? 'w' : '-');
    $perms[1] .= (($perms & 0x0001) ?
                (($perms & 0x0200) ? 't' : 'x' ) :
                (($perms & 0x0200) ? 'T' : '-'));

    return $perms;
}

/**
 *
 *
 * @param $source
 * @param $replace_data
 * @return mixed
 */
function scv_replace($source, $replace_data) {
    $patterns=array();

    preg_match_all('/\[(\w+)\]/iu', $source, $vars);

    $replacements = array();
    $vars = array_unique($vars[1]);

    foreach ($vars as $key => $value) {
        if (isset($replace_data[$value])) {
            $replacements[$key] = $replace_data[$value];
            $patterns[$key] = '/\[' . $value . '\]/iu';
        }
    }

    return preg_replace($patterns, $replacements, $source);
}

/**
 *
 *
 * @param $algo
 * @param $data
 * @param bool $raw_output
 * @return bool|int|string
 */
if(!function_exists('hash')) {
    function hash($algo, $data, $raw_output = false) {
        switch ($algo) {
            case 'md5':
                return(md5($data));
                break;
            case 'sha1':
                return(sha1($data, $raw_output));
                break;
            case 'crc32':
                return(crc32($data));
                break;
            case 'sha256':
                return(mhash(MHASH_SHA256, $data));
                break;
            case 'gost':
                return(mhash(MHASH_GOST, $data));
                break;
            case 'ripemd160':
                return(mhash(MHASH_RIPEMD160, $data));
                break;
            default: return false;
        }
    }
}

/**
 * Generate salt
 *
 * @param int $len
 * @param string $algo
 * @return bool|string
 */
function genSalt($len = 33, $algo='sha1') {
    return hash($algo, random($len, 'LDS'));
}

/**
 * Returns password hash
 *
 * @param string $pwd
 * @param string $salt1
 * @param string $salt2
 * @param string $algo
 * @return bool|string
 */
function passwordHash ($pwd, $salt1, $salt2, $algo='sha1') {
	$hl = floor(strlen($pwd)/2);

    return hash($algo, (mb_substr($pwd, 0, $hl) . $salt1 . mb_substr($pwd, $hl) . $salt2));
}

/**
 * Encrypt cookie
 *
 * @param $cipher
 * @param $key
 * @param $data
 * @param $mode
 * @param $iv
 * @return bool|string
 */
function encrypt_cookie($cipher, $key, $data, $mode, $iv){
   if(!isset($data) || !isset($key) || !isset($iv)) {
       return false;
   }

   return trim(base64_encode(mcrypt_encrypt($cipher, $key, $data, $mode, $iv)));
}

/**
 * Decrypt cookie
 *
 * @param $cipher
 * @param $key
 * @param $data
 * @param $mode
 * @param $iv
 * @return bool|string
 */
function decrypt_cookie($cipher, $key, $data, $mode, $iv){
   if(!isset($data) || !isset($key) || !isset($iv)) {
       return false;
   }

   $data = base64_decode($data);

   return mcrypt_decrypt($cipher, $key, $data, $mode, $iv);
}

/**
 * xHash - function to generate salted hashes from given data.
 * Really huge thanks for Kevin for this nice algo (kevin at bionichippo dot com).
 *
 * @param mixed  $data
 * @param string $salt1
 * @param string $salt2
 * @param string $salt3
 * @param string $algo
 * @return string
 */
function xHash($data, $salt1, $salt2, $salt3 = '', $algo = 'sha1'){
    $data = str_split($data, (strlen($data) / 2) + 1);

    //return hash($algo, $salt1 . $data[0] . $salt2 . $data[1] . $salt3);
    return hash($algo, $salt1 . $data[0] . $salt2 . $data[1] . $salt3);
}

/**
 *
 *
 * @param $type
 * @return null|string
 */
function getFileExtFromMimeType($type)
{
    switch ($type) {
        case 'image/gif' :
            return 'gif';
            break;
        case 'image/pjpeg' :
            return 'jpg';
            break;
        case 'image/jpeg' :
            return 'jpg';
            break;
        case 'image/x-png' :
            return 'png';
            break;
        case 'image/png' :
            return 'png';
            break;
        case 'application/pdf' :
            return 'pdf';
            break;
        case 'text/rtf' :
            return 'rtf';
            break;
        default :
            return null;
    }
}

/**
 * Транслитерация русского алфавита латиницей
 *
 * @param string $str
 * @param string $space
 * @return string
 */
function translit_ru($str, $space = '_') {

    $tr = array(
        "Ґ" => "G", "Ё" => "YO", "Є" => "E", "Ї" => "YI", "І" => "I", "і" => "i",
        "ґ" => "g", "ё" => "yo", "№" => "#", "є" => "e", "ї" => "yi", "А" => "A",
        "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D", "Е" => "E", "Ж" => "ZH",
        "З" => "Z", "И" => "I", "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M",
        "Н" => "N", "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
        "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH", "Ш" => "SH",
        "Щ" => "SCH", "Ъ" => "'", "Ы" => "YI", "Ь" => "", "Э" => "E", "Ю" => "YU",
        "Я" => "YA", "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d",
        "е" => "e", "ж" => "zh", "з" => "z", "и" => "i", "й" => "y", "к" => "k",
        "л" => "l", "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
        "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h", "ц" => "ts",
        "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "'", "ы" => "yi", "ь" => "'",
        "э" => "e", "ю" => "yu", "я" => "ya", " " => $space
    );

    return strtr($str, $tr);
}
