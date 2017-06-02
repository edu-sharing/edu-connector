$(document).ready(function() {
    tinymce.init({
        selector: '#theTextarea',
        plugins: [
            'advlist autolink lists link charmap hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars fullscreen',
            'insertdatetime nonbreaking save table contextmenu directionality',
            'emoticons paste textcolor colorpicker help print'
        ],
        toolbar1: 'save | print | undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',
        //toolbar2: 'forecolor backcolor emoticons | help',
        image_advtab: true,
        branding: false,
        height: 500,
        readonly: readonly,
        language : 'de',
        language_url: 'js/tinymce/langs/de.js',
        save_onsavecallback: function () { save() }
    });

    function destroy() {
        //tinymce.activeEditor.destroy();
        $('#modal').modal();
        $('#modal').modal('open');
    }

    save = function  () {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', wwwurl + '/ajax/setText');
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200) {
                window.opener.postMessage({event:'UPDATE_SESSION_TIMEOUT'},'*');
                Materialize.toast('Dateiinhalt wurde gespeichert', 4000, 'success');
            } else if(xhr.status === 401) {
                destroy();
            } else {
                Materialize.toast('Dateiinhalt konnte nicht gespeichert werden (HTTP Status ' + xhr.status + ')', 4000, 'error');
            }
        };
        xhr.send('text=' + encodeURIComponent(tinymce.activeEditor.getContent()));
    }

    unlockNode = function() {
        var xhr = new XMLHttpRequest();
        //synchronous!
        xhr.open('GET', wwwurl + '/ajax/unlockNode', false);
        xhr.send();
    }

    setInterval(function(){
        if (tinyMCE.activeEditor.isDirty()) {
            tinyMCE.activeEditor.save(); // to reset isDirty
            save();
        }
    }, 20000);

    if(access < 1) {
        destroy();
    }

    window.onbeforeunload = function() {
        if (tinyMCE.activeEditor.isDirty()) {
            return 'Die von Ihnen vorgenommenen Änderungen werden möglicherweise nicht gespeichert.'; // default chrome text
        }
        unlockNode();
    }

    /*window.onunload = function(){
        unlockNode();
    }*/

    window.addEventListener("message", receiveMessage, false);
    function receiveMessage(event){
        if(event.data.event=="SESSION_TIMEOUT"){
            if(event.data.data > 0) {
                var min = Math.floor(event.data.data/60);
                if(min < 10)
                    min = '0' + min;
                var sec = Math.floor(event.data.data%60);
                if(sec < 10)
                    sec = '0' + sec;
                $('#countdown').html(min + ':' + sec);
            } else {
                $('#countdown').html('00:00');
                destroy();
            }
        }
    }

});