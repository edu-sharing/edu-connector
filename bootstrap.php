<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$container = new \Slim\Container;
$app = new \Slim\App([$container, 'settings' => [
    'displayErrorDetails' => true,
    'debug'               => true,
    'whoops.editor'       => 'sublime',
]]);

$container = $app->getContainer();
$container['log'] = function ($container) {
    $log = new \connector\lib\Logger();
    return $log->getLog();
};
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('templates', [
        'cache' => false
    ]);
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    return $view;
};

$app->get('/', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $connector = new \connector\lib\Connector($this->get('log'));
});

$app->get('/error/{errorid}', function (Request $request, Response $response, $args) {
    $this->get('log')->info($request->getUri());
    switch($args['errorid']) {
        case ERROR_INVALID_ID:
            return $this->view->render($response, 'error/invalidid.html', []);
            break;
        default:
            return $this->view->render($response, 'error/default.html', []);
    }
});

$app->get('/metadata', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $metadataGenerator = new \connector\lib\MetadataGenerator();
    $metadataGenerator -> serve();
});

//ajax.php needed because h5p concatenates GET parameters
$app->post('/ajax/ajax.php', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $H5PFramework = new connector\tools\h5p\H5PFramework();
     if(isset($request->getQueryParams()['action']) && $request->getQueryParams()['action']==='h5p_files') {
         $H5PCore = new \H5PCore($H5PFramework, $H5PFramework->get_h5p_path(), $H5PFramework->get_h5p_url(), LANG, false);
         $H5PEditor = new \H5peditor( $H5PCore, new connector\tools\h5p\H5peditorStorageImpl(), new connector\tools\h5p\H5PEditorAjaxImpl());
         $token = '';//$_GET['token'];
         $contentId = 0;//$_GET['contentId'];
         $H5PEditor->ajax->action(H5PEditorEndpoints::FILES, $token, $contentId);
     }

    if(isset($request->getQueryParams()['action']) && $request->getQueryParams()['action']==='h5p_create') {
        global $db;
        try {
            $id = $_REQUEST['id'];
            $db = new \PDO('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASSWORD);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $contentHandler = new connector\tools\h5p\H5PContentHandler();
            $cid = $contentHandler->process_new_content();
            if ($cid) {
                $apiClient = new \connector\lib\EduRestClient($id);
                $contentPath = DOCROOT . '/src/tools/h5p/exports/'.$_SESSION[$id]['node']->node->ref->id.'-'.$cid.'.h5p';
                $res = $apiClient->createContentNodeEnhanced($_SESSION[$id]['node']->node->ref->id, $contentPath, 'application/zip', 'EDITOR_UPLOAD,H5P');

                if($res) {
                    //cleanup filesystem and db
                    unlink($contentPath);
                    //delete from 5p_contents
                    $H5PFramework -> deleteContentData($cid);
                    //h5p_contents_libraries
                    //$permaWithoutVersion = substr($_SESSION[$id]['node']->node->properties->{'virtual:permalink'}[0], 0, strrpos( $_SESSION[$id]['node']->node->properties->{'virtual:permalink'}[0], '/'));
                    //header('location:' . $permaWithoutVersion. '?closeOnBack=true');
                    echo '<script> window.opener.postMessage({event:"REFRESH"},"*");window.close();</script>';
                    exit(0);
                }
            }
        } catch (\Exception $e) {
            $response = $response->withStatus($e -> getCode());
            $this->get('log')->error('HTTP ' . $e -> getCode() . ' ' . $e->getMessage());
        }
    }
});

//ajax.php needed because h5p concatenates GET parameters
$app->get('/ajax/ajax.php', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    global $db;
    $response ->withHeader('Content-Typ', 'application/json');
    try {
        $db = new \PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPASSWORD);
        $db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
        $H5PFramework = new connector\tools\h5p\H5PFramework();
        $H5PCore = new \H5PCore($H5PFramework, $H5PFramework->get_h5p_path(), $H5PFramework->get_h5p_url(), LANG, false);
        $H5PEditor = new \H5peditor( $H5PCore, new connector\tools\h5p\H5peditorStorageImpl(), new connector\tools\h5p\H5PEditorAjaxImpl());

        if(isset($request->getQueryParams()['machineName']) && isset($request->getQueryParams()['majorVersion']) && isset($request->getQueryParams()['minorVersion'])) {
            $lib = $H5PEditor->ajax->action(H5PEditorEndpoints::SINGLE_LIBRARY, $request->getQueryParams()['machineName'],
                $request->getQueryParams()['majorVersion'], $request->getQueryParams()['minorVersion'], LANG, '',
                $H5PFramework->get_h5p_path()
            );
            return $response->withStatus(200)
                ->withHeader('Content-type', 'application/json')
                ->write($lib);
        } else {
            $libs = $H5PEditor->ajax->action(H5PEditorEndpoints::LIBRARIES);
            return $response->withStatus(200)
                ->withHeader('Content-type', 'application/json')
                ->write($libs);
        }

    } catch (\Exception $e) {
        $response = $response->withStatus($e -> getCode());
        $this->get('log')->error('HTTP ' . $e -> getCode() . ' ' . $e->getMessage());
    }
});

$app->get('/ajax/unlockNode', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $id = $request->getHeaderLine('X-CSRF-Token');
    try {
        $apiClient = new \connector\lib\EduRestClient($id);
        $apiClient->unlockNode($_SESSION[$id]['node']->node->ref->id);
    } catch (\Exception $e) {
        $response = $response->withStatus($e -> getCode());
        $this->get('log')->error('HTTP ' . $e -> getCode() . ' ' . $e->getMessage());
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
        $response = $response->withStatus($e -> getCode());
        $this->get('log')->error('HTTP ' . $e -> getCode() . ' ' . $e->getMessage());
    }
    $_SESSION[$id]['content'] = $content;
    return $response;
});

$app -> run();
