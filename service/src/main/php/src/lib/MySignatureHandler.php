<?php

namespace connector\lib;

use EduSharingApiClient\SignatureHandler;

/**
 * Signature handler forcing the SHA512withRSA algorithm regardless of the
 * algorithm advertised by the connected repository.
 * (Same as in the internal connector logic)
 */
class MySignatureHandler implements SignatureHandler
{
    public function getAlgorithm(): string {
        return 'SHA512withRSA';
    }
}