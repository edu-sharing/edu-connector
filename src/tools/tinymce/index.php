<?php
session_start();

$id = $_GET['id'];

if(empty($_SESSION[$id]) || empty($_GET['id']))
 die();
 
$access = 1;
if($_SESSION[$id]['first_run'] !== true) {
  $access = 0;
}
$_SESSION[$id]['first_run'] = false;
?>
 
<!DOCTYPE html>
<html>
<head>
  <meta name="csrf-token" content="<?php echo $id ?>">
  <link href="//fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
  <link href="//fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/materialize/0.98.2/css/materialize.min.css">
  <link rel="stylesheet" href="<?php echo $_SESSION[$id]['WWWURL']?>/css/main.css" rel="stylesheet">

  <script src="<?php echo $_SESSION[$id]['WWWURL']?>/js/jquery-3.2.1.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/materialize/0.98.2/js/materialize.min.js"></script>
  <script src='js/tinymce/tinymce.min.js'></script>
  <script>
    var wwwurl = '<?php echo $_SESSION[$id]['WWWURL']?>';
    var readonly = <?php echo $_SESSION[$id]['readonly'] ?>;
    var access = <?php echo $access ?>;
    var id = '<?php echo $id ?>';
  </script>
  <script src="<?php echo $_SESSION[$id]['WWWURL']?>/src/tools/tinymce/js/tool.js"></script>
</head>
<body>
<?php echo $_SESSION[$id]['node']->node->ref->id ?> <i class="material-icons">av_timer</i> <span id="countdown"></span>
<form method="post">
  <textarea id="theTextarea">
      <?php echo $_SESSION[$id]['content'];?>
  </textarea>
</form>

<div id="modal" class="modal">
    <div class="modal-content">
      <h4>Sie wurden abgemeldet / RELOAD NICHT ERLAUBT (todo error msg)</h4>
      <p>Ihre Sitzung wurde aufgrund von Inaktivität zu Ihrer Sicherheit automatisch beendet, da Sie mindestens 10 Minuten nicht aktiv waren.<br/>Der Dateiinhalt wurde automatisch gespeichert.<br/>Bitte schließen Sie den Editor und melden Sie sich erneut an.</p>
      <div style="text-align: right">
        <a class="waves-effect waves-light btn" onclick="javascript:window.close()">EDITOR SCHLIESSEN</a>
        </div>
    </div>
  </div>
</body>
</html>