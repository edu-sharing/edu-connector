<?php
session_start();

$lang = 'de';

$id = $_GET['id'];

require_once __DIR__ . '/../../../defines.php';

if(false === filter_var($id, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-z0-9]*$/"))) || empty($_GET['id'])) {
    header('Location: ' . '../../../error/' . ERROR_DEFAULT);
    exit;
}

if(empty($_SESSION[$id])) {
    $permalink = base64_decode($_GET['ref']);
    $permalinkwithoutversion = substr($permalink, 0, strripos($permalink, '/'));
    header('Location: ' . $permalinkwithoutversion);
    exit;
}

$access = 1;
if($_SESSION[$id]['first_run'] !== true) {
  $access = 0;
}
$_SESSION[$id]['first_run'] = false;
?>
 
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="<?php echo $_SESSION[$id]['WWWURL']?>/css/materialize.min.css">
  <link rel="stylesheet" href="<?php echo $_SESSION[$id]['WWWURL']?>/css/main.css" rel="stylesheet">
<style>
body {
    background-color: #f6f6f6;
    margin: 0 auto;
    max-width: 1024px;
}
h4 {
    font-size: 1.2rem;
}

.mce-content-body p {
    line-height: 1.5em;
}
</style>
  <script>
    var wwwurl = '<?php echo $_SESSION[$id]['WWWURL']?>';
    var readonly = <?php echo $_SESSION[$id]['readonly'] ?>;
    var access = <?php echo $access ?>;
    var id = '<?php echo $id ?>';
    var lang = '<?php echo $lang ?>';
  </script>

  <script src="<?php echo $_SESSION[$id]['WWWURL']?>/js/lang/<?php echo $lang ?>.js"></script>
  <script src="<?php echo $_SESSION[$id]['WWWURL']?>/js/jquery-3.2.1.min.js"></script>
  <script src="<?php echo $_SESSION[$id]['WWWURL']?>/js/materialize.min.js"></script>
  <script src='js/tinymce/tinymce.min.js'></script>
  <script src="<?php echo $_SESSION[$id]['WWWURL']?>/src/tools/tinymce/js/tool.js"></script>
</head>
<body>
<header>
  <div id="countdown"><img src="<?php echo $_SESSION[$id]['WWWURL']?>/img/ic_av_timer_black_24px.svg"> <span id="countdownvalue"></span></div>
  <h4><?php echo $_SESSION[$id]['node']->node->name ?></h4>
</header>
<section>
<form method="post">
  <textarea id="theTextarea">
      <?php echo $_SESSION[$id]['content'];?>
  </textarea>
</form>
</section>
<div id="modal" class="modal">
    <div class="modal-content">
      <h4 id="modalHeading"></h4>
      <p id="modalText"></p>
      <div style="text-align: right">
        <a id="modalButton" class="waves-effect waves-light btn" onclick="javascript:window.close()"></a>
        </div>
    </div>
  </div>
</body>
</html>