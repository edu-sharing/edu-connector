<?php


class H5peditorStorageImpl implements H5peditorStorage {


    /**
     * Load language file(JSON) from database.
     * This is used to translate the editor fields(title, description etc.)
     *
     * @param string $name The machine readable name of the library(content type)
     * @param int $major Major part of version number
     * @param int $minor Minor part of version number
     * @param string $lang Language code
     * @return string Translation in JSON format
     */
    public function getLanguage($machineName, $majorVersion, $minorVersion, $language) {

        global $db;

        // Load translation field from DB
        $asd =  $db->query('SELECT hlt.translation
           FROM h5p_libraries_languages hlt
           JOIN h5p_libraries hl ON hl.id = hlt.library_id
           WHERE hl.name = '.$db->quote($machineName).'
            AND hl.major_version = '.$majorVersion.'
            AND hl.minor_version = '.$minorVersion.'
            AND hlt.language_code ='.$db->quote($language));

return $asd->fetchColumn();
    }

    /**
     * "Callback" for mark the given file as a permanent file.
     * Used when saving content that has new uploaded files.
     *
     * @param int $fileId
     */
    public function keepFile($fileId) {}

    /**
     * Decides which content types the editor should have.
     *
     * Two usecases:
     * 1. No input, will list all the available content types.
     * 2. Libraries supported are specified, load additional data and verify
     * that the content types are available. Used by e.g. the Presentation Tool
     * Editor that already knows which content types are supported in its
     * slides.
     *
     * @param array $libraries List of library names + version to load info for
     * @return array List of all libraries loaded
     */
    public function getLibraries($libraries = NULL) {

        global $db;

        $super_user = true;//  i am super current_user_can('manage_h5p_libraries');
/*
        if ($libraries !== NULL) {
            // Get details for the specified libraries only.
            $librariesWithDetails = array();
            foreach ($libraries as $library) {
                // Look for library
                $sth = $db->query(
                    'SELECT title, runnable, restricted, tutorial_url
              FROM h5p_libraries
              WHERE name = ' . $db->quote($library->name) . ' 
              AND major_version = ' . $library->majorVersion . ' 
              AND minor_version = ' . $library->minorVersion . '
              AND semantics IS NOT NULL');

                $details = $sth->fetchAll();

                if ($details) {
                    // Library found, add details to list
                    $library->tutorialUrl = $details->tutorial_url;
                    $library->title = $details->title;
                    $library->runnable = $details->runnable;
                    $library->restricted = $super_user ? FALSE : ($details->restricted === '1' ? TRUE : FALSE);
                    $librariesWithDetails[] = $library;
                }
            }

            // Done, return list with library details
            return $librariesWithDetails;
        }*/

            // Load all libraries
            $libraries = array();
            $st = $db->query(
                "SELECT name,
                title,
                major_version AS majorVersion,
                minor_version AS minorVersion,
                tutorial_url AS tutorialUrl,
                restricted
          FROM h5p_libraries
          WHERE runnable = 1
          AND semantics IS NOT NULL
          ORDER BY title"
            );

            $libraries_result = $st->fetchAll(PDO::FETCH_CLASS);
            foreach ($libraries_result as $library) {
                // Make sure we only display the newest version of a library.
                foreach ($libraries as $key => $existingLibrary) {
                    if ($library->name === $existingLibrary->name) {

                        // Found library with same name, check versions
                        if ( ( $library->majorVersion === $existingLibrary->majorVersion &&
                                $library->minorVersion > $existingLibrary->minorVersion ) ||
                            ( $library->majorVersion > $existingLibrary->majorVersion ) ) {
                            // This is a newer version
                            $existingLibrary->isOld = TRUE;
                        }
                        else {
                            // This is an older version
                            $library->isOld = TRUE;
                        }
                    }
                }

                // Check to see if content type should be restricted
                $library->restricted = $super_user ? FALSE : ($library->restricted === '1' ? TRUE : FALSE);

                // Add new library
                $libraries[] = $library;
            }

            return $libraries;


    }

    /**
     * Alter styles and scripts
     *
     * @param array $files
     *  List of files as objects with path and version as properties
     * @param array $libraries
     *  List of libraries indexed by machineName with objects as values. The objects
     *  have majorVersion and minorVersion as properties.
     */
    public function alterLibraryFiles(&$files, $libraries) {}

    /**
     * Saves a file or moves it temporarily. This is often necessary in order to
     * validate and store uploaded or fetched H5Ps.
     *
     * @param string $data Uri of data that should be saved as a temporary file
     * @param boolean $move_file Can be set to TRUE to move the data instead of saving it
     *
     * @return bool|object Returns false if saving failed or the path to the file
     *  if saving succeeded
     */
    public static function saveFileTemporarily($data, $move_file) {}

    /**
     * Marks a file for later cleanup, useful when files are not instantly cleaned
     * up. E.g. for files that are uploaded through the editor.
     *
     * @param H5peditorFile
     * @param $content_id
     */
    public static function markFileForCleanup($file, $content_id) {}

    /**
     * Clean up temporary files
     *
     * @param string $filePath Path to file or directory
     */
    public static function removeTemporarilySavedFiles($filePath) {}


}