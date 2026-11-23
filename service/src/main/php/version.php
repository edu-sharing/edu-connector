<?php
// never mix warnings into the json (config.php defines VERSION too, the build version below has to win)
ini_set('display_errors', '0');
require_once __DIR__ . '/install/version_info.php';
// config.php (DATA, FORCED_APIURL, REPOSITORY_METADATA_URL) is loaded by the autoloader
require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');

// only the repository may read the build info (used for its status report / cve tracking)
$error = (new \connector\lib\RepositorySignatureVerifier())->verifyRequest();
if ($error !== null) {
    http_response_code(401);
    echo json_encode(['error' => $error]);
    exit;
}

$info["version"] = VERSION;
$info["build_date"] = BUILD_DATE;
$info["commit"] = BUILD_COMMIT;
$info["build_branch"] = BUILD_BRANCH;

echo json_encode($info);
