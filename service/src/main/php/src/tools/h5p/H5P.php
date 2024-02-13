<?php
namespace connector\tools\h5p;

use connector\lib\Database;
use connector\lib\EduRestClient;
use Exception;
use Slim\Http\Response;

define('MODE_NEW', 'mode_new');

class H5P extends \connector\lib\Tool {

    protected static $instance = null;
    public $H5PFramework;
    public $H5PCore;
    public $H5PValidator;
    public $H5PStorage;
    public $H5PContentValidator;
    public $H5PEditorStorageImpl;
    public $H5PEditorAjaxImpl;
    public $H5PEditor;
    private $mode;
    private $library;
    private $parameters;
    private $metadata;
    private $h5pLang;
    private $language;
    private $logger;
    private bool $forceLibraryLoad = false;

    public function __construct($apiClient = NULL, $log = NULL, $connectorId = NULL) {

        if($apiClient && $log && $connectorId){
            parent::__construct($apiClient, $log, $connectorId);
        }
        global $db;

        $this->logger = new \connector\lib\Logger();

        $this -> h5pLang = isset($_SESSION[$connectorId]['language'])? $_SESSION[$connectorId]['language'] : 'de';
        $langPathBase = __DIR__ . '/../../../lang/' . $this -> h5pLang;
        // PHP Code Sniffer can only handle two concatenated strings and wants to see a file extension.
	    $this -> language = include $langPathBase . '.php';
        $db = new Database();
        $this->H5PFramework = new H5PFramework();
        $this->H5PCore = new \H5PCore($this->H5PFramework, $this->H5PFramework->get_h5p_path(), $this->H5PFramework->get_h5p_url(), $this -> h5pLang, true);
        $this->H5PCore->aggregateAssets = TRUE; // why not?
        $this->H5PCore->disableFileCheck = TRUE; // @needs approval

        $this->H5PValidator = new \H5PValidator($this->H5PFramework, $this->H5PCore);
        $this->H5PStorage = new \H5PStorage($this->H5PFramework, $this->H5PCore);
        $this->H5PContentValidator = new \H5PContentValidator($this->H5PFramework, $this->H5PCore);
        $this->H5PEditorStorageImpl = new H5peditorStorageImpl();
        $this->H5PEditorAjaxImpl = new H5PEditorAjaxImpl();
        $this->H5PEditor = new \H5peditor( $this->H5PCore, $this->H5PEditorStorageImpl, $this->H5PEditorAjaxImpl);

        self::$instance = $this;
    }

    public static function getInstance() {
        if (null === self::$instance)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function run(Response $response) {
        $log = $this->logger->getLog();
        $this->H5PCore->disableFileCheck = true;

        if($this->mode === MODE_NEW) {
            //$log->info('new H5P');
            $content['id'] = '';
        }else {

            if($this->H5PValidator->isValidPackage()){
                $content['language'] = $this -> h5pLang;

                $titleShow = $_SESSION[$this->connectorId]['node']->node->title;
                if (!empty($this->H5PValidator->h5pC->mainJsonData['title'])) {
                    $titleShow = $this->H5PValidator->h5pC->mainJsonData['title'];
                }
                if(empty($titleShow)){
                    $titleShow = $_SESSION[$this->connectorId]['node']->node->name;
                }

                $this->H5PStorage->savePackage(array('title' => $titleShow, 'disable' => 0));
                $content = $this->H5PCore->loadContent($this->H5PStorage->contentId);
                $this->library = $this->H5PCore->libraryToString($content['library']);

                $this->parameters = htmlentities($content['params']);
                $this->metadata = htmlentities(json_encode($content['metadata']));

                htmlentities($this->H5PCore->filterParameters($content));
                //copy media to editor
                $this->copyr($this->H5PFramework->get_h5p_path().'/content/'.$content['id'], $this->H5PFramework->get_h5p_path().'/editor/');
                $_SESSION[$this->connectorId]['viewContentId'] = $content['id'];
            }else{
                $h5p_error_array = array_values($this->H5PFramework->getMessages('error'));
                $h5p_error = end($h5p_error_array);
                $log->error('eduConnector: There was a problem with the H5P-file: '.$h5p_error->code);
            }

        }
        $this->showEditor($content, $response);
    }

    private function copyr($source, $dest) {
        if(is_file($source) && basename($source) == 'content.json')
            return true;

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            if ($dest !== "$source/$entry") {
                $this->copyr("$source/$entry", "$dest/$entry");
            }
        }

        // Clean up
        $dir->close();
        return true;
    }


    /**
     * @throws Exception
     */
    public function rrmdir(string $dir, bool $throwException = false): void {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object))
                        $this -> rrmdir($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            $isDirDeleted = rmdir($dir);
            ! $isDirDeleted && $throwException && throw new Exception('Cannot delete directory: ' . $dir . '.');
        }
    }


    public function showEditor($content, Response $response) {
        $integration = array();
        $integration['baseUrl'] = WWWURL;
        //$integration['url'] = '/eduConnector/src/tools/h5p';
        $integration['url'] = WWWURL . '/src/tools/h5p/cache';
        $integration['siteUrl'] = WWWURL;
        $integration['postUserStatistics'] = '';
        $integration['ajax'] = array();
        $integration['saveFreq'] = false;
        $integration['l10n'] = array('H5P' => $this->H5PCore->getLocalization());
        $integration['hubIsEnabled'] = true;
        $integration['user'] = array();
        $integration['core'] = array('style'=>\H5PCore::$styles, 'scripts'=>\H5PCore::$scripts);
        $integration['loadedJs'] = '';
        $integration['loadedCss'] = '';
        //$integration['editor']['filesPath'] = WWWURL . '/src/tools/h5p/editor';
        $integration['editor']['filesPath'] = WWWURL . '/src/tools/h5p/cache/editor';
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
        $integration['editor']['assets']['js'][] = WWWURL . '/vendor/h5p/h5p-editor/language/'.$this -> h5pLang.'.js';

        $integration['editor']['deleteMessage'] = 'Soll das wirklich geloescht werden?';
        $integration['editor']['apiVersion'] = \H5PCore::$coreApi;
        $integration['editor']['nodeVersionId'] = $this->H5PStorage->contentId;
        $integration['editor']['metadataSemantics'] = $this->H5PContentValidator->getMetadataSemantics();

        //set visibility of lib selector here!
        if(isset($_SESSION[$this->connectorId]['defaultCreateElement']) && $this->mode !== MODE_NEW) {
            $integration['editor']['hideHub'] = true;
        }

        echo '<script>'.
            'window.H5PIntegration='. json_encode($integration).
            '</script>';
        $styles = [
            WWWURL . '/css/h5p.css'
        ];
        foreach(\H5PCore::$styles as $style) {
           $styles[] = WWWURL . '/vendor/h5p/h5p-core/' . $style;
        }
        foreach(\H5PEditor::$styles as $style) {
            $styles[] = WWWURL . '/vendor/h5p/h5p-editor/' . $style;
        }
        $scripts = [];
        foreach (\H5PCore::$scripts as $script) {
            $scripts[] = WWWURL . '/vendor/h5p/h5p-core/' . $script;
        }
        foreach (\H5PEditor::$scripts as $script) {
            $scripts[] = WWWURL . '/vendor/h5p/h5p-editor/' . $script;
        }
        $scripts[] = WWWURL . '/vendor/h5p/h5p-editor/language/'.$this -> h5pLang.'.js';
        $scripts[] = WWWURL . '/vendor/h5p/h5p-editor/scripts/h5peditor-editor.js';
        $scripts[] = WWWURL . '/src/tools/h5p/js/h5peditor-init.js';




        $titleShow = $_SESSION[$this->connectorId]['node']->node->title;
        if(empty($titleShow)){
            $titleShow = $_SESSION[$this->connectorId]['node']->node->name;
        }
        $this->container->view->render($response, 'h5p.html.twig', [
            'WWWURL' => WWWURL,
            'title' => $titleShow,
            'content' => $content,
            'library' => $this->library,
            'parameters' => $this->parameters,
            'metadata' => $this->metadata,
            'styles' => $styles,
            'nodeId' => $_SESSION[$this->connectorId]['node']->node->ref->id,
            'connectorId' => $this->connectorId,
            'scripts' => $scripts,
            'integration' => json_encode($integration),
            'language' => $this -> language
        ]);
        /*
        echo '<form method="post" enctype="multipart/form-data" id="h5p-content-form" action="'.WWWURL.'/ajax/ajax.php?title='.$_SESSION[$this->connectorId]['node']->node->ref->id.'&action=h5p_create&id='.$this->connectorId.'">';
        echo '<div class="h5pSaveBtnWrapper"><h1 class="h5pTitle">'.$titleShow.'</h1><input type="submit" name="submit" value="' . $this -> language['save'] . '" class="h5pSaveBtn btn button button-primary button-large"/></div>';
        echo '<div class="h5p-create"><div class="h5p-editor"></div></div>';
        echo '<input type="hidden" name="title" value="'.$content['title'].'">';
        echo '<input type="hidden" name="library" value="'.$this->library.'">';
        echo '<input type="hidden" name="parameters" value="'.$this->parameters.'">';
        echo '<input type="hidden" name="metadata" value="'.$this->metadata.'">';
        echo '<div class="h5pSaveBtnWrapper"><input type="submit" name="submit" value="' . $this -> language['save'] . '" class="h5pSaveBtn btn button button-primary button-large"/></div>';
        echo '</form>';
        echo '</body></html>';*/
    }

    public function setNode() {
        $node = $this->getNode();
        if ($node->node->size === NULL) {
            try {
                if(!isset($_SESSION[$this->connectorId]['defaultCreateElement']) || !file_exists(__DIR__ . '/templates/' . $_SESSION[$this->connectorId]['defaultCreateElement'] . '.h5p')){
                    throw new \Exception('Template not specified or found');
                }
                copy(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $_SESSION[$this->connectorId]['defaultCreateElement'] . '.h5p', $this->H5PFramework->getUploadedH5pPath());
	    } catch (\Exception $e) {
               $this->mode = MODE_NEW;
            }
        } else {
            $client = new EduRestClient($this->connectorId);
            $client->getContent($node, null, true);
        }
        $_SESSION[$this->connectorId]['node'] = $node;
    }

    /**
     * Function enableForceLibraryLoad
     *
     * Calling this function will force the H5P Framework implementation to reload all libraries
     *
     * @return void
     */
    public function enableForceLibraryLoad(): void {
        $this->H5PFramework->setForceLibraryLoad(true);
    }
}
