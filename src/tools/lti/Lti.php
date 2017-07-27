<?php
/*
 * @see from https://gist.github.com/matthanger/1171921
 */
namespace connector\tools\lti;

define("LTI_VERSION", "LTI-1p0");
define("LTI_MESSAGE_TYPE", "basic-lti-launch-request");
define("OAUTH_CALLBACK", "about:blank");
define("OAUTH_VERSION", "1.0");
define("OAUTH_SIGNATUER_METHOD", "HMAC-SHA1");

class Lti extends \connector\lib\Tool {

    private $launch_data = array();
    private $signature = '';

    //replace with session data
    private $toolConfig;



    private function prepareLaunchData() {

        //set person details, roles, etc.

        $this->launch_data = array(
            //"user_id" => "292832126", //nö
            //"roles" => "Instructor", // nö
            "resource_link_id" => "achso" //chatroom id / pad id etc.
        );

        $this->launch_data["lti_version"] = LTI_VERSION;
        $this->launch_data["lti_message_type"] = LTI_MESSAGE_TYPE;

        # Basic LTI uses OAuth to sign requests
        $this->addOAuthData();
        $this->setOAuthSignature();
    }

    private function setToolConfig() {
        //get config from node
        //$this->apiClient()->getNode($nodeId from config object);
        $this->toolConfig = ''; // config properties....
    }

    private function setOAuthSignature() {

        //get values from toolconfig
        //$bla = $this->toolConfig['blub'];

        # In OAuth, request parameters must be sorted by name
        $launch_data_keys = array_keys($this->launch_data);
        sort($launch_data_keys);
        $launch_params = array();
        foreach ($launch_data_keys as $key) {
            array_push($launch_params, $key . "=" . rawurlencode($this->launch_data[$key]));
        }
        $base_string = "POST&" . urlencode($this->launch_url) . "&" . rawurlencode(implode("&", $launch_params));
        $secret = urlencode($secret) . "&";
        $this->signature = base64_encode(hash_hmac("sha1", $base_string, $secret, true));
    }

    private function addOAuthData() {

        //get values from toolconfig
        //$bla = $this->toolConfig['blub'];

        # OAuth Core 1.0 spec: http://oauth.net/core/1.0/
        $this->launch_data["oauth_callback"] = OAUTH_CALLBACK;
        $this->launch_data["oauth_consumer_key"] = $key;
        $this->launch_data["oauth_version"] = OAUTH_VERSION;
        $this->launch_data["oauth_nonce"] = uniqid('', true);
        $now = new \DateTime();
        $this->launch_data["oauth_timestamp"] = $now->getTimestamp();
        $this->launch_data["oauth_signature_method"] = OAUTH_SIGNATUER_METHOD;
    }

    private function renderLaunchForm() {

        //get values from toolconfig
        //$bla = $this->toolConfig['blub'];

        echo '<html>
        <head></head>
        <body onload="document.ltiLaunchForm.submit();">
        <body>
        <form id="ltiLaunchForm" name="ltiLaunchForm" method="POST" action="'.printf($this->launch_url).'">';
            foreach ($this->launch_data as $k => $v ) {
                echo '<input type="hidden" name="' . $k  . '" value="' . $v . '">';
            }
            echo '<input type="hidden" name="oauth_signature" value="' . $this->signature . '">';
            echo '<button type="submit">Launch</button>
        </form>
        <body>
        </html>';
    }

    public function run() {
        $this->setToolConfig();
        $this->prepareLaunchData();
        $this->renderLaunchForm();
    }
}
