<?php

$handle = fopen('callback.log', 'a');
fwrite($handle, json_decode($_REQUEST));
fclose($handle);