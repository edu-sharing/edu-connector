<?php

namespace connector\lib;

class MetadataGenerator {

    public function __construct() {

    }

    public function serve() {
        $cryptographer = new Cryptographer();
        $cryptographer -> checkPrivateKey();
        $publicKey = $cryptographer -> getPublicKey();
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><!DOCTYPE properties SYSTEM "http://java.sun.com/dtd/properties.dtd"><properties></properties>');
        $entry = $xml->addChild('entry', 'educonnector');
        $entry->addAttribute('key', 'appid');
        $entry = $xml->addChild('entry', $publicKey);
        $entry->addAttribute('key', 'public_key');
        header('Content-type: text/xml');
        print(html_entity_decode($xml->asXML()));
    }
}