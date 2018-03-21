<?php

require_once __DIR__ . '/vendor/autoload.php';

class mod_h5p {

    private $H5PFramework;
    private $H5PCore;
    private $H5PValidator;
    private $H5PStorage;
    private static $settings = array();

    public function __construct() {
        if(isset($_GET['h5p']))
            $this->testfile = $_GET['h5p'];
        global $db;
        $db = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPASSWORD);
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

        $this->H5PFramework = new H5PFramework();
        $this->H5PCore = new H5PCore($this->H5PFramework, $this->H5PFramework->get_h5p_path(), $this->H5PFramework->get_h5p_url(), LANG, false);
        $this->H5PCore->aggregateAssets = TRUE; // why not?
        $this->H5PValidator = new H5PValidator($this->H5PFramework, $this->H5PCore);
        $this->H5PStorage = new H5PStorage($this->H5PFramework, $this->H5PCore);
    }

    public function run() {
        @mkdir($this->H5PFramework->get_h5p_path());
        mkdir($this->H5PFramework->get_h5p_path() . DIRECTORY_SEPARATOR . md5($this->testfile));
        copy('test' . DIRECTORY_SEPARATOR . $this->testfile, $this->H5PFramework->get_h5p_path() . DIRECTORY_SEPARATOR . md5($this->testfile) . DIRECTORY_SEPARATOR . $this->testfile);
        $this->H5PFramework->uploadedH5pFolderPath = $this->H5PFramework->get_h5p_path() . DIRECTORY_SEPARATOR . md5($this->testfile);
        $this->H5PFramework->uploadedH5pPath = $this->H5PFramework->get_h5p_path() . DIRECTORY_SEPARATOR . md5($this->testfile) . DIRECTORY_SEPARATOR . $this->testfile;
        $this->H5PCore->disableFileCheck = true;
        $this->H5PValidator->isValidPackage();
        $this->H5PStorage->savePackage(array('title' => 'ein titel', 'disable' => 0));
        $content = $this->H5PCore->loadContent($this->H5PFramework->id);

        $this->add_assets($content);
        $this->render($content['id']);
    }


    private function add_assets($content) {

        // Add core assets
        $this->add_core_assets();

        $cid = 'cid-' . $this->H5PFramework->id;
        if (!isset(self::$settings['contents'][$cid])) {
            self::$settings['contents'][$cid] = $this->get_content_settings($content);

            // Get assets for this content
           $preloaded_dependencies = $this -> H5PCore ->loadContentDependencies($content['id'], 'preloaded');

           $files = $this -> H5PCore -> getDependenciesFiles($preloaded_dependencies);

            self::$settings['contents'][$cid]['scripts'] = $this -> H5PCore->getAssetsUrls($files['scripts']);
            self::$settings['contents'][$cid]['styles'] = $this -> H5PCore->getAssetsUrls($files['styles']);
        }
    }

    private function render($contentId) {

        echo '<html><head>';

        echo '<script>window.H5PIntegration='. json_encode(self::$settings).'</script>';

        foreach (self::$settings['core']['styles'] as $style) {
            echo '<link rel="stylesheet" href="' . DOMAIN . $style.'"> ';
        }
        foreach (self::$settings['contents']['cid-'.$contentId]['styles'] as $style) {
            echo '<link rel="stylesheet" href="'. $style.'"> ';
        }

        foreach (self::$settings['core']['scripts'] as $script) {
            echo '<script src="'. DOMAIN. $script.'"></script> ';
        }

        foreach (self::$settings['contents']['cid-'.$contentId]['scripts'] as $script) {
            echo '<script src="'.$script.'"></script> ';
        }

        echo '</head><body>';


        echo '<div class="h5p-iframe-wrapper"><iframe id="h5p-iframe-' . $contentId . '" class="h5p-iframe" data-content-id="' . $contentId . '" style="height:1px" src="about:blank" frameBorder="0" scrolling="no"></iframe></div>';

        echo '</body></html>';
    }


    private function add_core_assets() {

        if (self::$settings !== null) {
            //return; // Already added
        }

        self::$settings = $this->get_core_settings();

        self::$settings['core'] = array(
            'styles' => array(),
            'scripts' => array()
        );
        self::$settings['loadedJs'] = array();
        self::$settings['loadedCss'] = array();
        $cache_buster = '?ver=' . time();

        // Use relative URL to support both http and https.
        $lib_url =  DOMAIN . PATH . '/vendor/h5p/h5p-core/';
        $rel_path = '/' . preg_replace('/^[^:]+:\/\/[^\/]+\//', '', $lib_url);
        // Add core stylesheets
        foreach (H5PCore::$styles as $style) {
            self::$settings['core']['styles'][] = $rel_path . $style . $cache_buster;
        }

        // Add core JavaScript
        foreach (H5PCore::$scripts as $script) {
            self::$settings['core']['scripts'][] = $rel_path . $script . $cache_buster;
        }
    }

    public function get_content_settings($content)
    {
        global $wpdb;
        $core = $this->H5PCore;

        $safe_parameters = $core->filterParameters($content);

        // Getting author's user id
        //$author_id = (int)(is_array($content) ? $content['user_id'] : $content->user_id);
        $author_id = 3;
        // Add JavaScript settings for this content
        $settings = array(
            'library' => H5PCore::libraryToString($content['library']),
            'jsonContent' => $safe_parameters,
            'fullScreen' => $content['library']['fullscreen'],
           // 'exportUrl' => get_option('h5p_export', TRUE) ? $this->get_h5p_url() . '/exports/' . ($content['slug'] ? $content['slug'] . '-' : '') . $content['id'] . '.h5p' : '',
          //  'embedCode' => '<iframe src="' . admin_url('admin-ajax.php?action=h5p_embed&id=' . $content['id']) . '" width=":w" height=":h" frameborder="0" allowfullscreen="allowfullscreen"></iframe>',
          //  'resizeCode' => '<script src="' . plugins_url('h5p/h5p-php-library/js/h5p-resizer.js') . '" charset="UTF-8"></script>',
          //  'url' => admin_url('admin-ajax.php?action=h5p_embed&id=' . $content['id']),
            'title' => $content['title'],
            'displayOptions' => $core->getDisplayOptionsForView($content['disable'], $author_id),
            'contentUserData' => array(
                0 => array(
                    'state' => '{}'
                )
            )
        );

            return $settings;

    }


    public function get_core_settings() {
        $settings = array(
            'baseUrl' =>  DOMAIN,
            'url' => PATH,
           // 'postUserStatistics' => (get_option('h5p_track_user', TRUE) === '1') && $current_user->ID,
         /*   'ajax' => array(
                'setFinished' => admin_url('admin-ajax.php?token=' . wp_create_nonce('h5p_result') . '&action=h5p_setFinished'),
                'contentUserData' => admin_url('admin-ajax.php?token=' . wp_create_nonce('h5p_contentuserdata') . '&action=h5p_contents_user_data&content_id=:contentId&data_type=:dataType&sub_content_id=:subContentId')
            ),*/
            'saveFreq' => false,
            'siteUrl' => DOMAIN . PATH,
            'l10n' => array(
                'H5P' => '',
            ),
            'hubIsEnabled' => false
        );

            $settings['user'] = array(
                'name' => 'steffen',
                'mail' => 'hippeli@metaventis.com'
            );


        return $settings;
    }



//for testing
    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object))
                        self::rrmdir($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }

}

$mod = new mod_h5p();
$mod->run();