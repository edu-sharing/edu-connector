<?php
session_start();

$lang = 'de';

$id = $_GET['id'];

if(false === filter_var($id, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-z0-9]*$/"))))
    die('Invalid ID');

if(empty($_SESSION[$id]) || empty($_GET['id']))
  die('Invalid ID');

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
  <div id="countdown"><i class="material-icons">av_timer</i> <span id="countdownvalue"></span></div>
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