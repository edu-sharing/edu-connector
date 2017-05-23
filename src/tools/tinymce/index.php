<?php
session_start();
?>
 

<!DOCTYPE html>
<html>
<head>
 <script src='js/tinymce/tinymce.min.js'></script>
  <script>

	 save = function  () {
		alert(tinymce.activeEditor.getContent());
	 }

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

     var lastPing = Date.now();

     function destroy() {
         alert('Session abgelaufen. Bitte neu anmelden.');
     }

     function pingApi() {
         var xhr = new XMLHttpRequest();
         xhr.open('GET', '<?php echo $_SESSION['api_url']?>' + 'authentication/v1/validateSession');
         xhr.onload = function() {
             if (xhr.status === 200) {
                 lastPing = Date.now();
             }
             else {
                 destroy();
             }
         };
         xhr.send();
     }

     setInterval(function(){
         if (tinyMCE.activeEditor.isDirty()) {
             pingApi();
         } else {
             if(Date.now() - lastPing > 500)
                 destroy();
         }

     }, 10000);


  
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