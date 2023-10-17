<?php
ob_start();
set_time_limit(0);

require_once __DIR__ . '/../../../config.php';

$connector_name = basename(WWWURL);
$base = DATA.'/h5p';

$wholeRequest = $_REQUEST['ID'];
$requestUri = explode(' ', $wholeRequest)[1];
$oldPath = explode('?', $requestUri)[0];

$src_file = str_replace('/'.$connector_name.'/src/tools/h5p/cache', $base, $oldPath);
$src_file = str_replace('/src/tools/h5p/cache', $base, $oldPath);

$realPath = realpath($src_file);
if($realPath === false || strpos($realPath, $base) !== 0) {
    http_response_code(404);
    return;
}

$filesize = filesize($src_file);

$mimetype = mime_content_type($src_file);
if(strpos($src_file, '.css')){
    $mimetype = 'text/css';
}elseif(strpos($src_file, '.js')){
    $mimetype = 'text/javascript';
}elseif(strpos($src_file, '.svg')){
    $mimetype = 'image/svg+xml';
}

header("Content-type: ".$mimetype);
header("Content-length: " . $filesize);
header('Access-Control-Allow-Origin: *');

if($filesize <= 2048) {
    @readfile($src_file);
} else {
    $fd = fopen($src_file, 'rb');
    if($fd != false){
        while(!feof($fd)) {
            $buffer = fread($fd, 2048);
            echo $buffer;
            flush();
        }
    }
    fclose($fd);
}

exit();

