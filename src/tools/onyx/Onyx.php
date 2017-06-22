<?php
namespace connector\tools\onyx;

class Onyx extends \connector\lib\Tool {

    public function run() {
        $this->forwardToEditor();
    }

    private function forwardToEditor() {
        header('Location: ' . ONYXURL . '?repository=' . $this->getRepoId() . '&hash=' . urlencode($this->getHash()));
        exit();
    }

    private function encrypt($data) {
        $publicKey = openssl_pkey_get_public(ONYXPUB);
        $encrypted = '';
        openssl_seal($data, $sealed, $ekeys, array($publicKey));
        openssl_free_key($publicKey);
        if(empty($sealed)) {
            echo 'Encryption error';
            exit();
        }
        return $sealed . '::' . $ekeys[0];
    }

    private function getHash() {
        $hash = new \stdClass;
        $hash-> first = $_SESSION[$this->connectorId]['user']->profile->firstName;
        $hash-> last = $_SESSION[$this->connectorId]['user']->profile->lastName;
        $hash-> mail = $_SESSION[$this->connectorId]['user']->profile->email;
        $hash-> inst = $_SESSION[$this->connectorId]['user']->homeFolder->repo;
        $hash-> username = $_SESSION[$this->connectorId]['user']->userName;
        $hash-> nodeid = $_SESSION[$this->connectorId]['node']->node->ref->id;
        $hash-> accessToken = $_SESSION[$this->connectorId]['accessToken'];
        $hash-> refreshToken = $_SESSION[$this->connectorId]['refreshToken'] ;
        $hash = json_encode($hash);
        $hash = $this->encrypt($hash);
        $hash = base64_encode($hash);
        return $hash;
    }

    private function getRepoId() {
        return REPOSITORY;
    }

}
