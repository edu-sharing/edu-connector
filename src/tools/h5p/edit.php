<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

class mod_h5p
{
    private $H5PFramework;
    private $H5PCore;
    private $H5PValidator;
    private $H5peditorStorageImpl;
    private $H5PEditorAjaxImpl;
    private $H5PEditor;
    private static $settings = array();
    private $library = '';
    private $parameters = '';
    private $title = 'Neuer Titel';

    public function __construct()
    {
        global $db;
        $db = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPASSWORD);
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $this->H5PFramework = new connector\tools\h5p\H5PFramework();
        $this->H5PCore = new H5PCore($this->H5PFramework, $this->H5PFramework->get_h5p_path(), $this->H5PFramework->get_h5p_url(), LANG, false);
        $this->H5PCore->aggregateAssets = TRUE; // why not?
        $this->H5PValidator = new H5PValidator($this->H5PFramework, $this->H5PCore);
        $this->H5PContentValidator = new H5PContentValidator($this->H5PFramework, $this->H5PCore);
        $this->H5peditorStorageImpl = new connector\tools\h5p\H5peditorStorageImpl();
        $this->H5PEditorAjaxImpl = new connector\tools\h5p\H5PEditorAjaxImpl();
        $this->H5PEditor = new \H5peditor( $this->H5PCore, $this->H5peditorStorageImpl, $this->H5PEditorAjaxImpl);
    }

    public function run()
    {

        $this->render();
    }

    public function setContent() {
        $contentId = $_GET['h5p'];
        if(!is_numeric($_GET['h5p'])) {
            echo 'invalid id - perhaps empty (demo content)';
            exit();
        }
        $content = $this->H5PCore->loadContent($contentId);
        $content['language'] = 'de';
        $this->title = $content['title'];
        $this->library = H5PCore::libraryToString($content['library']);
        $this->parameters = htmlentities($this->H5PCore->filterParameters($content));
    }


    private function render()
    {

        echo '<html><head>';

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
        $integration['core'] = array('style'=>H5PCore::$styles, 'scripts'=>H5PCore::$scripts);
        $integration['loadedJs'] = '';
        $integration['loadedCss'] = '';

        $integration['editor']['filesPath'] = '/h5p/editor';
        $integration['editor']['fileIcon'] = '';
        $integration['editor']['ajaxPath'] = WWWURL . '/ajax/ajax.php?action=h5p_';
        $integration['editor']['libraryUrl'] = WWWURL . '/vendor/h5p/h5p-editor/';
        $integration['editor']['copyrightSemantics'] = $this->H5PContentValidator ->getCopyrightSemantics();

        foreach(H5PCore::$styles as $b) {
            $integration['editor']['assets']['css'][] = WWWURL . '/vendor/h5p/h5p-core/' . $b;
        }
        foreach(H5PEditor::$styles as $b) {
            $integration['editor']['assets']['css'][] = WWWURL . '/vendor/h5p/h5p-editor/' . $b;
        }


        foreach(H5PCore::$scripts as $b) {
            $integration['editor']['assets']['js'][] = WWWURL . '/vendor/h5p/h5p-core/' . $b;
        }
        foreach(H5PEditor::$scripts as $b) {
            $integration['editor']['assets']['js'][] = WWWURL . '/vendor/h5p/h5p-editor/' . $b;
        }

        $integration['editor']['assets']['js'][] = WWWURL . '/vendor/h5p/h5p-editor/language/'.LANG.'.js';

        $integration['editor']['deleteMessage'] = 'soll das echt geloescht werden?';
        $integration['editor']['apiVersion'] = $this->H5PCore::$coreApi;

        echo '<script>'.
            'window.H5PIntegration='. json_encode($integration).
            '</script>';


        foreach(H5PCore::$styles as $style) {
            echo '<link rel="stylesheet" href="' . WWWURL . '/vendor/h5p/h5p-core/' . $style . '"> ';
        }
        foreach(H5PEditor::$styles as $style) {
            echo '<link rel="stylesheet" href="' . WWWURL . '/vendor/h5p/h5p-editor/' . $style . '"> ';
        }
        foreach (H5PCore::$scripts as $script) {
            echo '<script src="' . WWWURL . '/vendor/h5p/h5p-core/' . $script . '"></script> ';
        }
        foreach (H5PEditor::$scripts as $script) {
            echo '<script src="' . WWWURL . '/vendor/h5p/h5p-editor/' . $script . '"></script> ';
        }

        //echo '<script src="'.WWWURL.'/src/tools/h5p/js/editor.js"></script>';
        echo '</head><body>';

        echo '<form method="post" enctype="multipart/form-data" id="h5p-content-form" action="'.WWWURL.'/ajax/ajax.php?action=h5p_create&id='.$_GET['id'].'">';

        echo '<input type="title" name="title" value="'.$this->title.'">';
        echo '<input type="submit" name="submit" value="save" class="button button-primary button-large"/>';

        echo '<div class="h5p-create"><div class="h5p-editor"></div></div>';

        echo '<input type="hidden" name="library" value="'.$this->library.'">';
       echo '<input type="hidden" name="parameters" value="'.$this->parameters.'">';

        echo '</form>';
        echo '</body></html>';
    }

}

$mod = new mod_h5p();
if(isset($_GET['h5p'])) {
    $mod->setContent();
}
$mod->run();