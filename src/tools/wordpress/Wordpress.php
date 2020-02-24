<?php
namespace connector\tools\wordpress;

use connector\lib\EduRestClient;

require_once __DIR__ . '/../../../config.php';

class Wordpress extends \connector\lib\Tool {


    private $createdPage = '';

    public function run() {
        $this->displayIframe( $this->createPage() );
    }

    private function displayIframe($wp_page_id){
        echo '
            <!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">            
                    <title>Wordpress Connector Test</title>            
                    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700&display=swap" rel="stylesheet">
                    <link rel="stylesheet" href="'.WWWURL.'/src/tools/wordpress/style.css" />
                </head>
                <body>
                    <div class="h5p-header">
                        <h1>Wordpress Connector Test</h1>
                    </div>
                
                    <div class="wrap">
                        <iframe src="'.WP_URL.'wp-login.php?user='.WP_USER.'&pw='.WP_PW.'&page='.$wp_page_id.'" width="100%" height="100%" frameBorder="0"></iframe>
                    </div>
                </body>
            </html>
        ';
    }

    protected function checkWpPage($id){

        if(empty($id)){
            $this->log->warn('no wordpress-id found');
            return false;
        }

        $url = WP_URL . 'wp-json/wp/v2/pages/'.$id;
        $auth = WP_USER.':'.WP_PW;
        $response = '';
        try {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERPWD, $auth);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Accept: application/json',
                    'Content-Type: application/json'
                )
            );
            $response = curl_exec($curl);
            if($response === false) {
                trigger_error(curl_error($curl), E_USER_WARNING);
                return false;
            }
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return false;
        }
        curl_close($curl);

        $response = json_decode($response);
        //error_log('RESPONSE: '.print_r($response, true));
        if ($response->id == $id && $response->status == 'publish'){
            $this->log->info('wordpress-page found: '.$response->id);
            return true;
        }
        $this->log->warn('no wordpress-page found');
        return false;
    }

    private function createPage(){
        $node = $_SESSION[$this->connectorId]['node']->node;
        $nodeId = $node->ref->id;
        $ticket = $_SESSION[$this->connectorId]['ticket'];

        $fileContent = $this->downloadContent($nodeId, $ticket);

        if ($this->checkWpPage($fileContent->id)){
            //Page found, no need to create one.
            $this->log->info('WORDPRESS_PageId: '.$fileContent->id);
            return $fileContent->id;
        }

        $wpContent = '';
        if(!empty($fileContent->content)){
            $wpContent = $fileContent->content;
        }

        $data = array(
            "title" => $node->name,
            "content" => $wpContent,
            "status" => 'publish',
            "meta" => array(
                'eduConnector' => array(
                    'nodeID' => $nodeId,
                    'ticket' => $ticket,
                    'repoUrl' => $_SESSION[$this->connectorId]['api_url']
                )
            )
        );
        $data_string = json_encode($data);

        $url = WP_URL.'wp-json/wp/v2/pages/';
        $auth = WP_USER.':'.WP_PW;

        $response = '';
        try {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERPWD, $auth);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );
            $response = curl_exec($curl);
            if($response === false) {
                trigger_error(curl_error($curl), E_USER_WARNING);
            }
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        curl_close($curl);

        $this->createdPage = json_decode($response);
        $this->log->info('Created wordpress-page with ID: '.$this->createdPage->id);
        return $this->createdPage->id;
    }

    protected function downloadContent($nodeId, $ticket){

        error_log('ApiUrl: '.$_SESSION[$this->connectorId]['api_url']);

        $downloadUrl = 'http://localhost:8080/edu-sharing/eduservlet/download?nodeId='.$nodeId.'&ticket='.$ticket;
        $content = '';
        try {
            $ch = curl_init($downloadUrl);
            $headers = array(
                'Accept: application/json',
                'Content-Type: multipart/form-data'
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $res = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode >= 200 && $httpcode < 308) {
                curl_close($ch);
                $content = json_decode($res);
            }
        } catch (Exception $e) {
            $this->log->error('Could not load textContent: '.$e->getMessage());
        }

        return $content;
    }

    protected function loadTextContent($nodeId, $ticket){
        $currentFileUrl = $_SESSION[$this->connectorId]['api_url'].'node/v1/nodes/-home-/'.$nodeId.'/textContent';
        $currentFile = '';
        try {
            $ch = curl_init($currentFileUrl);
            $headers = array(
                'Authorization: EDU-TICKET '.$ticket,
                'Accept: application/json',
                'Content-Type: multipart/form-data'
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $res = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            var_dump('$httpcode: '.$httpcode);
            var_dump('$res: '.$res);
            if ($httpcode >= 200 && $httpcode < 308) {
                $currentFile = json_decode($res);
                curl_close($ch);
            }
        } catch (Exception $e) {
            $this->log->error('Could not load textContent: '.$e->getMessage());
        }

        return json_decode($currentFile->raw);
    }

}
