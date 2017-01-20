<?php

require_once 'config.php';
require_once 'EduRestClient.php';

class MoodleConnector extends EduRestClient {
	    
    private $nodeId = '';
    private $categoryId = '';
    private $title = '';
    
    public function __construct() {
        if(empty($_REQUEST['nodeid'])) {
            echo 'Node id empty';
            exit(0);
        }
        
        $this -> nodeId = $_REQUEST['nodeid'];
        
        if(!empty($_REQUEST['categoryid']) && !empty($_REQUEST['title'])) {
            $this -> categoryId = $_REQUEST['categoryid'];
            $this -> title = $_REQUEST['title'];
        }
    }
    
    public function run() {

        if(!empty($this -> categoryId) && $_REQUEST['import'] =='yes') {
            
            $courseParams = $this -> restore();       
            
            if(!empty($courseParams)) {
           		$courseUrl = MOODLEURL . '/course/view.php?id=' . $courseParams -> id;
				$this -> createReference(self::TOOL, $this -> title, $courseUrl);
                header('HTTP/1.1 303 See other');
            	header('Location: ' . $courseUrl);
            } else {
            	echo 'Error creating course';
            }
            
            exit(0);
        }
        
        $this -> showDialog();
    }
    
    public function showDialog() {
        include('./view/moodle.phtml');
    }
    
    public function restore() {
        $url = MOODLEURL . "/webservice/rest/server.php?wsfunction=local_educopu_restore&moodlewsrestformat=json&wstoken=" . MOODLETOKEN;
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        $params = array('nodeid'=> $this -> nodeId,'category' => $this -> categoryId, 'title' => htmlentities($this -> title));
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
        $resp = curl_exec ( $ch );
        $httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
        curl_close($ch);
        if ($httpcode >= 200 && $httpcode < 300) {
			return json_decode(json_decode($resp));
        }
        echo 'Error pushing course<br/>';
        return false;
    }
    
    public function getCategories() {
        $url = MOODLEURL . "/webservice/rest/server.php?wsfunction=local_educopu_getcategories&moodlewsrestformat=json&wstoken=" . MOODLETOKEN;
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
        $resp = curl_exec ( $ch );
        $httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
        curl_close ( $ch );
        if ($httpcode >= 200 && $httpcode < 300) {
            // double decoding needed because of double encoding :p
            return json_decode ( json_decode ( $resp, true ), true);    
        }
        echo 'Error fetching categories';
    }
    
    public function renderCategories() {
        $categories = $this -> getCategories();

        $this -> renderNestedList($categories);

        return;
        /*echo '<select name="categoryid">';
        echo $this -> renderSelectboxOptions($categories);
        echo '</select>';*/
    }
    /*
    public function renderSelectboxOptions($categories, $level = -1) {
        $level += 1;
        foreach($categories as $id => $cat) {
            echo '<option class="lvl' . $level . '" value="' . $cat['id'] . '">' . str_repeat('', $level) . $cat['name'] . '</option>';
            if(is_array($cat['children'])) {
                $this -> renderSelectboxOptions($cat['children'], $level);
            }
        }
    }*/
    
    public function renderNestedList($categories) {
        echo '<ul>';
        foreach($categories as $id => $cat) {
            $class = '';
            //echo '<li><a href="?nodeid='.$this -> nodeId.'&categoryid=' . $cat['id'] . '&import=yes&tool=' . self::TOOL . '">' . $cat['name'] . '</a>';
            if(is_array($cat['children']))
                $class = 'folder';
            echo '<li><span data-catid="' . $cat['id'] . '" class="toggler ' . $class . '"><span>' . $cat['name'] . '</span></span>';
            if(is_array($cat['children'])) {
                $this -> renderNestedList($cat['children']);
            }
        }
        echo '</ul>';
    }
}
