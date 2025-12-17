<?php

namespace connector\tools\h5p;

/**
 * custom impl of h5p file storage
 * only difference is that we map the temp path to a real temp instead of a mounted volume to improve performance
 */
class H5PStorageImpl extends \H5PDefaultStorage {
    //public static $temp_path = '/dev/shm';
    //public static $temp_exports = '/dev/shm/exports';
    public static $temp_path = '/tmp/h5p';
    public static $temp_exports = '/tmp/h5p/exports';
    private function _dirReady($dir) {
        $reflection = new \ReflectionMethod(\H5PDefaultStorage::class, 'dirReady');
        $reflection->setAccessible(true);
        return $reflection->invoke($this, $dir);
    }
    public function getTmpPath() {
        $this->_dirReady(self::$temp_path);
        return self::$temp_path . "/" . uniqid('h5p-');
    }

    public function deleteExport($filename) {
        $target = self::$temp_path . "/" . $filename;
        if (file_exists($target)) {
            unlink($target);
        }
    }

    public function saveExport($source, $filename) {
        $this->deleteExport($filename);

        if (!$this->_dirReady(self::$temp_exports)) {
            throw new \Exception("Unable to create directory for H5P export file.");
        }
        if (!copy($source, self::$temp_exports . "/" . $filename)) {
            throw new \Exception("Unable to save H5P export file.");
        }
    }

}
