<?php

class Connector {

    public function __construct() {
        try {
            $this->setParameters();
            $this->startTool();
        } catch (Exception $e) {
            echo 'ERROR - ' . $e -> getMessage();
            error_log('ERROR IN CONNECTOR - ' . $e -> __toString());
        }
    }


    private function setParameters() {
        $encrypted = base64_decode($_REQUEST['data']);
        $decrypted = $this->decryptData($encrypted);
        $this->validate($decrypted);
        $_SESSION['parameters'] = $decrypted;
    }

    private function decryptData($encrypted) {
        $this->checkPrivateKey();
        $privateKey = $this->getPrivateKey();
        $decrypted = '';
        if(false === openssl_private_decrypt ( $encrypted, $decrypted, $privateKey))
            throw new Exception('Cannot decrypt data');
        openssl_free_key($privateKey);
        return json_decode($decrypted);
    }

    private function checkPrivateKey() {
        if(!file_exists(__DIR__ . '/../../assets/private.key')) {
            $this->generateSslKeys();
            throw new Exception('No key found. Please check keys and try it again!');
        }

        $privateKey = openssl_pkey_get_private ('file://' . __DIR__ . '/../../assets/private.key');
        if(false === $privateKey)
            throw new Exception('Cannot load private key');
        openssl_free_key($privateKey);
        return true;

    }

    private function getPrivateKey() {
        $privateKey = openssl_pkey_get_private ('file://' . __DIR__ . '/../../assets/private.key');
        return $privateKey;
    }

    private function generateSslKeys() {
        $privateKey = openssl_pkey_new(array(
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ));
        openssl_pkey_export_to_file($privateKey, __DIR__ . '/../../assets/private.key');
        $keyStr = openssl_pkey_get_details($privateKey);
        file_put_contents(__DIR__ . '/../../assets/public.key', $keyStr['key']);
        openssl_free_key($privateKey);
    }

    private function validate() {
        //check ts and so on
        return true;
    }

    private function startTool() {
        switch($_SESSION['parameters']->tool) {
            case 'ONLY_OFFICE':
                $tool = new OnlyOffice();
            break;
            default:
                echo 'Unknown tool';
            exit(0);
        }
    }
}