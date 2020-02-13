<?php
header('Content-Type: application/json');
require_once ('../config.php');


$info["version"] = VERSION;

echo json_encode($info);
