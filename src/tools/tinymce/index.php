<?php
session_start();
?>
 

<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="<?php echo $_SESSION['csrftoken'] ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.2/css/materialize.min.css">


  <script src="<?php echo $_SESSION['WWWURL']?>/js/jquery-3.2.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.2/js/materialize.min.js"></script>
    
<?php if($_SESSION['edit']) : ?>

 <script src='js/tinymce/tinymce.min.js'></script>
  <script>

$(document).ready(function() {

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

     function destroy() {
         //tinymce.activeEditor.destroy();
         $('#modal').modal();
         $('#modal').modal('open'); 
     }

     save = function  () {
        var xhr = new XMLHttpRequest();
         xhr.open('POST', '<?php echo $_SESSION['WWWURL']?>/ajax/' + 'setText');
         xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
         xhr.onload = function() {
             if (xhr.status === 200) {
                 window.opener.postMessage({event:'UPDATE_SESSION_TIMEOUT'},'*');
                 // Materialize.toast(message, displayLength, className, completeCallback);
                    Materialize.toast('Dateiinhalt wurde gespeichert.', 4000) // 4000 is the duration of the toast
             } else {
                destroy();
             }  
         };
         xhr.send('text=' + encodeURIComponent(tinymce.activeEditor.getContent()));
	 }

     unlockNode = function() {
         var xhr = new XMLHttpRequest();
         //synchronous!
         xhr.open('GET', '<?php echo $_SESSION['WWWURL']?>/ajax/' + 'unlockNode', false);
         xhr.send();
     }

     setInterval(function(){
         if (tinyMCE.activeEditor.isDirty()) {
             tinyMCE.activeEditor.save(); // to reset isDirty
             save();
         }
     }, 10000);


    window.onunload = function(){
        unlockNode();
    }

    window.addEventListener("message", receiveMessage, false);
    function receiveMessage(event){
        if(event.data.event=="SESSION_TIMEOUT"){
            
            if(event.data.data < 0) {
                destroy();
            }

            var min = Math.floor(event.data.data/60);
            if(min < 10)
                min = '0' + min;
            var sec = Math.floor(event.data.data%60);
            if(sec < 10)
                sec = '0' + sec;
            $('#countdown').html(min + ':' + sec);
        }
    }

});
  </script>
  
  <?php endif; ?>
</head>

<body>
<h1>DEV - NICHT BENUTZEN - <span id="countdown"></span></h1>
<?php if($_SESSION['edit']) : ?>
  <form method="post">
    <textarea id="theTextarea">
        <?php echo $_SESSION['content']?>
    </textarea>
  </form>

  <?php else : ?>

    <div><?php echo $_SESSION['content']?></div>

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