<?php
header('Content-Type: application/json');
require_once('install/version_info.php');

$info["version"] = VERSION;
$info["build_date"] = BUILD_DATE;
$info["build_commit"] = BUILD_COMMIT;
$info["build_branch"] = BUILD_BRANCH;

echo json_encode($info);
