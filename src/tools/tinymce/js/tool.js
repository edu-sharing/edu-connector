$(document).ready(function() {

    tinymce.init({
        selector: '#theTextarea',
        plugins: [
            'advlist autolink lists link charmap hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars fullscreen',
            'insertdatetime nonbreaking save table contextmenu directionality',
            'emoticons paste textcolor colorpicker help print'
        ],
        toolbar: 'save | print | undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',
        image_advtab: true,
        branding: false,
        height: 500,
        readonly: readonly,
        language : lang,
        language_url: 'js/tinymce/langs/' + lang + '.js',
        save_onsavecallback: function () { save() } //save button
    });

    function destroy(text) {
        $('#theTextarea').html('');
        $('#modalHeading').html(text[0]);
        $('#modalText').html(text[1]);
        $('#modalButton').html(text[2]);
        $('#modal').modal({
            dismissible: false,
            opacity: .8,
        });
        $('#modal').modal('open');
    }

    save = function  () {
        $.ajax({
            type: 'POST',
            url: wwwurl + '/ajax/setText',
            contentType: 'application/x-www-form-urlencoded',
            beforeSend: function(request) {
                request.setRequestHeader("X-CSRF-Token", id);
            },
            crossDomain: true,
            data: 'text=' + encodeURIComponent(tinymce.activeEditor.getContent())
        }).done(function() {
            window.opener.postMessage({event:'UPDATE_SESSION_TIMEOUT'},'*');
            Materialize.toast(language.datasaved, 4000, 'success');
        })
        .fail(function(jqXHR) {
            switch(jqXHR.status) {
                case 401:
                    destroy([language.invalidsessionheading, language.invalidsessiontext, language.closeeditor]);
                break;
                default:
                    Materialize.toast(language.datasavederror + ' (HTTP status code ' + jqXHR.status + ')', 4000, 'error');
            }
        });
    }

    unlockNode = function() {
        $.ajax({
            type: 'GET',
            async: false, // will not work if changed
            url: wwwurl + '/ajax/unlockNode',
            beforeSend: function(request) {
                request.setRequestHeader("X-CSRF-Token", id);
            },
            crossDomain: true
        });
    }

    setInterval(function(){
        if (tinyMCE.activeEditor.isDirty()) {
            tinyMCE.activeEditor.save(); // to reset isDirty
            save();
        }
    }, 20000);

    if(access < 1) {
        destroy([language.functiondeactivatedheading, language.functiondeactivatedtext, language.closeeditor]);
    }

    window.onbeforeunload = function() {
        if (tinyMCE.activeEditor.isDirty()) {
            return language.leavesiteunsaved;
        }
        unlockNode();
    }

    window.addEventListener("message", receiveMessage, false);
    
    receiveMessage = function(event){
        if(event.data.event=="SESSION_TIMEOUT"){
            if(event.data.data > 0) {
                var min = Math.floor(event.data.data/60);
                if(min < 10)
                    min = '0' + min;
                var sec = Math.floor(event.data.data%60);
                if(sec < 10)
                    sec = '0' + sec;
                $('#countdownvalue').html(min + ':' + sec);
            } else {
                $('#countdownvalue').html('00:00');
                destroy([language.timeoutheading, language.timeouttext, language.closeeditor]);
            }
        }
        if(event.data.event=='USER_LOGGED_OUT') {
            $('#countdownvalue').html('00:00');
            destroy([language.invalidsessionheading, language.invalidsessiontext, language.closeeditor]);
        }
    }

});