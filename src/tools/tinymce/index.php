<?php
session_start();
?>
 

<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="<?php echo $_SESSION['csrftoken'] ?>">
    <link href="<?php echo WWWURL?>/css/toastr.min.css" rel="stylesheet"/>
    <script src="<?php echo WWWURL?>/js/toastr.min.js"></script>

<?php if($_SESSION['edit']) : ?>

 <script src='js/tinymce/tinymce.min.js'></script>
  <script>
      tinymce.init({
        selector: '#theTextarea',
        plugins: [
                  'advlist autolink lists link image charmap hr anchor pagebreak',
                  'searchreplace wordcount visualblocks visualchars code fullscreen',
                  'insertdatetime nonbreaking save table contextmenu directionality',
                  'emoticons paste textcolor colorpicker textpattern imagetools codesample toc help code'
                ],
                toolbar1: 'save | undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',
                toolbar2: 'forecolor backcolor emoticons | help',
                image_advtab: true,
        branding: false,
        height: 500,
        save_onsavecallback: function () { save() }
      });

     function destroy(message) {
         tinymce.activeEditor.destroy();
         document.body.innerHTML = message + '<br/><a href ="'+document.referrer+'">'+document.referrer+'</a>';
     }

    /* function pingApi() {
         var xhr = new XMLHttpRequest();
         xhr.open('GET', '<?php echo $_SESSION['ajax_url']?>' + 'pingApi');
         xhr.onload = function() {
             if (xhr.status === 200) {
                 window.opener.postMessage({event:'UPDATE_SESSION_TIMEOUT'},'*');
             }
             else {
                 destroy('Session abgelaufen. Bitte neu anmelden.');
             }
         };
         xhr.send();
     }*/

     save = function  () {
        var xhr = new XMLHttpRequest();
         xhr.open('POST', '<?php echo $_SESSION['ajax_url']?>' + 'setText');
         xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
         xhr.onload = function() {
             if (xhr.status === 200) {
                 //alert('das dokument wurde automatisch gespeichert, christian');
             } else {
                destroy('Fehler beim Speichern.');
             }  
         };
         xhr.send('text=' + encodeURIComponent(tinymce.activeEditor.getContent()));
	 }

     unlockNode = function() {
         var xhr = new XMLHttpRequest();
         //synchronous
         xhr.open('GET', '<?php echo $_SESSION['ajax_url']?>' + 'unlockNode', false);
         xhr.send();
     }

     setInterval(function(){
         if (tinyMCE.activeEditor.isDirty()) {
             //pingApi();
             save();
         }
     }, 10000);


    window.onunload = function(){
        unlockNode();
    }

  </script>
  <?php endif; ?>
</head>

<body>
<?php if($_SESSION['edit']) : ?>
  <form method="post">
    <textarea id="theTextarea">
        <?php echo $_SESSION['content']?>
    </textarea>
  </form>

  <?php else : ?>

    <div><?php echo $_SESSION['content']?></div>

<?php endif; ?>

</body>
</html>