<?php
namespace connector\tools\wordpress;

use connector\lib\EduRestClient;

require_once __DIR__ . '/../../../config.php';

class Wordpress extends \connector\lib\Tool {


    private $createdPage = '';

    public function run() {
        $this->redirectToWp( $this->createPage() );
        //$this->displayIframe( $this->createPage() );
    }

    private function redirectToWp($wp_page_id){
        $url = WP_URL.'wp-login.php?user='.WP_USER.'&pw='.WP_PW.'&page='.$wp_page_id;
        header("Location: ".$url);
        exit();
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
                        <pre>'.WP_URL.'wp-login.php?user='.WP_USER.'&pw='.WP_PW.'&page='.$wp_page_id.'</pre>
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

        $url = WP_URL . 'wp-json/wp/v2/uploadathon/'.$id;
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
        $node = $_SESSION[$this->connectorId]['node'];
        $nodeId = $node->node->ref->id;
        $ticket = $_SESSION[$this->connectorId]['ticket'];

        $client = new EduRestClient($this->connectorId);
        $fileContent = json_decode($client->getContent($node));

        //$this->log->info('$fileContent->id: '.$fileContent->id);
        //$this->log->info(print_r($fileContent, true));

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
            "title" => $node->node->name,
            "content" => $wpContent,
            "status" => 'publish',
            "meta" => array(
                'eduConnector' => array(
                    'nodeID' => $nodeId,
                    'ticket' => $ticket,
                    'repoUrl' => $_SESSION[$this->connectorId]['api_url']
                ),
            )
        );
        $data_string = json_encode($data);

        $url = WP_URL.'wp-json/wp/v2/uploadathon/';
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

        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        //$this->log->info('$httpcode: '.$httpcode);

        curl_close($curl);

        $this->createdPage = json_decode($response);
        $this->log->info('Created wordpress-page with ID: '.$this->createdPage->id);
        return $this->createdPage->id;
    }

}
