<?php
session_start();
?>
 

<!DOCTYPE html>
<html>
<head>
  <meta name="csrf-token" content="<?php echo $_SESSION['csrftoken'] ?>">
  <link href="//fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.2/css/materialize.min.css">
  <script src="<?php echo $_SESSION['WWWURL']?>/js/jquery-3.2.1.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/materialize/0.98.2/js/materialize.min.js"></script>
    
<?php if($_SESSION['edit']) : ?>
    <script src='js/tinymce/tinymce.min.js'></script>
    <script>var wwwurl = '<?php echo $_SESSION['WWWURL']?>';
    <script src="<?php echo $_SESSION['WWWURL']?>/src/tools/tinymce/js/tool.js"></script>
<?php endif; ?>
</head>

<body>
<h1>DEV - NICHT BENUTZEN - <i class="material-icons">av_timer</i> <span id="countdown"></span></h1>
<?php if($_SESSION['edit']) : ?>
  <form method="post">
    <textarea id="theTextarea">
        <?php echo $_SESSION['content']?>
    </textarea>
  </form>

  <?php else : ?>

    <div>umabauen autologout oder tinymce nonedit mode<?php echo $_SESSION['content']?></div>

<?php endif; ?>


<div id="modal" class="modal">
    <div class="modal-content">
      <h4>Sie wurden abgemeldet oder ein Fehler ist aufgetreten ...</h4>
      <p>... mal sehen</p>
      <div style="text-align: right">
        <a class="waves-effect waves-light btn" href="<?php echo $_SERVER["HTTP_REFERER"]?>">NEU ANMELDEN</a>
        </div>
    </div>
  </div>
          


</body>
</html>