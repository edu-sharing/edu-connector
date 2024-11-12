<?php
$id = preg_replace('/[^a-f0-9]/', '', $_GET["id"]);
?>

<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>edu-sharing</title>

  <script type="text/javascript" src="plugins.js.php?id=<?php echo $id; ?>"></script>
  <script type="text/javascript" src="edusharing.js.php?id=<?php echo $id; ?>"></script>

  <link rel="stylesheet" href="edu-style.css" />

</head>
<body>

<div class="eduHeader">
  <h1 id="eduHeader_label">View and edit edu-sharing object</h1>
</div>

<div class="eduWrapper">
  <div class="repoMenu">
    <p id="repoMenu_label">Open edu-sharing repository:</p>
    <button class="btn" id="repo_btn">Open Repo</button>
  </div>


  <div style="display:none;">
    <input type="text" class="form-control" id="textbox_url" autocomplete="off">
    <button class="btn-text-default" id="textbox_button">OK</button>
  </div>

  <div id="eduViewer"></div>

</div>

</body>
</html>
