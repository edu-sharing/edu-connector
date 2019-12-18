<?php
namespace connector\tools\moodle;
use connector\lib\EduRestClient;
use Exception;

class Moodle extends \connector\lib\Tool {

    private $courseId;

    public function run() {
        //error_log('forward-url: '.$this->getForwardUrl());
        $this->restoreCourse();
        $this->forwardToMoodle();
    }

    private function restoreCourse() {

        $apiClient = new EduRestClient($this->connectorId);
        $nodeId = $_SESSION[$this->connectorId]['node']->node->ref->id;

        //error_log('#########ccm:wwwurl: '.$_SESSION[$this->connectorId]['node']->node->properties->{'ccm:wwwurl'}[0], 3, '/var/cache/eduConnector/log/moodleCon.log');

        if ($_SESSION[$this->connectorId]['node']->node->properties->{'ccm:wwwurl'}[0] === NULL){
            $url = MOODLE_BASE_DIR . "/webservice/rest/server.php?wsfunction=local_edusharing_createempty&moodlewsrestformat=json&wstoken=" . MOODLE_TOKEN;
        }
        else {
            //$url = MOODLE_BASE_DIR . "/webservice/rest/server.php?wsfunction=local_edusharing_restore&moodlewsrestformat=json&wstoken=" . MOODLE_TOKEN;
            return true;
        }

        $ch = curl_init ();
        error_log('url: '.$url);
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        $params = array('nodeid'=> $_SESSION[$this->connectorId]['node']->node->ref->id,'category' => '1', 'title' => htmlentities(str_replace('.mbz', '', $_SESSION[$this->connectorId]['node']->node->name)));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($ch);
        //error_log('response:'.$resp);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode >= 200 && $httpcode < 300 && strpos($resp, 'exception') === false) {
            $resp = str_replace('<?php', '', $resp); // moodle reponse sometimes contains '<?php' for some reason
            $courseId = json_decode($resp);

            if(!is_numeric($courseId)) {
                throw new \Exception('Moodle course id is not numeric');
            }
            $this->courseId = $courseId;

            $url = MOODLE_BASE_DIR .'/course/view.php?id='.$courseId;
            $_SESSION[$this->connectorId]['node']->node->properties->{'ccm:wwwurl'}[0] = $url;
            $apiClient->updateReferenceUrl($nodeId, $url);
            //error_log('#########ccm:wwwurl: '.$_SESSION[$this->connectorId]['node']->node->properties->{'ccm:wwwurl'}[0], 3, '/var/cache/eduConnector/log/moodleCon.log');

            return true;
        }
        throw new \Exception('Cannot create course');
    }


    private function forwardToMoodle() {
        $url = $this->getForwardUrl();
        header('Location: ' . $url);
        exit();
    }

    /*
	 * Call moodle WS local_edusharing_handleuser
	 * create/fetch user
	 * enroll user
	 * retrieve token for login
	 * */
    private function getUserToken() {
        $url = MOODLE_BASE_DIR . "/webservice/rest/server.php?wsfunction=local_edusharing_handleuser&moodlewsrestformat=json&wstoken=" . MOODLE_TOKEN;
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        $params = array('user_name' => htmlentities($_SESSION[$this->connectorId]['user']->authorityName),
            'user_givenname' => htmlentities($_SESSION[$this->connectorId]['user']->profile->firstName),
            'user_surname' => htmlentities($_SESSION[$this->connectorId]['user']->profile->lastName),
            'user_email' => htmlentities($_SESSION[$this->connectorId]['user']->profile->email) ,
            'courseid' => $this->courseId, 'role' => 'editingteacher'); // or role 'editingteacher'
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode >= 200 && $httpcode < 300 && strpos($resp, 'exception') === false) {
            return json_decode($resp);
        }

        //$logger->error('Error retrieving user token - ' . $httpcode . ' ' . json_decode($resp)->exception);
        return false;
    }


    public function getForwardUrl() {
        if ($_SESSION[$this->connectorId]['node']->node->properties->{'ccm:wwwurl'}[0]){
            return $_SESSION[$this->connectorId]['node']->node->properties->{'ccm:wwwurl'}[0];
        }else{
            return MOODLE_BASE_DIR . '/local/edusharing/forwardUser.php?token=' . urlencode($this-> getUserToken());
        }
    }
}