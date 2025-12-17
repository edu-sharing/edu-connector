<?php

namespace connector\tools\h5p;

/**
 * custom impl of h5p file storage
 * only difference is that we map the temp path to a real temp instead of a mounted volume to improve performance
 */
class H5PStorageImpl extends \H5PDefaultStorage {
    private $temp_exports = 'tmp/h5p/exports';
    private function dirReady($dir) {
        $reflection = new \ReflectionMethod(\H5PDefaultStorage::class, 'dirReady');
        $reflection->setAccessible(true);
        return $reflection->invoke($this, $dir);
    }
    public function getTmpPath() {
        $temp = "/tmp/h5p";
        self::dirReady($temp);
        return "{$temp}/" . uniqid('h5p-');
    }

    public function deleteExport($filename) {
        $target = "{$this->temp_exports}/{$filename}";
        if (file_exists($target)) {
            unlink($target);
        }
    }

    public function saveExport($source, $filename) {
        $this->deleteExport($filename);

        if (!self::dirReady($this->temp_exports)) {
            throw new \Exception("Unable to create directory for H5P export file.");
        }

        if (!copy($source, "{$this->temp_exports}/${filename}")) {
            throw new \Exception("Unable to save H5P export file.");
        }
    }

}
