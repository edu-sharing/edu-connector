<?php
$id = preg_replace('/[^a-f0-9]/', '', $_GET["id"]);
?>
(function (window, undefined) {
    let repoConfig, viewEduObject;

    function changeBranding(string) {
        return string.replace('${brandingName}', repoConfig?.brandingName || 'edu-sharing');
    }

    window.Asc.plugin.init = function (objectData) {
        if (objectData) {
            objectData = JSON.parse(objectData);
        }
        document.getElementById("textbox_button").onclick = function (e) {

            if (!viewEduObject) {
                document.getElementById("eduViewer").innerHTML = '<div id="content"></div>';
                viewEduObject = true;
                window.Asc.plugin.resizeWindow(620, 480);
            }

            const inline = '<div class="eduContainer" data-type="esObject">' +
                '<div class="edusharing_spinner_inner"><div class="edusharing_spinner1"></div></div>' +
                '<div class="edusharing_spinner_inner"><div class="edusharing_spinner2"></div></div>' +
                '<div class="edusharing_spinner_inner"><div class="edusharing_spinner3"></div></div>' +
                '</div>';

            document.getElementById("content").innerHTML = inline;

            const windowHeight = (620 * objectData.nodeHeight) / objectData.nodeWidth;
            window.Asc.plugin.resizeWindow(620, parseInt(windowHeight, 10) + 150);
        };
        window.document.getElementById("repo_btn").onclick = function (e) {
            open_repo();
        }

        const pluginRef = this;
        fetch("repo_config.php?id=<?php echo $id; ?>")
            .then(response => response.json())
            .then(function (response) {
                repoConfig = response;
                if (objectData && objectData.id) {
                    getRenderData(objectData).then(() => {

                        document.getElementById("repoMenu").style.display = "none";

                    });
                }
                if (!(objectData.url == "" || objectData.url == null)) {
                    document.getElementById("textbox_button").onclick();
                }

            });

        async function checkRenderer2() {
            try {
                const response = await fetch(repoConfig.repoUrl + 'rest/_about');
                const jsonResponse = await response.json()

                if (jsonResponse.renderingService2.url) {
                    window.__env = {
                        EDU_SHARING_API_URL: repoConfig.repoUrl + "/rest"
                    }
                    return true;
                } else {
                    return false;
                }
            } catch (e) {
                // console.log(e);
                return false;
            }

        }

        async function getRenderData(objectData) {
            function loadRenderer1() {
                let xhr = new XMLHttpRequest();
                xhr.open("POST", repoConfig.repoUrl + "rest/rendering/v1/details/" + objectData.nodeRepo + "/" + objectData.id + '?displayMode=inline', true);
                xhr.setRequestHeader("Content-type", "application/json");
                xhr.setRequestHeader("Accept", "application/json");
                xhr.crossDomain = true;
                xhr.withCredentials = true;
                xhr.setRequestHeader("Authorization", "EDU-TICKET " + repoConfig.ticket);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status === 200) {
                        const response = JSON.parse(xhr.response);

                        const renderData = response.detailsSnippet.replace('{{{LMS_INLINE_HELPER_SCRIPT}}}&closeOnBack=true', objectData.nodePermaLink);
                        const eduObject = '<div id="eduContainer" class="eduContainer" data-type="esObject" data-url="' +
                            '?mimetype=' + objectData.nodeMimeType +
                            '&caption=' + objectData.nodeCaption +
                            '&width=' + objectData.nodeWidth +
                            '">' + renderData + '</div>';
                        document.getElementById("content").innerHTML = eduObject;
                    }
                }
                //xhr.send('{"width":"'+ objectData.nodeWidth +'"}');
                xhr.send('{"width":"' + 620 + '"}');
            }

            async function loadRenderer2() {
                const mainScript = document.createElement('script')
                mainScript.type = "module"
                mainScript.src = repoConfig.repoUrl + 'web-components/rendering-service/main.js'

                const styleScript = document.createElement('link')
                styleScript.rel = "stylesheet"
                styleScript.href = repoConfig.repoUrl + 'web-components/rendering-service/styles.css'

                document.body.appendChild(mainScript)
                document.body.appendChild(styleScript)


                const serviceWorkerScript = "./service_worker.php?id=<?php echo $id; ?>"

                if ('serviceWorker' in navigator) {
                    await navigator.serviceWorker.register(serviceWorkerScript, {
                        scope: '/'
                    })
                    await navigator.serviceWorker.ready
                }

                const nodeId = objectData.id;
                const repoId = objectData.nodeRepo;

                const securedNodeUrl = `${repoConfig.repoUrl}rest/node/v1/nodes/${repoId}/${nodeId}/metadata/secured?ticket=${repoConfig.ticket}`;
                const rawResponse = await fetch(securedNodeUrl, {credentials: 'include'});
                const response = await rawResponse.json();

                const renderComponent = document.createElement('edu-sharing-render');
                renderComponent.classList.add('edu-sharing-render');
                renderComponent.encoded_node = response.signedNode;
                renderComponent.signature = response.signature;
                renderComponent.jwt = response.jwt;
                renderComponent.render_url = response.renderingBaseUrl;
                //renderComponent.encoded_user = btoa(JSON.stringify(eduUser));
                renderComponent.service_worker_url = '';
                renderComponent.activate_service_worker = false;
                renderComponent.assets_url = repoConfig.repoUrl + '/web-components/rendering-service/assets';
                renderComponent.resource_url = objectData.nodePermaLink;
                renderComponent.preview_url = objectData.nodePreviewUrl;

                const contentHeight = 600; // px
                const footerHeight = 60;

                renderComponent.target_blank = true;
                renderComponent.component_height = contentHeight;
                renderComponent.footer_height = footerHeight;

                window.Asc.plugin.resizeWindow(620, contentHeight + footerHeight);


                let contentElement = document.getElementById("content");
                contentElement.innerHTML = ''
                contentElement.appendChild(renderComponent);


            }

            let renderer2Active = await checkRenderer2()

            if (renderer2Active) {
                await loadRenderer2();
            } else {
                loadRenderer1();
            }

        }

        //open the repo & get data
        function open_repo() {
            //Window-Event-Listener gets the Objects data and sets the usage
            window.addEventListener('message', function handleRepo(event) {
                if (event.data.event == "APPLY_NODE") {
                    const node = event.data.data;
                    window.win.close();
                    nodeID = node.ref.id;
                    nodePreviewUrl = node.preview.url;
                    if (!node.properties["ccm:height"]) {
                        nodeWidth = 266;
                        nodeHeight = 200;
                    } else {
                        nodeWidth = node.properties["ccm:width"][0];
                        nodeHeight = node.properties["ccm:height"][0];
                    }
                    nodeTitle = node.title || node.properties["cm:name"];
                    nodeCaption = node.description;
                    nodeMimeType = node.mimetype;
                    nodePermaLink = node.content.url;
                    nodeRepo = node.ref.repo;
                    nodeFreeTextAuthor = (node.properties['ccm:author_freetext'] ?? []).join(', ').trim();
                    nodeAuthorsFN = (node.properties['ccm:lifecyclecontributer_authorFN'] ?? []).join(', ').trim();
                    nodeOrgs = (node.properties['ccm:lifecyclecontributer_authorVCARD_ORG'] ?? []).join(', ').trim();
                    nodeAuthors = nodeAuthorsFN + (nodeOrgs ? ', ' + nodeOrgs : '') + (nodeFreeTextAuthor ? ', ' + nodeFreeTextAuthor : '');
                    nodeLicenseIcon = node.license.icon
                    nodeLicenseUrl = node.license.url


                    if (!viewEduObject) {
                        const contentHtml = '<div class="eduContent">' +
                            '<div id="content"></div>' +
                            '</div>';

                        document.getElementById("eduViewer").innerHTML = contentHtml;
                        viewEduObject = true;

                        const windowHeight = (620 * nodeHeight) / nodeWidth;
                        window.Asc.plugin.resizeWindow(620, parseInt(windowHeight, 10) + 150);
                    }

                    const eduPreview = '<div class="eduPreview">' +
                        '<img src="' + nodePreviewUrl + '&ticket=' + repoConfig.ticket + '">' +
                        '<h3>' + nodeTitle + '</h3>' +
                        '</div>';

                    document.getElementById("content").innerHTML = eduPreview;

                    //remove event listener
                    window.removeEventListener('message', handleRepo, false);
                }
            }, false);

            window.win = window.open(repoConfig.repoUrl + '/components/search?&applyDirectories=true&reurl=WINDOW&ticket=' + repoConfig.ticket);
        }

        function scaleImage(scale) {
            nodeWidth = Math.round(nodeWidth * (scale / 100));
            nodeHeight = Math.round(nodeHeight * (scale / 100));
        }

    };
    window.Asc.plugin.button = function (id) {
        if (id !== 0) {
            // button id 0 is ok
            // button id 1 is cancel
            this.executeCommand("close", "");
            return;
        }

        if ((typeof nodeID === "undefined" || !nodeID)) {
            this.executeCommand("close", "");
            return;
        }


        const _info = window.Asc.plugin.info;
        const _method = (_info.objectId === undefined) ? "AddOleObject" : "EditOleObject";
        let width = nodeWidth / _info.mmToPx;
        let height = nodeHeight / _info.mmToPx;
        if (width >= 150) {
            height = (150 * height) / width;
            width = 150;
        }
        const _param = {
            guid: _info.guid,
            width: _info.width ? _info.width : parseInt(width, 10),
            height: _info.height ? _info.height : parseInt(height, 10),
            widthPix: (_info.mmToPx * _info.width) >> 0,
            heightPix: (_info.mmToPx * _info.height) >> 0,
            imgSrc: nodePreviewUrl + '&format=png&ticket=' + repoConfig.ticket,
            data: '{ "id":"' + nodeID + '",' +
                '"url":"' + nodePreviewUrl + '",' +
                '"nodeWidth":"' + nodeWidth + '",' +
                '"nodeHeight":"' + nodeHeight + '",' +
                '"nodeTitle":"' + nodeTitle + '",' +
                '"nodeCaption":"' + nodeCaption + '",' +
                '"nodePermaLink":"' + nodePermaLink + '",' +
                '"nodeMimeType":"' + nodeMimeType + '",' +
                '"nodeRepo":"' + nodeRepo + '"' +
                '}',
            objectId: _info.objectId,
            resize: _info.resize
        };
        //send OLE-object to document
        window.Asc.plugin.executeMethod(_method, [_param], function () {
            window.Asc.plugin.executeCommand("command", "");
        });


        let authorText = nodeAuthors ? ` ${window.Asc.plugin.tr("by_author")} ${nodeAuthors}  ` : '  ';
        var text = nodeTitle + authorText;
        var eduIcon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAMAAAC6V+0/AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHCUExURTFip8LH5MHG44CSxYCRxYCSxIGSxcHG5HqMwr7E4r3C4fn6/CVZotLW6sDF4+fp83+QxMLG4y1fpYunzr/E4r/F4r/I4XiLwSRYotHY6iRYoXmLwoGSxniKwfDx99LX5d3h7zBip8DP5NHV6naIwLO93PT3+niLwCBVn0Rwr36PxHeKwevt9e3v9XmMwvT1+TZmqenu9d7m8Jmx0+nu9jBhpsPS5cbN5HuNws/W6cLQ5MjV536cx3aWxM3R6PL1+bvB4cHG4nSHv97i8JKgxy1fpvn5+dLV6uLm8S9gpnybxypdpCtdpGKIvHyOwsrP5xpRniZZotre7oqmzcLI4+Pl8XeKwOzu9klzsd7j74CRxIShyu7w9/j5/MPR5dba7NHc6tjd7Zuz1CVYorDD3a3A29PW6tDT6b/F48bL5XyOwx5Tn8LH4zhnqsTS5tDX6Shco4eXyOHo8r7E4efq83yNwyhbo3+Rw2OHvClcpJakz7W/3dPZ6sPS5r7D4o2dyoumzcHH47/E49ve7svQ59DW6IqZxMTM46iz1r3D4rzF4MLI5MXK5CBVoPL0+b7H4SdaomOIvH+Rxdjc53qMwQAAAM1wQbgAAACWdFJOU///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////AGkSznoAAAEmSURBVHjaVNFVb8NQDAXgC0maJmXu2o6ZmbeOmZmZmZmZ2f93N520pn78pGMd2QggI6Zer583QnAQwFyFTauVXmQ19m8TQjB57FLjjQ0zxKQyPIi9kiKEmLXlQUy7NLAwxvisGgreHY6nEyV+LZkZ4YV2uK3q8yXE7qcylBevNBqisY+MnrfxAqXpD0olo2Gl59k+DsdOynsFgYoR6H/74O4XN+TlBH7pjqG8NpZ/Gp27Q02UpxxHC2sYTiXuHW68HsC9h+OZmZK/EWxlWhBCUdMzcCQyFXxxdQjKdEiZliR/pNMj/qRsvgEqbs4LIAprgsl1t7uogZWfdf0Z6vxUHaT1I2CW1WwVTtQ2BtI5IUcu7XDpdMsD8SGY1X1htQ6XqN7xK8AAYpOAIExuNXEAAAAASUVORK5CYII=';
        Asc.scope.text = text;
        Asc.scope.img = eduIcon;
        Asc.scope.nodeLicenseIcon = nodeLicenseIcon
        Asc.scope.nodeLicenseUrl = nodeLicenseUrl
        this.callCommand(function () {
            var oDocument = Api.GetDocument();
            oDocument.CreateNewHistoryPoint();
            oDocument.InsertParagraphBreak();
            // move cursor 1 step to right -> otherwise it overwrites the img
            oDocument.MoveCursorRight(1);

            var oParagraph = Api.CreateParagraph();

            // spacer will add a smaller linebreak between ole object and text/license
            var spacerParagraph = Api.CreateParagraph()
            var spacer = Api.CreateRun();
            spacerParagraph.SetFontSize(1);
            spacer.SetFontSize(1);
            spacer.AddText(" ");
            spacer.AddLineBreak();
            spacerParagraph.AddElement(spacer);


            var oRun = Api.CreateRun();
            oRun.SetFontSize(24);
            oRun.SetHighlight(221, 221, 221);
            oRun.SetColor(85, 85, 85);
            oRun.AddText(Asc.scope.text);
            oParagraph.AddElement(oRun);


            // 16pt font-size * 12700 ~ pt -> EMU (english metric unit)
            const fontsize = 16;
            let width = fontsize * 12700;
            let height = fontsize * 12700;

            var oLicenseImg = Api.CreateImage(Asc.scope.nodeLicenseIcon, width, height)

            oParagraph.AddDrawing(oLicenseImg);


            // oParagraph.AddHyperlink(Asc.scope.nodeLicenseUrl)
            oDocument.InsertContent([spacerParagraph, oParagraph]);


            new Promise((resolve, reject) => {
                try {
                    const img = new Image()
                    img.onload = () => {
                        Asc.scope.nodeLicenseIconRatio = img.width / img.height
                        resolve(Asc.scope.nodeLicenseIconRatio)
                    }
                    img.src = Asc.scope.nodeLicenseIcon
                } catch (e) {
                    resolve(-1)
                }
            }).then((r) => {
                if (r > 0) {
                    // only resize if ratio is positive
                    width = fontsize * Asc.scope.nodeLicenseIconRatio * 12700;
                    oLicenseImg.SetSize(width, height)
                }

            })


        }, true);


    };

    window.Asc.plugin.onEnableMouseEvent = function (isEnabled) {
        let _frames = document.getElementsByTagName("iframe");
        if (_frames && _frames[0]) {
            _frames[0].style.pointerEvents = isEnabled ? "none" : "";
        }
    };

    window.Asc.plugin.onTranslate = function () {
        console.log('Translate', window.Asc.plugin.tr("view_edit_title"));
        document.getElementById("eduHeader_label").innerHTML = changeBranding(window.Asc.plugin.tr("view_edit_title"));
        document.getElementById("repoMenu_label").innerHTML = changeBranding(window.Asc.plugin.tr("view_edit_open"));
        document.getElementById("repo_btn").innerHTML = changeBranding(window.Asc.plugin.tr("open_repo"));
        document.getElementById("textbox_button").innerHTML = window.Asc.plugin.tr("ok");
    };
})(window, undefined);