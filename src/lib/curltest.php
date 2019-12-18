<?php

define('APPID', 'educonnector');

function getHeaders() {
    $timestamp = round(microtime(true) * 1000);
    $signdata = APPID . $timestamp;
    //echo $signdata;
    //$cryptographer = new \connector\lib\Cryptographer();
    //$privkey = $cryptographer->getPrivateKey();
    $privkey = '-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDDrXJQ8CB9Ixzw
HUUAmucwcPTOsQFkvDzmOdkru9loKm7owNmkDBxwKs1QKQ4VofCYGzbf2rKyJRGp
70CKhB6RnW9g0hz6OdHmbGzm2UjZebKBp77bwWjwgE05w9eQ5/w/yioAtPnzislT
O0V/xKGqQuyclsIv48AKylEQCogplK+6/QjbGcskQLWar0+9l06HxXH+UtZRDGCt
PM/enfDCz8rQNrmwEIbZJL21yBqk6ltx1H6Vo5jGtlvjA6Wsvu5TVxYJrHGmLWvQ
RIQXWiv/vxm79a3RtysgcooRwL6frQhdLIGOKLf91Fq9BR4jM+LTNWv2TzuepgGA
WgJP0v7PAgMBAAECggEAMqjJ6shwMNWRXVzGi4SPDAyCZKyQxsqxHY2metsUSxKJ
Sjr7XaxBBI0gQHuQiOs3Bbot98B/+Pz92Lf3RqUz4NEYj8F1/RZREE3L1+wzHxKR
s12BXRVynKkq1SStv3c/6Cfnve0ctP+eZaz3rj9y90iCR3wEZC+bfW+pkvSXEeRX
o2puRavFXoc39omEK0vTwHMwpJcsPdz1l/VwjYbUvctGbom+8f3WbnW4dnL/47/M
W3+i2QNeigBTCb1I7MmahguzfYj92ni+vqjl9CPoUhH9nPxbBzDX/gVcPWPBAj0o
mr0fXd7qyJRLiDY77PwIJttE2R4ugJH+R+NgalKNaQKBgQDqWR90NxP/Cl8iggby
JPdSL/stth7B9Zq5Xlkru8kq54hBwsTNwMR6H/WbSKkZKDYTBSQZ+Ddl/TG8Jin5
BnN9uXKwE3/tdCzygLuncdVNdSqywgXYnkYKQHzTahzrQQkFMZ4ZUQAeVlfxQCeN
nPyGVzuf2jHHEqgRMqa4JbYWGwKBgQDVwayMiW/ORO8KdkmKj8St5UmMc6j0Xq9T
rQ26sXvDmVgZRHvXoU9VIHHirSnGCzN5Xa0sjYQ5tQATBmZkJm4XsTMuKYbMO+u5
dY/iOoQnOJnt5QKWTjHvx7kV5RBReereV3Ojp+7RZbc5TcOQzsKyq1S84U0YGxCm
cvFnI2JVXQKBgHiVZ0PrW1SuR4mAEobiUohtu/cncOgosnaTf5qrQ7XZ8Ri1WYLt
n6ufakd+udQCBuD+kjbyq77E66R+lrZWhXK5y7OiNP8/+ijF6MkeH719fn0ArPVr
a2q3CAPY5AyBKF0NzOYF8eOqVhIDCtcpYh7WIA3+vgJLLUO8JmE2tlzlAoGBALxB
A6wJ5pPtFfFK193WXEsiYjH2Mth1A0hYYn0Hjo5nYLVwIPl5MZsxJduS0fV/K+g5
XiicwdTo2ZXnzwlo5xCqmP6QyAzawPHm3b9J9dVs/fQL+slROJ2KcjEcOdDn5LfI
oSmC3stAH6uyFwhTcBlW1xw5+GHAhFtzY7OH8DRpAoGBAJ08shsVt3rN4x31zz5K
RBREQdYzKnNu9s8vbXO42FXs1x41ddrUch9mIpJHHUspZTzH2dzCvWbUjH8HYwCj
CfTAVyfGdPYwDYtqzCA4FGgb6HVFOjb91K6Y47oBD9pYkH3EinrN4wsLxwyqtPnL
n1KoKG2SfVU7rYcuAWhv8dXM
-----END PRIVATE KEY-----
';
    $pkeyid = openssl_get_privatekey($privkey);
    openssl_sign($signdata, $signature, $pkeyid);
    $signature = base64_encode($signature);
    openssl_free_key($pkeyid);

    return array(
        //$this->getAuthHeader(),
        'Cookie:JSESSIONID=E5952CC6BF8F8FFC06634BEB3FB072C2',
        'X-Edu-App-Id:' . APPID,
        'X-Edu-App-Sig:'.$signature,
        'X-Edu-App-Signed:'.$signdata,
        'X-Edu-App-Ts:'.$timestamp,
        'Accept: application/json'
    );
}

echo 'test: ';

echo 'headers: ';
//echo getHeaders();

$ch = curl_init('https://smart.vhb.org:443/edu-sharing/rest/node/v1/nodes/-home-/2a4424e8-4d73-42b9-bd92-ccd295d9376a/content?versionComment=EDITOR_UPLOAD,H5P&mimetype=application/zip');
$headers = getHeaders();
$headers[] = 'Content-Type: multipart/form-data';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//$cfile = curl_file_create($contentpath, $mimetype, 'file');
error_log('file: '.print_r($cfile, true));
//$fields = array('file' => $cfile);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_SAFE_UPLOAD, 1);
//curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo 'httpcode: '.$httpcode.' ';
if ($httpcode >= 200 && $httpcode < 308) {
    curl_close($ch);
    echo json_decode($res);
}
//error_log('httpcode: '.$httpcode);
$error = curl_error($ch);
curl_close($ch);
echo 'end ';
echo $error;
