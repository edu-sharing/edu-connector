<?php

namespace connector\tools\h5p;

class H5PEditorAjaxImpl implements \H5PEditorAjaxInterface {

    public function getLatestLibraryVersions() {
        global $db;

        // Get latest version of local libraries
        $major_versions_sql =
            "SELECT hl.name,
                MAX(hl.major_version) AS major_version
           FROM h5p_libraries hl
          WHERE hl.runnable = 1
       GROUP BY hl.name";

        $minor_versions_sql =
            "SELECT hl2.name,
                 hl2.major_version,
                 MAX(hl2.minor_version) AS minor_version
            FROM (" .$major_versions_sql .") hl1
            JOIN h5p_libraries hl2
              ON hl1.name = hl2.name
             AND hl1.major_version = hl2.major_version
        GROUP BY hl2.name, hl2.major_version";


        $query =
            "SELECT hl4.id,
                hl4.name AS machine_name,
                hl4.title,
                hl4.major_version,
                hl4.minor_version,
                hl4.patch_version,
                hl4.restricted,
                hl4.has_icon
           FROM (".$minor_versions_sql.") hl3
           JOIN h5p_libraries hl4
             ON hl3.name = hl4.name
            AND hl3.major_version = hl4.major_version
            AND hl3.minor_version = hl4.minor_version";

        return $db->query($query)->fetchAll(\PDO::FETCH_OBJ);


    }

    /**
     * Get locally stored Content Type Cache. If machine name is provided
     * it will only get the given content type from the cache
     *
     * @param $machineName
     *
     * @return array|object|null Returns results from querying the database
     */
    public function getContentTypeCache($machineName = NULL) {
        global $db;
        if ($machineName) {
            $query = "SELECT id, is_recommended FROM h5p_libraries_hub_cache WHERE machine_name=" . $db->quote($machineName);
            return $db->query($query)->fetch();
        }
        $query = "SELECT * FROM h5p_libraries_hub_cache";
        return $db->query($query)->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Gets recently used libraries for the current author
     *
     * @return array machine names. The first element in the array is the
     * most recently used.
     */
    public function getAuthorsRecentlyUsedLibraries() {}

    /**
     * Checks if the provided token is valid for this endpoint
     *
     * @param string $token The token that will be validated for.
     *
     * @return bool True if successful validation
     */
    public function validateEditorToken($token) {

return true;

    }

}