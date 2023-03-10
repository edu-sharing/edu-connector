<?php

namespace php\src\lib;

/**
 * Class Cryptographer
 * @package connector\lib
 */
class Cryptographer
{

    public function __construct()
    {
    }

    public function decryptData($encryptedData, $encryptedKey)
    {
        $this->checkPrivateKey();
        $privateKey = $this->getPrivateKey();

        $decryptedKey = '';
        if (false === openssl_private_decrypt($encryptedKey, $decryptedKey, $privateKey))
            throw new \Exception('Cannot decrypt AES Key.');
        openssl_free_key($privateKey);
        $decryptedData = openssl_decrypt($encryptedData, "aes-128-ecb", $decryptedKey, OPENSSL_RAW_DATA);
        if (false === $decryptedData)
            throw new \Exception('Cannot decrypt Data.');
        return json_decode($decryptedData);
    }

    public function checkPrivateKey()
    {
        if (!file_exists(DATA . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'private.key')) {
            $this->generateSslKeys();
        }

        $privateKey = openssl_pkey_get_private('file://' . DATA . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'private.key');
        if (false === $privateKey){
            throw new \Exception('Cannot load private key');
            return false;
        }
        openssl_free_key($privateKey);
        return true;
    }

    public function getPrivateKey()
    {
        $privateKey = openssl_pkey_get_private('file://' . DATA . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'private.key');
        return $privateKey;
    }

    public function getPublicKey() {
        $publicKey = openssl_pkey_get_public ('file://' . DATA . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'public.key');
        if(false === $publicKey)
            throw new \Exception('Cannot load public key from "file://' . DATA . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'public.key"');
        return $publicKey;
    }

    private function generateSslKeys()
    {
        $privateKey = openssl_pkey_new(array(
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ));

        openssl_pkey_export_to_file($privateKey, DATA . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'private.key');
        $keyStr = openssl_pkey_get_details($privateKey);
        file_put_contents(DATA  . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'public.key', $keyStr['key']);
        openssl_free_key($privateKey);
    }

}
