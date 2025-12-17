<?php

namespace connector\tools\h5p;

/**
 * custom impl of h5p file storage
 * only difference is that we map the temp path to a real temp instead of a mounted volume to improve performance
 */
class H5PStorageImpl extends \H5PDefaultStorage {
    public function getTmpPath() {
        $temp = "/tmp/h5p";
        $reflection = new ReflectionMethod(\H5PDefaultStorage::class, 'dirReady');
        $reflection->setAccessible(true);
        $reflection->invoke($this, $temp);
        return "{$temp}/" . uniqid('h5p-');
    }

}
