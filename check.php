<?php

$data = new stdClass();

$data -> endpoint = 'http://localhost:8080/edu-sharing/ng2/../rest/';
$data -> node = '';
$data -> tool = 'ONLY_OFFICE';
$data -> filetype = 'docx';
$data -> ts = time();
$data -> sessionId = '425B349D3EE6DCD9EFC6CD67D48DAC7B';

$jsondata = json_encode($data);
$crypted = '';

$pkey = openssl_get_publickey('file://' . __DIR__ . '/assets/public.key');
openssl_public_encrypt($jsondata, $crypted, $pkey);

$data = base64_encode($crypted);

header('Location: http://127.0.0.1/eduConnector/?data=' . urlencode($data));