<?php
session_start();

require_once 'config.php';
require_once 'src/lib/Connector.php';
require_once 'src/lib/EduRestClient.php';
require_once 'src/tools/OnlyOffice/OnlyOffice.php';

$connector = new Connector();
