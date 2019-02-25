Open and edit tools


hints
- h5p tool: as all required metadata is handled in edu-sharing itself, it will be ignored in editor. uploading of content is disabled as it should be done in edu-sharing workspace.


knowkn bugs
- (solved) for h5p editing insert ns.libraryCache[libraryName] = undefined; in C:\xampp\htdocs\eduConnector\vendor\h5p\h5p-editor\scripts\h5peditor.js right after ns.loadLibrary = function (libraryName, callback) {