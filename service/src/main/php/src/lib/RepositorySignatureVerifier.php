<?php

namespace connector\lib;

/**
 * Verifies that a request was signed by the repository this connector is registered at.
 *
 * The repository sends the usual app signature headers (the same scheme the connector uses for its own calls to the
 * repository, see EduRestClient::getHeaders):
 *   X-Edu-App-Id        app id of the repository
 *   X-Edu-App-Ts        timestamp in ms
 *   X-Edu-App-Signed    signed data, must be app id + timestamp
 *   X-Edu-App-Sig       base64 signature of the signed data, made with the private key of the repository
 *   X-Edu-App-SignedAlg e.g. SHA512withRSA
 *
 * The public key (and app id) of the repository is loaded from its metadata (REPOSITORY_METADATA_URL, by default
 * derived from FORCED_APIURL) and cached in DATA/ssl. It is reloaded when it is older than KEY_MAX_AGE, or when a
 * signature does not verify (key rotation), but at most once per KEY_MIN_REFRESH.
 */
class RepositorySignatureVerifier
{
    const MAX_TIMESTAMP_OFFSET_MS = 10 * 1000;
    const KEY_MAX_AGE = 60 * 60;
    const KEY_MIN_REFRESH = 60;
    const METADATA_TIMEOUT = 5;

    const ALGORITHMS = [
        'SHA512withRSA' => OPENSSL_ALGO_SHA512,
        'SHA384withRSA' => OPENSSL_ALGO_SHA384,
        'SHA256withRSA' => OPENSSL_ALGO_SHA256,
    ];

    /**
     * @return string|null null if the request is signed by the repository, otherwise the reason why not
     */
    public function verifyRequest(): ?string
    {
        $appId = $this->getHeader('X-Edu-App-Id');
        $ts = $this->getHeader('X-Edu-App-Ts');
        $signed = $this->getHeader('X-Edu-App-Signed');
        $sig = $this->getHeader('X-Edu-App-Sig');
        $alg = $this->getHeader('X-Edu-App-SignedAlg') ?: 'SHA512withRSA';
        if (empty($appId) || empty($ts) || empty($signed) || empty($sig)) {
            return 'missing signature headers';
        }
        if (!ctype_digit($ts) || abs(round(microtime(true) * 1000) - (int)$ts) > self::MAX_TIMESTAMP_OFFSET_MS) {
            return 'timestamp is invalid or too old';
        }
        if ($signed !== $appId . $ts) {
            return 'signed data does not match app id and timestamp';
        }
        if (!isset(self::ALGORITHMS[$alg])) {
            return 'unsupported signature algorithm';
        }
        $signature = base64_decode($sig, true);
        if ($signature === false) {
            return 'signature is not base64';
        }

        $repository = $this->getRepository(false);
        if ($repository !== null && $this->verify($repository, $appId, $signed, $signature, $alg)) {
            return null;
        }
        // the key of the repository may have changed, try once with a fresh one
        $repository = $this->getRepository(true);
        if ($repository === null) {
            return 'public key of the repository is not available';
        }
        if ($this->verify($repository, $appId, $signed, $signature, $alg)) {
            return null;
        }
        return 'signature is invalid';
    }

    private function verify(array $repository, string $appId, string $signed, string $signature, string $alg): bool
    {
        if (!empty($repository['appid']) && $repository['appid'] !== $appId) {
            return false;
        }
        $key = openssl_pkey_get_public($repository['public_key']);
        if ($key === false) {
            return false;
        }
        return openssl_verify($signed, $signature, $key, self::ALGORITHMS[$alg]) === 1;
    }

    /**
     * @param bool $refresh reload the metadata, unless the cache was written less than KEY_MIN_REFRESH ago
     * @return array|null ['appid' => ..., 'public_key' => pem]
     */
    private function getRepository(bool $refresh): ?array
    {
        $cacheFile = DATA . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'repository.json';
        $cached = null;
        $age = PHP_INT_MAX;
        if (is_file($cacheFile)) {
            $cached = json_decode((string)file_get_contents($cacheFile), true) ?: null;
            $age = time() - filemtime($cacheFile);
        }
        $reload = $cached === null || $age > self::KEY_MAX_AGE || ($refresh && $age > self::KEY_MIN_REFRESH);
        if (!$reload) {
            return $cached;
        }
        $loaded = $this->loadRepositoryMetadata();
        if ($loaded === null) {
            // repository not reachable, keep using the known key
            return $cached;
        }
        @file_put_contents($cacheFile, json_encode($loaded), LOCK_EX);
        return $loaded;
    }

    private function loadRepositoryMetadata(): ?array
    {
        $url = self::getMetadataUrl();
        if (empty($url)) {
            error_log('connector: no REPOSITORY_METADATA_URL (or FORCED_APIURL) configured, can not verify repository signatures');
            return null;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::METADATA_TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::METADATA_TIMEOUT);
        $xml = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($xml === false || $status !== 200) {
            error_log('connector: could not load repository metadata from ' . $url . ' (status ' . $status . ')');
            return null;
        }
        $properties = @simplexml_load_string($xml);
        if ($properties === false) {
            error_log('connector: invalid repository metadata from ' . $url);
            return null;
        }
        $result = ['appid' => null, 'public_key' => null];
        foreach ($properties->entry as $entry) {
            $key = (string)$entry['key'];
            if (array_key_exists($key, $result)) {
                $result[$key] = trim((string)$entry);
            }
        }
        if (empty($result['public_key'])) {
            error_log('connector: repository metadata from ' . $url . ' contains no public key');
            return null;
        }
        $result['public_key'] = self::toPem($result['public_key']);
        return $result;
    }

    /**
     * the repository publishes its metadata (incl. public key) at /edu-sharing/metadata
     */
    public static function getMetadataUrl(): ?string
    {
        if (defined('REPOSITORY_METADATA_URL') && REPOSITORY_METADATA_URL) {
            return REPOSITORY_METADATA_URL;
        }
        if (defined('FORCED_APIURL') && FORCED_APIURL) {
            // e.g. http://repository-service:8080/edu-sharing/rest/
            return preg_replace('#/rest/?$#', '', rtrim(FORCED_APIURL)) . '/metadata?format=lms';
        }
        return null;
    }

    private static function toPem(string $key): string
    {
        $body = preg_replace('/-----(BEGIN|END) PUBLIC KEY-----|\s+/', '', $key);
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split($body, 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    private function getHeader(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return isset($_SERVER[$key]) ? trim($_SERVER[$key]) : null;
    }
}
