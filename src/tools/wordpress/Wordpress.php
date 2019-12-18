<?php
namespace connector\tools\wordpress;
class Wordpress extends \connector\lib\Tool {

    private $courseId;

    public function run() {
        $this->restoreCourse();
        $this->forwardToMoodle();
    }

    private function restoreCourse() {

        if ($_SESSION[$this->connectorId]['node']->node->size === NULL)
            $url = MOODLE_BASE_DIR . "/webservice/rest/server.php?wsfunction=local_edusharing_createempty&moodlewsrestformat=json&wstoken=" . MOODLE_TOKEN;
        else
            $url = MOODLE_BASE_DIR . "/webservice/rest/server.php?wsfunction=local_edusharing_restore&moodlewsrestformat=json&wstoken=" . MOODLE_TOKEN;

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        $params = array('nodeid'=> $_SESSION[$this->connectorId]['node']->node->ref->id,'category' => '1', 'title' => htmlentities(str_replace('.mbz', '', $_SESSION[$this->connectorId]['node']->node->name)));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode >= 200 && $httpcode < 300 && strpos($resp, 'exception') === false) {
            $resp = str_replace('<?php', '', $resp); // moodle reponse sometimes contains '<?php' for some reason
            $courseId = json_decode($resp);
            if(!is_numeric($courseId)) {
                throw new \Exception('Moodle course id is not numeric');
            }
            $this->courseId = $courseId;
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


    protected function getForwardUrl() {
        return MOODLE_BASE_DIR . '/local/edusharing/forwardUser.php?token=' . urlencode($this-> getUserToken());
    }
}
