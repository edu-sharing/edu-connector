<?php

use connector\lib\Connector;
use connector\lib\Logger;
use connector\lib\MetadataGenerator;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

error_reporting(0); //do not change, can cause ajax problems

$container = new \Slim\Container;
$app = new \Slim\App([$container, 'settings' => [
    'displayErrorDetails' => true,
    'debug'               => true,
    'whoops.editor'       => 'sublime',
]]);

$container = $app->getContainer();
$container['log'] = function ($container) {
    $log = new Logger();
    return $log->getLog();
};
$container['view'] = function ($container) {
    $view = new Twig('templates', [
        'cache' => false
    ]);
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    return $view;
};

$app->get('/', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $connector = new Connector($this->get('log'));
});

$app->get('/error/{errorid}[/{language}]', function (Request $request, Response $response, $args) {
    $this->get('log')->info($request->getUri());
    if(!isset($args['language']))
	$args['language'] = 'en';
    $language = include __DIR__ . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $args['language'] . '.php';
    switch($args['errorid']) {
        case ERROR_INVALID_ID:
            return $this->view->render($response, 'error/invalidid.html', array('title' => $language['error'], 'message' => $language['errorInvalidId'], 'wwwurl' => WWWURL));
            break;
        case ERROR_NOT_SAVED:
            return $this->view->render($response, 'error/notsaved.html', array('title' => $language['error'], 'message' => $language['errorNotSaved'], 'wwwurl' => WWWURL));
            break;
        default:
            return $this->view->render($response, 'error/default.html', array('title' => $language['error'], 'message' => $language['errorDefault'], 'wwwurl' => WWWURL));
    }
});

$app->get('/metadata', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $metadataGenerator = new MetadataGenerator();
    $metadataGenerator -> serve();
});

$app->get('/install', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $installer = new \connector\lib\install();
    $installer -> install();
});

//ajax.php needed because h5p concatenates GET parameters
$app->post('/ajax/ajax.php', function (Request $request, Response $response) {
    global $db;
    $db = new \PDO('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $contentHandler = new \connector\tools\h5p\H5PContentHandler();
    $h5p = \connector\tools\h5p\H5P::getInstance();

     if(isset($request->getQueryParams()['action']) && $request->getQueryParams()['action']==='h5p_files') {
         $token = '';//$_GET['token'];
         $contentId = $_GET['contentId'];
         $h5p->H5PEditor->ajax->action(H5PEditorEndpoints::FILES, $token, $contentId);
     }

    if(isset($request->getQueryParams()['action']) && $request->getQueryParams()['action']==='h5p_libraries') {
        $db = new \PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPASSWORD);
        $db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
        $libs = $h5p->H5PEditor->ajax->action(H5PEditorEndpoints::LIBRARIES);
        return $response->withStatus(200)
            ->withHeader('Content-type', 'application/json')
            ->write($libs);
    }

    if(isset($request->getQueryParams()['action']) && $request->getQueryParams()['action']==='h5p_library-install') {
        $db = new \PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPASSWORD);
        $db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
        $token = '';//$_GET['token'];
        $libs = $h5p->H5PEditor->ajax->action(H5PEditorEndpoints::LIBRARY_INSTALL, $token, $request->getQueryParams()['id']);
        return $response->withStatus(200)
           ->withHeader('Content-type', 'application/json')
            ->write($libs);
    }

    if(isset($request->getQueryParams()['action']) && $request->getQueryParams()['action']==='h5p_create') {
    try {
            $id = $_REQUEST['id']; // apiClient id
            $cid = $contentHandler->process_new_content();
            if ($cid) {
                $apiClient = new \connector\lib\EduRestClient($id);
                $contentPath = DATA . DIRECTORY_SEPARATOR . 'h5p' . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . $_SESSION[$id]['node']->node->ref->id.'-'.$cid.'.h5p';
                $res = $apiClient->createContentNodeEnhanced($_SESSION[$id]['node']->node->ref->id, $contentPath, 'application/zip', 'EDITOR_UPLOAD,H5P');
                if($res) {
                    //cleanup filesystem and db
                    $h5p->H5PFramework -> deleteContentData($cid);
                    $h5p->H5PFramework -> deleteLibraryUsage($cid);
                    unlink($contentPath);
                    $h5p -> rrmdir($h5p -> H5PFramework -> get_h5p_path() . '/content/' . $cid);
                    if(isset($_SESSION[$id]['viewContentId'])){
                        $h5p -> rrmdir($h5p -> H5PFramework -> get_h5p_path() . '/content/' . $_SESSION[$id]['viewContentId']);
                    }
                    $h5p -> rrmdir(DATA . DIRECTORY_SEPARATOR . 'h5p' . DIRECTORY_SEPARATOR . 'editor');
                    $h5p -> rrmdir(DATA . DIRECTORY_SEPARATOR . 'h5p' . DIRECTORY_SEPARATOR . 'temp');

                    //cordova
                    echo '<script>window.shouldClose=true;</script>';
                    echo '<script>setInterval(function(){if(window.opener){window.opener.postMessage({event:"REFRESH"},"*"); window.opener.postMessage({event:"CLOSE"},"*");}},100);</script>';
                    exit(0);
                }
            }
        } catch (\Exception $e) {
            $this -> get('log') -> error('HTTP ' . $e -> getCode() . ' ' . $e -> getMessage());
            return $response->withStatus(302)->withHeader('Location', WWWURL . '/error/2/' . $_SESSION[$_REQUEST['id']]['language']);
        }
    }
});

//ajax.php needed because h5p concatenates GET parameters
$app->get('/ajax/ajax.php', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    global $db;
    try {
        $db = new \PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPASSWORD);
        $db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
        $h5p = \connector\tools\h5p\H5P::getInstance();

        if(isset($request->getQueryParams()['machineName']) && isset($request->getQueryParams()['majorVersion']) && isset($request->getQueryParams()['minorVersion'])) {
            $lib = $h5p->H5PEditor->ajax->action(H5PEditorEndpoints::SINGLE_LIBRARY, $request->getQueryParams()['machineName'],
                $request->getQueryParams()['majorVersion'], $request->getQueryParams()['minorVersion'], 'de', '',
                $h5p->H5PFramework->get_h5p_path(),''
            );
            return $response->withStatus(200)
                ->withHeader('Content-type', 'application/json')
                ->write($lib);
        } else {
            $libs = $h5p->H5PEditor->ajax->action(str_replace('h5p_', '', $request->getQueryParams()['action']));
            return $response->withStatus(200)
                ->withHeader('Content-type', 'application/json')
                ->write($libs);
        }
    } catch (\Exception $e) {
        $this->get('log')->error('HTTP ' . $e -> getCode() . ' ' . $e->getMessage());
        return $response->withStatus(302)->withHeader('Location', WWWURL . '/error/0/' . $_SESSION[$_REQUEST['id']]['language']);
    }
});

$app->post('/ajax/unlockNode', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $id = $request->getQueryParams()['X-CSRF-Token'];
    try {
        $apiClient = new \connector\lib\EduRestClient($id);
        $apiClient->unlockNode($_SESSION[$id]['node']->node->ref->id);
    } catch (\Exception $e) {
        $this->get('log')->error('HTTP ' . $e -> getCode() . ' ' . $e->getMessage());
        $response = $response->withStatus($e -> getCode());
    }
    return $response;
});

$app->post('/ajax/setText', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $id = $request->getHeaderLine('X-CSRF-Token');
    try {
        $apiClient = new \connector\lib\EduRestClient($id);
        $parsedBody = $request->getParsedBody();
        $content = $parsedBody['text'];
        $apiClient->createTextContent($_SESSION[$id]['node']->node->ref->id, $content, 'text/html', 'EDITOR_UPLOAD,TINYMCE');
    } catch (\Exception $e) {
        $this->get('log')->error('HTTP ' . $e -> getCode() . ' ' . $e->getMessage());
        $response = $response->withStatus($e -> getCode());
    }
    $_SESSION[$id]['content'] = $content;
    return $response;
});

$app -> run();
