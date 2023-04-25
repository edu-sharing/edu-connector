Usage:
Install via 
```bash
helm upgrade --install connector . --set baseUrl=example.repo.org --set storageClassName=storage-class --set passwordDB=example
```

View logs of Container `edu-sharing-connector-0` and check for an output like

```
Connector is ready. Please register it at your repository (Admin Tools -> Remote-Systems) with the following url:
```

Go to the admin tools of your repository and register the app.

In order to activate H5P as an editor, configure it in the repository.
Go to Admin Tools -> Global System Config -> Cluster-Override and add
```
connectorList{
  connectors:[
    {
      id:"H5P", icon:"edit", showNew: true, onlyDesktop: true, hasViewMode: false,
      filetypes:[
        {mimetype: "application/zip",filetype: "h5p", ccressourcetype: "h5p", createable: true,editable: true}
      ]
    }
  ]
}
```