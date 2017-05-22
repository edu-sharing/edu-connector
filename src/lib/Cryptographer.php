<?php

namespace connector\lib;

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
        $decryptedData = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $decryptedKey, $encryptedData, MCRYPT_MODE_ECB));
        if (false === $decryptedData)
            throw new \Exception('Cannot decrypt Data.');
        return json_decode($decryptedData);
    }

    public function checkPrivateKey()
    {
        if (!file_exists(__DIR__ . '/../../assets/private.key')) {
            $this->generateSslKeys();
            throw new \Exception('No key found. Please check keys and try it again!');
        }

        $privateKey = openssl_pkey_get_private('file://' . __DIR__ . '/../../assets/private.key');
        if (false === $privateKey)
            throw new \Exception('Cannot load private key');
        openssl_free_key($privateKey);
        return true;

    }

    private function getPrivateKey()
    {
        $privateKey = openssl_pkey_get_private('file://' . __DIR__ . '/../../assets/private.key');
        return $privateKey;
    }

    public function getPublicKey($keyname = 'public') {
        $publicKey = openssl_pkey_get_public ('file://' . __DIR__ . '/../../assets/'.$keyname.'.key');
        if(false === $publicKey)
            throw new \Exception('Cannot load public key from "file://' . __DIR__ . '/../../assets/'.$keyname.'.key"');
        return $publicKey;
    }

    private function generateSslKeys()
    {
        $privateKey = openssl_pkey_new(array(
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ));
        openssl_pkey_export_to_file($privateKey, __DIR__ . '/../../assets/private.key');
        $keyStr = openssl_pkey_get_details($privateKey);
        file_put_contents(__DIR__ . '/../../assets/public.key', $keyStr['key']);
        openssl_free_key($privateKey);
    }

}