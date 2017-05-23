<?php
session_start();
?>
 

<!DOCTYPE html>
<html>
<head>
 <script src='js/tinymce/tinymce.min.js'></script>
  <script>

      tinymce.init({
        selector: '#theTextarea',
        plugins: [
                  'advlist autolink lists link image charmap hr anchor pagebreak',
                  'searchreplace wordcount visualblocks visualchars code fullscreen',
                  'insertdatetime media nonbreaking save table contextmenu directionality',
                  'emoticons template paste textcolor colorpicker textpattern imagetools codesample toc help code'
                ],
                toolbar1: 'save | undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
                toolbar2: 'forecolor backcolor emoticons | help',
                image_advtab: true,
        branding: false,
        height: 500,
        save_onsavecallback: function () { save() }
      });

     function destroy() {
         alert('Session abgelaufen. Bitte neu anmelden.');
     }

     function pingApi() {
         var xhr = new XMLHttpRequest();
         xhr.open('GET', '<?php echo $_SESSION['api_url']?>' + 'authentication/v1/validateSession');
         xhr.withCredentials = true;
         xhr.setRequestHeader('Accept','application/json');
         xhr.onload = function() {
             if (xhr.status === 200) {
                     window.opener.postMessage('asdasdasd', '*');
             }
             else {
                 destroy();
             }
         };
         xhr.send();
     }

     save = function  () {
		alert(tinymce.activeEditor.getContent());

        //save contentn without versioning
    
	 }

     unlockNode = function() {
         //call api
     }

     setInterval(function(){
         if (tinyMCE.activeEditor.isDirty()) {
             pingApi();
             save();
         }
     }, 10000);

     window.addEventListener("onbeforeunload ", function (e) {
        unlockNode();

        (e || window.event).returnValue = null;
        return null;
        });


  
  </script>
</head>

<body>
<h1>Logineo</h1>
  <form method="post">
    <textarea id="theTextarea">
        <?php echo $_SESSION['content']?>
    </textarea>
  </form>
</body>
</html>