<?php
session_start();
?>
 

<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="<?php echo $_SESSION['csrftoken'] ?>">
    <style type="text/css"> 
      * {
          font-family:verdana, sans-serif;
        }
      .modal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.6);
        }

        .modal-content {
            padding: 20px;
            color: #333;
            position: relative;
            background-color: #fff;
            margin: auto;
            padding: 0;
            border: 1px solid #888;
            width: 60%;
            box-shadow: 0 0 5px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
        }
    </style> 
    <link href="<?php echo $_SESSION['WWWURL']?>/css/toastr.min.css" rel="stylesheet"/>
    <script src="<?php echo $_SESSION['WWWURL']?>/js/jquery-3.2.1.min.js"></script>
    <script src="<?php echo $_SESSION['WWWURL']?>/js/toastr.min.js"></script>

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

     function destroy() {
         //tinymce.activeEditor.destroy();
         document.getElementById('modal').style.display = "block";
     }

     save = function  () {
        var xhr = new XMLHttpRequest();
         xhr.open('POST', '<?php echo $_SESSION['WWWURL']?>/ajax/' + 'setText');
         xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
         xhr.onload = function() {
             if (xhr.status === 200) {
                 window.opener.postMessage({event:'UPDATE_SESSION_TIMEOUT'},'*');
                 toastr.success('Dateiinhalt wurde gespeichert.');
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

    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-bottom-center",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
        }

  </script>
  <?php endif; ?>
</head>

<body>
<div id="contentwrapper">
<?php if($_SESSION['edit']) : ?>
  <form method="post">
    <textarea id="theTextarea">
        <?php echo $_SESSION['content']?>
    </textarea>
  </form>

  <?php else : ?>

    <div><?php echo $_SESSION['content']?></div>

<?php endif; ?>
</div>
<div id="modal" class="modal">

  <div class="modal-content">
    <h1>Sie wurden abgemeldet oder ein Fehler ist aufgetreten</h1>
    Ihre Sitzung.....
    <hr/>
   <a href="<?php echo $_SERVER["HTTP_REFERER"]?>">NEU ANMELDEN</a>
  </div>

</div>
</body>
</html>