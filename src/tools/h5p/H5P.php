<?php

namespace connector\tools\h5p;

define('MODE_NEW', 'mode_new');

class H5P extends \connector\lib\Tool {

    private $showLibSelect = false;

    private $H5PFramework;
    private $H5PCore;
    private $H5PValidator;
    private $H5PStorage;
    private $H5PContentValidator;
    private $H5peditorStorageImpl;
    private $H5PEditorAjaxImpl;
    private $H5PEditor;
    private $mode;


    //private static $settings = array();
    private $library;
    private $parameters;

    public function __construct($apiClient, $log, $connectorId) {
        parent::__construct($apiClient, $log, $connectorId);
        global $db;
        $db = new \PDO('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASSWORD);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->H5PFramework = new H5PFramework();
        $this->H5PCore = new \H5PCore($this->H5PFramework, $this->H5PFramework->get_h5p_path(), $this->H5PFramework->get_h5p_url(), LANG, false);
        $this->H5PCore->aggregateAssets = TRUE; // why not?
        $this->H5PValidator = new \H5PValidator($this->H5PFramework, $this->H5PCore);
        $this->H5PStorage = new \H5PStorage($this->H5PFramework, $this->H5PCore);
        $this->H5PContentValidator = new \H5PContentValidator($this->H5PFramework, $this->H5PCore);
        $this->H5peditorStorageImpl = new H5peditorStorageImpl();
        $this->H5PEditorAjaxImpl = new H5PEditorAjaxImpl();
        $this->H5PEditor = new \H5peditor( $this->H5PCore, $this->H5peditorStorageImpl, $this->H5PEditorAjaxImpl);
    }

    public function run() {
        $this->H5PCore->disableFileCheck = true;
        $this->H5PValidator->isValidPackage();
        $content['language'] = 'de';
        if($this->mode === MODE_NEW) {
            $content['id'] = 0;
        } else {
             $this->H5PStorage->savePackage(array('title' => $_SESSION[$this->connectorId]['node']->node->ref->id, 'disable' => 0));
             $content = $this->H5PCore->loadContent($this->H5PFramework->id);
             $this->library = \H5PCore::libraryToString($content['library']);
             $this->parameters = htmlentities($this->H5PCore->filterParameters($content));
        }
        $this->showEditor($content['id']);
    }

    public function showEditor($contentId) {
        echo '<html><head><meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $integration = array();
        $integration['baseUrl'] = WWWURL;
        $integration['url'] = '/' . PATH;
        $integration['siteUrl'] = WWWURL;
        $integration['postUserStatistics'] = '';
        $integration['ajax'] = array();
        $integration['saveFreq'] = false;
        $integration['l10n'] = array('H5P' => $this->H5PCore->getLocalization());
        $integration['hubIsEnabled'] = false;
        $integration['user'] = array();
        $integration['core'] = array('style'=>\H5PCore::$styles, 'scripts'=>\H5PCore::$scripts);
        $integration['loadedJs'] = '';
        $integration['loadedCss'] = '';
        $integration['editor']['filesPath'] = DOMAIN . PATH . '/editor';
        $integration['editor']['fileIcon'] = '';
        $integration['editor']['ajaxPath'] = WWWURL . '/ajax/ajax.php?action=h5p_';
        $integration['editor']['libraryUrl'] = WWWURL . '/vendor/h5p/h5p-editor/';
        $integration['editor']['copyrightSemantics'] = $this->H5PContentValidator ->getCopyrightSemantics();
        foreach(\H5PCore::$styles as $b) {
            $integration['editor']['assets']['css'][] = WWWURL . '/vendor/h5p/h5p-core/' . $b;
        }
        foreach(\H5PEditor::$styles as $b) {
            $integration['editor']['assets']['css'][] = WWWURL . '/vendor/h5p/h5p-editor/' . $b;
        }
        foreach(\H5PCore::$scripts as $b) {
            $integration['editor']['assets']['js'][] = WWWURL . '/vendor/h5p/h5p-core/' . $b;
        }
        foreach(\H5PEditor::$scripts as $b) {
            $integration['editor']['assets']['js'][] = WWWURL . '/vendor/h5p/h5p-editor/' . $b;
        }
        $integration['editor']['assets']['js'][] = WWWURL . '/vendor/h5p/h5p-editor/language/'.LANG.'.js';

        $integration['editor']['assets']['js'][] = WWWURL . '/src/tools/h5p/js/custom.js';
        $integration['editor']['assets']['css'][] = WWWURL . '/src/tools/h5p/style/custom.css';

        $integration['editor']['deleteMessage'] = 'soll das echt geloescht werden?';
        $integration['editor']['apiVersion'] = \H5PCore::$coreApi;
        $integration['editor']['nodeVersionId'] = $contentId;

        echo '<link rel="stylesheet" href="' . WWWURL . '/css/h5p.css"> ';

        echo '<script>'.
            'window.H5PIntegration='. json_encode($integration).
            '</script>';
        foreach(\H5PCore::$styles as $style) {
            echo '<link rel="stylesheet" href="' . WWWURL . '/vendor/h5p/h5p-core/' . $style . '"> ';
        }
        foreach(\H5PEditor::$styles as $style) {
            echo '<link rel="stylesheet" href="' . WWWURL . '/vendor/h5p/h5p-editor/' . $style . '"> ';
        }
        foreach (\H5PCore::$scripts as $script) {
            echo '<script src="' . WWWURL . '/vendor/h5p/h5p-core/' . $script . '"></script> ';
        }
        foreach (\H5PEditor::$scripts as $script) {
            echo '<script src="' . WWWURL . '/vendor/h5p/h5p-editor/' . $script . '"></script> ';
        }

                echo '<script src="'.WWWURL.'/src/tools/h5p/js/editor.js"></script>';
        echo '</head><body>';

        $titleShow = $_SESSION[$this->connectorId]['node']->node->title;
        if(empty($titleShow))
            $titleShow = $_SESSION[$this->connectorId]['node']->node->name;

        echo '<form method="post" enctype="multipart/form-data" id="h5p-content-form" action="'.WWWURL.'/ajax/ajax.php?title='.$_SESSION[$this->connectorId]['node']->node->ref->id.'&action=h5p_create&id='.$this->connectorId.'">';
        echo '<div class="h5pSaveBtnWrapper"><h1 class="h5pTitle">'.$titleShow.'</h1><input type="submit" name="submit" value="Speichern" class="h5pSaveBtn btn button button-primary button-large"/></div>';
        echo '<div class="h5p-create"><div class="h5p-editor"></div></div>';
        echo '<input type="hidden" name="library" value="'.$this->library.'">';
        echo '<input type="hidden" name="parameters" value="'.$this->parameters.'">';
        echo '<div class="h5pSaveBtnWrapper"><input type="submit" name="submit" value="Speichern" class="h5pSaveBtn btn button button-primary button-large"/></div>';
        echo '</form>';
        echo '</body></html>';
    }

    public function setNode() {
        $node = $this->getNode();

        $this->H5PFramework->uploadedH5pFolderPath = __DIR__ . '/temp/' .$node->node->ref->id;
        $this->H5PFramework->uploadedH5pPath = $this->H5PFramework->uploadedH5pFolderPath . '/'.$node->node->ref->id . '.h5p';

        if ($node->node->size === NULL) {
                try {
                    if(!isset($_SESSION[$this->connectorId]['defaultCreateElement']) || !file_exists(__DIR__ . '/templates/' . $_SESSION[$this->connectorId]['defaultCreateElement'] . '.h5p'))
                        throw new \Exception('Template not specified or found');
                    @mkdir(__DIR__ . '/temp');
                    @mkdir(__DIR__ . '/temp/' . $node->node->ref->id);
                    copy(__DIR__ . '/templates/' . $_SESSION[$this->connectorId]['defaultCreateElement'] . '.h5p', $this->H5PFramework->uploadedH5pPath);
                } catch (\Exception $e) {
                    $this->mode = MODE_NEW;
                }
        } else {
            if(defined('FORCE_INTERN_COM') && FORCE_INTERN_COM) {
                $arrApiUrl = parse_url($_SESSION[$this->connectorId]['api_url']);
                $arrContentUrl = parse_url($node->node->contentUrl);
                $contentUrl = $arrApiUrl['scheme'].'://'.$arrApiUrl['host'].':'.$arrApiUrl['port'].$arrContentUrl['path'].'?'.$arrContentUrl['query'] . '&com=internal';
                $curlHeader = array('Cookie:JSESSIONID=' . $_SESSION[$this->connectorId]['sessionId']);
                $url = $contentUrl . '&params=display%3Ddownload';
            } else {
                $contentUrl = $node->node->contentUrl;
                $curlHeader = array();
                $url = $contentUrl . '&ticket=' . $_SESSION[$this->connectorId]['ticket'] . '&params=display%3Ddownload';
            }
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeader);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            $data = curl_exec($curl);
            @mkdir(__DIR__ . '/temp');
            @mkdir(__DIR__ . '/temp/' .$node->node->ref->id);
            $fp = fopen($this->H5PFramework->uploadedH5pPath, 'w');
            fwrite($fp, $data);
            fclose($fp);
            curl_close($curl);
        }
        $node = $this->getNode();
        $_SESSION[$this->connectorId]['node'] = $node;
    }
}
