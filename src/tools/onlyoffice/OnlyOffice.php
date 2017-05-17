<?php

namespace connector\tools\OnlyOffice;

class OnlyOffice
{

    private $fileType = '';

    public function __construct()
    {

    }

    public function run()
    {
        $this->forwardToEditor();
    }

    private function forwardToEditor()
    {
        $_SESSION['fileUrl'] = $_SESSION['node']->node->downloadUrl . '&accessToken=' . $_REQUEST['accessToken'];
        $_SESSION['fileType'] = $this->fileType;
        header('Location: ' . WWWURL . EDITORPATH);
        exit();
    }

    public static function getMimetype($doctype)
    {
        switch ($doctype) {
            case 'docx':
                $mimetype = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            case 'xlsx':
                $mimetype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'pptx':
                $mimetype = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                break;
            case 'odt':
                $mimetype = 'application/vnd.oasis.opendocument.text';
                break;
            case 'ods':
                $mimetype = 'application/vnd.oasis.opendocument.spreadsheet';
                break;
            case 'odp':
                $mimetype = 'application/vnd.oasis.opendocument.presentation';
                break;
            default:
                error_log('No mimetype specified');
                throw new Exception();
        }
        return $mimetype;
    }
}
