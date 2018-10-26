var H5PEditor = H5PEditor || {};
var ns = H5PEditor;

// additions to language files
(function (ns)
{
    ns.language.core['chooseImage'] = 'Choose image';
    ns.language.core['newImage'] = 'New image';
    var userLang = navigator.language || navigator.userLanguage;
    if (userLang.indexOf('de') > -1)
    {
        ns.language.core['chooseImage'] = 'Bild wählen';
        ns.language.core['newImage'] = 'Neues Bild';
    }
})(ns);

// communication with cordova app
takePictureByCordova = function (that) {
    function receiveMessage(event)
    {
        if(event.data.event=='EVENT_CORDOVA_CAMERA_RESPONSE'){
            console.log('got camera response');
            if (event.data.data) that.uploadData("data:image/png;base64,"+event.data.data);
            window.removeEventListener("message", receiveMessage);
        }
    }
    console.log('requesting picture from cordova camera');
    window.addEventListener("message", receiveMessage, false);
    window.parent.postMessage({event:'EVENT_CORDOVA_CAMERA'},'*');
    return true;
}


// overwrite h5p-type "None"
ns.None.prototype.appendTo = function ($wrapper) {
    var that = this;
    this.$item = ns.$(this.createHtml()).appendTo($wrapper);
};

ns.None.prototype.createHtml = function () {
    var markup = ns.createDescription(this.field.description);
    return ns.wrapFieldMarkup(this.field, markup);
};


// overwrite functions from h5peditor.js
ns.loadLibrary = function (libraryName, callback) {

    ns.libraryCache[libraryName] = undefined;

    switch (ns.libraryCache[libraryName]) {
        default:
            // Get semantics from cache.
            ns.libraryRequested(libraryName, callback);
            break;

        case 0:
            // Add to queue.
            ns.loadedCallbacks[libraryName].push(callback);
            break;

        case undefined:
            // Load semantics.
            ns.libraryCache[libraryName] = 0; // Indicates that others should queue.
            ns.loadedCallbacks[libraryName] = []; // Other callbacks to run once loaded.
            var library = ns.libraryFromString(libraryName);

            var url = ns.getAjaxUrl('libraries', library);

            // Add content language to URL
            if (ns.contentLanguage !== undefined) {
                url += (url.indexOf('?') === -1 ? '?' : '&') + 'language=' + ns.contentLanguage;
            }

            // Fire away!
            ns.$.ajax({
                url: url,
                success: function (libraryData) {
                    var semantics = libraryData.semantics;
                    if (libraryData.language !== null) {
                        var language = JSON.parse(libraryData.language);
                        semantics = ns.$.extend(true, [], semantics, language.semantics);
                    }
                    libraryData.semantics = semantics;
                    ns.libraryCache[libraryName] = libraryData;

                    ns.libraryRequested(libraryName, callback);

                    // Run queue.
                    for (var i = 0; i < ns.loadedCallbacks[libraryName].length; i++) {
                        ns.loadedCallbacks[libraryName][i](libraryData.semantics);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    if (window['console'] !== undefined) {
                        console.log('Ajax request failed');
                        console.log(jqXHR);
                        console.log(textStatus);
                        console.log(errorThrown);
                    }
                },
                dataType: 'json'
            });
    }
};

ns.createText = function (value, maxLength, placeholder) {

    var html = '';

    html += '<input type="text"';
    html += ' class="h5peditor-text"';
    if (value !== undefined) {
        html += ' value="' + value + '"';
    }
    html += ' maxlength="' + (maxLength === undefined ? 255 : maxLength) + '"/>';
    return html;
};

// overwrite functions from h5peditor-selector-legacy
var oldSelectorLegacyPrototype = ns.SelectorLegacy.prototype;
ns.SelectorLegacy = function (libraries, selectedLibrary, changeLibraryDialog) {
    var self = this;

    H5P.EventDispatcher.call(this);

    var defaultLibraryParameterized = selectedLibrary ? selectedLibrary.replace('.', '-').toLowerCase() : undefined;
    this.currentLibrary = selectedLibrary;

    var options = '<option value="-">-</option>';
    for (var i = 0; i < libraries.length; i++) {
        var library = libraries[i];
        var libraryName = ns.libraryToString(library);

        if (selectedLibrary === libraryName ||
            ((library.restricted === undefined || !library.restricted) &&
                library.isOld !== true
            )
        ) {
            options += '<option value="' + libraryName + '"';
            if (libraryName === selectedLibrary || library.name === defaultLibraryParameterized) {
                options += ' selected="selected"';
            }
            if (library.tutorialUrl !== undefined) {
                options += ' data-tutorial-url="' + library.tutorialUrl + '"';
            }
            if (library.exampleUrl !== undefined) {
                options += ' data-example-url="' + library.exampleUrl + '"';
            }
            options += '>' + library.title + (library.isOld===true ? ' (deprecated)' : '') + '</option>';
        }
    }

    this.$selector = ns.$('' +
        '<select class="h5p-editor-hide-in-productive-mode" name="h5peditor-library" title="' + ns.t('core', 'selectLibrary') + '"' + '>' +
        options +
        '</select>'
    ).change(function () {
        // Use timeout to avoid bug in Chrome >44, when confirm is used inside change event.
        // Ref. https://code.google.com/p/chromium/issues/detail?id=525629
        setTimeout(function () {
            if (!self.currentLibrary) {
                self.currentLibrary = self.$selector.val();
                self.trigger('selected');
                return;
            }

            self.currentLibrary = self.$selector.val();
            changeLibraryDialog.show(self.$selector.offset().top);
        }, 0);
    });
};
ns.SelectorLegacy.prototype = oldSelectorLegacyPrototype;

// overwrite files from h5peditor-image.js
ns.widgets.image.prototype.addFile = function () {
    var that = this;

    if (this.params === undefined) {

        var htmlString = '<a href="#" class="add" title="' + ns.t('core', 'addFile') + '">' +
            '<div class="h5peditor-field-file-chooseimage-text">' + ns.t('core', 'chooseImage') + '</div>' +
            '</a>&nbsp;&nbsp;';

        if (navigator.userAgent.indexOf('cordova') > -1)
        {
            htmlString += '<a href="#" class="add" title="' + ns.t('core', 'addFile') + '">' +
                '<div class="h5peditor-field-file-upload-text">' + ns.t('core', 'newImage') + '</div>' +
                '</a>';
        }

        // No image look
        this.$file
            .html(htmlString)
            .children('.add')

        var addNewImageButton = getAddNewImageButton();
        if (addNewImageButton != undefined) addNewImageButton.classList.add('h5p-editor-hidden');
        else {
            interval = setInterval(function() {
                var addNewImageButton = getAddNewImageButton();
                if (addNewImageButton == undefined) return;
                addNewImageButton.classList.add('h5p-editor-hidden');
                clearInterval(interval);
            },50);
        }

        this.$file.find('.h5peditor-field-file-chooseimage-text').click(function (){
            that.openFileSelector();
            var addNewImageButton = getAddNewImageButton();
            return false;
        });

        if (navigator.userAgent.indexOf('cordova') > -1)
        {
            this.$file.find('.h5peditor-field-file-upload-text').click(function (){
                takePictureByCordova(that);
                if (addNewImageButton != undefined) addNewImageButton.classList.remove('h5p-editor-hidden');
                return false;
            });
        }

        // Remove edit image button
        this.$editImage.addClass('hidden');
        this.$copyrightButton.addClass('hidden');
        this.isEditing = false;

        return false;
    }

    var source = H5P.getPath(this.params.path, H5PEditor.contentId);
    var altText = (this.field.label === undefined ? '' : this.field.label);
    var fileHtmlString =
        '<a href="#" title="' + ns.t('core', 'changeFile') + '" class="thumbnail">' +
        '<img alt="' + altText + '"/>' +
        '</a>'

    var editingImageButtons = document.getElementsByClassName('h5p-editing-image-button')
    var correspondingImageButton = editingImageButtons[editingImageButtons.length - 1];

    this.$file.html(fileHtmlString)
        .children(':eq(0)')
        .click(function () {
            correspondingImageButton.click();
            return false;
        })
        .children('img')
        .attr('src', source);


    // Uploading original image
    that.$editImage.removeClass('hidden');
    that.$copyrightButton.removeClass('hidden');

    // Notify listeners that image was changed to params
    that.trigger('changedImage', this.params);
    getAddNewImageButton().classList.remove('h5p-editor-hidden')

    return true;
};

var getAddNewImageButton = function () {
    var allEditorButtons = document.getElementsByClassName('h5peditor-button h5peditor-button-textual');
    for (var i = 0; i < allEditorButtons.length; i++) {
        //todo: bessere Lösung für die Selektion des Buttons
        if (allEditorButtons[i].textContent.indexOf('Weiteres Bild') > -1) {
            return allEditorButtons[i];
        }
    }
    return undefined;
}

// overwrites from h5peditor-image-popup
H5PEditor.ImageEditingPopup = (function ($, EventDispatcher) {
    var instanceCounter = 0;
    var scriptsLoaded = false;

    /**
     * Popup for editing images
     *
     * @param {number} [ratio] Ratio that cropping must keep
     * @constructor
     */
    function ImageEditingPopup(ratio) {

        EventDispatcher.call(this);
        var self = this;
        var uniqueId = instanceCounter;
        var isShowing = false;
        var isReset = false;
        var topOffset = 0;
        var maxWidth;
        var maxHeight;

        // Create elements
        var background = document.createElement('div');
        background.className = 'h5p-editing-image-popup-background hidden';

        var popup = document.createElement('div');
        popup.className = 'h5p-editing-image-popup';
        background.appendChild(popup);

        var header = document.createElement('div');
        header.className = 'h5p-editing-image-header';
        popup.appendChild(header);

        var headerTitle = document.createElement('div');
        headerTitle.className = 'h5p-editing-image-header-title';
        headerTitle.textContent = '';
        header.appendChild(headerTitle);

        var headerButtons = document.createElement('div');
        headerButtons.className = 'h5p-editing-image-header-buttons';
        header.appendChild(headerButtons);

        var editingContainer = document.createElement('div');
        editingContainer.className = 'h5p-editing-image-editing-container';
        popup.appendChild(editingContainer);

        var imageLoading = document.createElement('div');
        imageLoading.className = 'h5p-editing-image-loading';
        imageLoading.textContent = ns.t('core', 'loadingImageEditor');
        popup.appendChild(imageLoading);

        // Create editing image
        var editingImage = new Image();
        editingImage.className = 'h5p-editing-image hidden';
        editingImage.id = 'h5p-editing-image-' + uniqueId;
        editingContainer.appendChild(editingImage);

        // Close popup on background click
        background.addEventListener('click', function () {
            this.hide();
        }.bind(this));

        // Prevent closing popup
        popup.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        // Make sure each ImageEditingPopup instance has a unique ID
        instanceCounter += 1;

        /**
         * Create header button
         *
         * @param {string} coreString Must be specified in core translations
         * @param {string} className Unique button identifier that will be added to classname
         * @param {function} clickEvent OnClick function
         */
        var createButton = function (coreString, className, clickEvent) {
            var button = document.createElement('button');
            button.textContent = ns.t('core', coreString);
            button.className = className;
            button.addEventListener('click', clickEvent);
            headerButtons.appendChild(button);
        };

        /**
         * Set max width and height for image editing tool
         */
        var setDarkroomDimensions = function () {

            // Set max dimensions
            var dims = ImageEditingPopup.staticDimensions;
            maxWidth = H5P.$body.get(0).offsetWidth - dims.backgroundPaddingWidth -
                dims.darkroomPadding;

            // Only use 65% of screen height
            var maxScreenHeight = screen.height * dims.maxScreenHeightPercentage;
            var maxScreenWidth = screen.width * dims.maxScreenWidthPercentage;

            // Calculate editor max height
            var editorHeight = H5P.$body.get(0).offsetHeight -
                dims.backgroundPaddingHeight - dims.popupHeaderHeight -
                dims.darkroomToolbarHeight - dims.darkroomPadding;

            // Use smallest of screen height and editor height,
            // we don't want to overflow editor or screen
            maxHeight = Math.min(maxScreenHeight, editorHeight);
            maxWidth = Math.min(maxWidth, maxScreenWidth);
        };

        /**
         * Create image editing tool from image.
         */
        var createDarkroom = function () {
            window.requestAnimationFrame(function () {
                self.darkroom = new Darkroom('#h5p-editing-image-' + uniqueId, {
                    initialize: function () {
                        // Reset transformations
                        this.transformations = [];

                        H5P.$body.get(0).classList.add('h5p-editor-image-popup');
                        background.classList.remove('hidden');
                        imageLoading.classList.add('hidden');
                        self.trigger('initialized');
                    },
                    maxWidth: maxWidth,
                    maxHeight: maxHeight,
                    plugins: {
                        crop: {
                            ratio: ratio || null
                        },
                        save : false
                    }
                });
            });
        };

        /**
         * Load a script dynamically
         *
         * @param {string} path Path to script
         * @param {function} [callback]
         */
        var loadScript = function (path, callback) {
            $.ajax({
                url: path,
                dataType: 'script',
                success: function () {
                    if (callback) {
                        callback();
                    }
                },
                async: true
            });
        };

        /**
         * Load scripts dynamically
         */
        var loadScripts = function () {
            loadScript(H5PEditor.basePath + 'libs/fabric.js', function () {
                loadScript(H5PEditor.basePath + 'libs/darkroom.js', function () {
                    createDarkroom();
                    scriptsLoaded = true;
                });
            });
        };

        /**
         * Grab canvas data and pass data to listeners.
         */
        var saveImage = function () {

            var isCropped = self.darkroom.plugins.crop.hasFocus();
            var canvas = self.darkroom.canvas.getElement();

            var convertData = function () {
                var newImage = self.darkroom.canvas.toDataURL();
                self.trigger('savedImage', newImage);
                canvas.removeEventListener('crop:update', convertData, false);
            };

            // Check if image has changed
            if (self.darkroom.transformations.length || isReset || isCropped) {

                if (isCropped) {
                    //self.darkroom.plugins.crop.okButton.element.click();
                    self.darkroom.plugins.crop.cropCurrentZone();

                    canvas.addEventListener('crop:update', convertData, false);
                } else {
                    convertData();
                }
            }

            isReset = false;
        };

        /**
         * Adjust popup offset.
         * Make sure it is centered on top of offset.
         *
         * @param {Object} [offset] Offset that popup should center on.
         * @param {number} [offset.top] Offset to top.
         */
        this.adjustPopupOffset = function (offset) {
            if (offset) {
                topOffset = offset.top;
            }

            // Only use 65% of screen height
            var maxScreenHeight = screen.height * 0.65;

            // Calculate editor max height
            var dims = ImageEditingPopup.staticDimensions;
            var backgroundHeight = H5P.$body.get(0).offsetHeight - dims.backgroundPaddingHeight;
            var popupHeightNoImage = dims.darkroomToolbarHeight + dims.popupHeaderHeight +
                dims.darkroomPadding;
            var editorHeight =  backgroundHeight - popupHeightNoImage;

            // Available editor height
            var availableHeight = maxScreenHeight < editorHeight ? maxScreenHeight : editorHeight;

            // Check if image is smaller than available height
            var actualImageHeight;
            if (editingImage.naturalHeight < availableHeight) {
                actualImageHeight = editingImage.naturalHeight;
            }
            else {
                actualImageHeight = availableHeight;

                // We must check ratio as well
                var imageRatio = editingImage.naturalHeight / editingImage.naturalWidth;
                var maxActualImageHeight = maxWidth * imageRatio;
                if (maxActualImageHeight < actualImageHeight) {
                    actualImageHeight = maxActualImageHeight;
                }
            }

            var popupHeightWImage = actualImageHeight + popupHeightNoImage;
            var offsetCentered = topOffset - (popupHeightWImage / 2) -
                (dims.backgroundPaddingHeight / 2);

            // Min offset is 0
            offsetCentered = offsetCentered > 0 ? offsetCentered : 0;

            // Check that popup does not overflow editor
            if (popupHeightWImage + offsetCentered > backgroundHeight) {
                var newOffset = backgroundHeight - popupHeightWImage;
                offsetCentered = newOffset < 0 ? 0 : newOffset;
            }

            //popup.style.top = offsetCentered + 'px';
        };

        /**
         * Set new image in editing tool
         *
         * @param {string} imgSrc Source of new image
         */
        this.setImage = function (imgSrc) {
            // Set new image
            var darkroom = popup.querySelector('.darkroom-container');
            if (darkroom) {
                darkroom.parentNode.removeChild(darkroom);
            }

            editingImage.src = imgSrc;
            imageLoading.classList.remove('hidden');
            editingImage.classList.add('hidden');
            editingContainer.appendChild(editingImage);

            createDarkroom();
        };

        /**
         * Show popup
         *
         * @param {Object} [offset] Offset that popup should center on.
         * @param {string} [imageSrc] Source of image that will be edited
         */
        this.show = function (offset, imageSrc) {

            H5P.$body.get(0).appendChild(background);
            setDarkroomDimensions();
            if (imageSrc) {

                // Load image editing scripts dynamically
                if (!scriptsLoaded) {
                    editingImage.src = imageSrc;
                    loadScripts();
                }
                else {
                    self.setImage(imageSrc);
                }

                if (offset) {
                    var imageLoaded = function () {
                        this.adjustPopupOffset(offset);
                        editingImage.removeEventListener('load', imageLoaded);
                    }.bind(this);

                    editingImage.addEventListener('load', imageLoaded);
                }
            }
            else {
                H5P.$body.get(0).classList.add('h5p-editor-image-popup');
                background.classList.remove('hidden');
                self.trigger('initialized');
            }
            $('.h5peditor').addClass("h5p-editor-hidden");
            isShowing = true;
        };

        /**
         * Hide popup
         */
        this.hide = function () {
            isShowing = false;
            $('.h5peditor').removeClass("h5p-editor-hidden");
            H5P.$body.get(0).classList.remove('h5p-editor-image-popup');
            background.classList.add('hidden');
            H5P.$body.get(0).removeChild(background);
        };

        /**
         * Toggle popup visibility
         */
        this.toggle = function () {
            if (isShowing) {
                this.hide();
            } else {
                this.show();
            }
        };

        // Create header buttons
        createButton('resetToOriginalLabel', 'h5p-editing-image-reset-button h5p-remove', function () {
            self.trigger('resetImage');
            isReset = true;
        });
        createButton('cancelLabel', 'h5p-editing-image-cancel-button', function () {
            self.trigger('canceled');
            self.hide();
        });
        createButton('saveLabel', 'h5p-editing-image-save-button h5p-done', function () {
            var clicked = false;
            var successButtons = document.getElementsByClassName("darkroom-button darkroom-button-success");
            if (successButtons != undefined)
            {
                for (var i=0; i<successButtons.length; i++)
                {
                    if (successButtons[i].className.indexOf('hidden') > -1) continue;
                    successButtons[i].click();
                    clicked = true;
                }
            }
            if (!clicked)
            {
                saveImage();
                self.hide();
            }
        });
    }

    ImageEditingPopup.prototype = Object.create(EventDispatcher.prototype);
    ImageEditingPopup.prototype.constructor = ImageEditingPopup;

    ImageEditingPopup.staticDimensions = {
        backgroundPaddingWidth: 32,
        backgroundPaddingHeight: 96,
        darkroomPadding: 64,
        darkroomToolbarHeight: 40,
        maxScreenHeightPercentage: 0.65,
        maxScreenWidthPercentage: 0.95,
        popupHeaderHeight: 59
    };

    return ImageEditingPopup;

}(H5P.jQuery, H5P.EventDispatcher));

