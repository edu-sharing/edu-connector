<?php
/*
 * @see from https://gist.github.com/matthanger/1171921
 */
namespace php\src\tools\lti;

define("LTI_VERSION", "LTI-1p0");
define("LTI_MESSAGE_TYPE", "basic-lti-launch-request");
define("OAUTH_CALLBACK", "about:blank");
define("OAUTH_VERSION", "1.0");
define("OAUTH_SIGNATURE_METHOD", "HMAC-SHA1");

class Lti extends \php\src\lib\Tool {

    private $launch_data = array();
    private $signature = '';

    //replace with session data
    private $toolConfig;
    private function prepareLaunchData() {
        //set person details, roles, etc.
        $this->launch_data = array(
            //"user_id" => "292832126", //nö
            //"roles" => "Instructor", // nö
            "lis_person_name_given" => $_SESSION[$this->connectorId]['user']->profile->firstName,
            "lis_person_name_family" => $_SESSION[$this->connectorId]['user']->profile->lastName,
            "lis_person_contact_email_primary" => $_SESSION[$this->connectorId]['user']->profile->email,
            "resource_link_id" => $_SESSION[$this->connectorId]['node']->node->ref->id, //chatroom id / pad id etc.,
	    "resource_link_title" => $_SESSION[$this->connectorId]['node']->node->name,
            "context_id" => "edu-sharing",
            "user_id" => $_SESSION[$this->connectorId]['user']->authorityName
        );
        $this->launch_data["lti_version"] = LTI_VERSION;
        $this->launch_data["lti_message_type"] = LTI_MESSAGE_TYPE;

        # Basic LTI uses OAuth to sign requests
        $this->addOAuthData();
        $this->setOAuthSignature();
    }

    private function setToolConfig() {
        $configObj = $this->apiClient->getNode(str_replace('workspace://SpacesStore/', '', $_SESSION[$this->connectorId]['node']->node->properties->{'ccm:tool_instance_ref'}[0]));
        $this->toolConfig = $configObj->node->properties;
    }

    private function setOAuthSignature() {
        # In OAuth, request parameters must be sorted by name
        $launch_data_keys = array_keys($this->launch_data);
        sort($launch_data_keys);
        $launch_params = array();
        foreach ($launch_data_keys as $key) {
            array_push($launch_params, $key . "=" . rawurlencode($this->launch_data[$key]));
        }
        $base_string = "POST&" . urlencode($this->toolConfig->{'ccm:tool_instance_provider_url'}[0]) . "&" . rawurlencode(implode("&", $launch_params));
        $secret = urlencode($this->toolConfig->{'ccm:tool_instance_secret'}[0]) . "&";
        $this->signature = base64_encode(hash_hmac("sha1", $base_string, $secret, true));
    }

    private function addOAuthData() {
        # OAuth Core 1.0 spec: http://oauth.net/core/1.0/
        $this->launch_data["oauth_callback"] = OAUTH_CALLBACK;
        $this->launch_data["oauth_consumer_key"] = $this->toolConfig->{'ccm:tool_instance_key'}[0];
        $this->launch_data["oauth_version"] = OAUTH_VERSION;
        $this->launch_data["oauth_nonce"] = uniqid('', true);
        $now = new \DateTime();
        $this->launch_data["oauth_timestamp"] = $now->getTimestamp();
        $this->launch_data["oauth_signature_method"] = OAUTH_SIGNATURE_METHOD;
    }

    private function renderLaunchForm() {
        echo '<html>
            <head>
            </head>
            <body onload="document.ltiLaunchForm.submit();">
                <form id="ltiLaunchForm" name="ltiLaunchForm" method="POST" action="'.$this->toolConfig->{'ccm:tool_instance_provider_url'}[0].'">';
                    foreach ($this->launch_data as $k => $v ) {
                        echo '<input type="hidden" name="' . $k  . '" value="' . $v . '">';
                    }
                    echo '<input type="hidden" name="oauth_signature" value="' . $this->signature . '">';
                    echo '<button id="submitter" type="submit">Launch</button>
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
