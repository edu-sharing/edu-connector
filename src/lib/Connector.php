<?php

class Connector {

    private $tool;
    private $parameters;

    public function __construct() {
        $this -> setParameters();
        $this -> switchTool();
        $this -> runTool();
    }


    private function setParameters() {
        $encrypted = $_REQUEST['data'];
        $decrypted = $this->decryptData($encrypted);
        $this->validate($decrypted);
        $this->parameters = $decrypted;
    }

    private function decryptData($encrypted) {
        $this->checkPrivateKey();
        $privateKey = $this->getPrivateKey();
        $decrypted = '';
        if(false === openssl_private_decrypt ( $encrypted , $decrypted , $privateKey))
            throw new Exception('Cannot decrypt data');
        return json_decode($decrypted);
    }

    private function checkPrivateKey() {
        if(!file_exists(__DIR__ . '/../../assets/private.key'))
            $this->generateSslKeys();
        $privateKey = openssl_pkey_get_private (__DIR__ . '/../../assets/private.key');
        if(false === $privateKey)
            throw new Exception('Cannot load private key');
        return true;

    }

    private function getPrivateKey() {
        $privateKey = openssl_pkey_get_private (__DIR__ . '/../../assets/private.key');
        return $privateKey;
    }

    private function generateSslKeys() {
        $privateKey = openssl_pkey_new(array(
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ));
        openssl_pkey_export_to_file($privateKey, __DIR__ . '/../../assets/private.key');
        $a_key = openssl_pkey_get_details($privateKey);
        file_put_contents(__DIR__ . '/../../assets/public.key', $a_key['key']);
        openssl_free_key($privateKey);
    }

    private function validate() {
        //check ts and so on
        return true;
    }

    private function switchTool() {
        switch($this->para) {
            case 'ONLY_OFFICE':
                $this -> tool = new OnlyOffice();
            break;
            default:
                echo 'Unknown tool';
            exit(0);
        }
    }

    private function runTool() {
        $this -> tool -> run();
    }
}