<?php declare(strict_types=1);

namespace connector\lib;

require_once __DIR__ . '/../../vendor/autoload.php';

use connector\tools\h5p\H5P;
use Exception;
use Monolog\Logger as MonoLogger;

/**
 * Class CacheCleaner
 *
 * encapsulates logic for cleaning the connector service H5P cache.
 * There is only one public function to be called: run(). Invoke it
 * to perform the cleaning process and behold its might!
 *
 * @author Marian Ziegler <ziegler@edu-sharing.net>
 */
class CacheCleaner
{
    public const LOCK_FILE_NAME = 'LOCK';
    private MonoLogger $logger;
    private Database $database;
    private H5P $h5p;

    /**
     * CacheCleaner constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Function init
     *
     * sets up dependencies
     */
    private function init(): void {
        $logInitializer = new Logger();
        $this->logger   = $logInitializer->getLog();
        $this->database = new Database();
        $this->h5p      = new H5P();
    }

    /**
     * Function run
     *
     * This is the main workhorse of the class
     *
     * @return void
     */
    public function run(): void {
        $this->logger->info('### Cache cleaner started ###');
        try {
            $this->lock();
            $this->database->beginTransaction();
            $this->clearH5pTables();
            $this->clearH5pDirectories();
            ! $this->database->commit() && throw new Exception('### Database commit failed. Script terminated ###');
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error('### Database transaction rollback started. ###');
            $this->database->rollBack();
        } finally {
            try {
                $this->unlock();
                $this->logger->info('### Cache cleaner ran successfully ###');
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }
    }

    /**
     * Function clearH5pTables
     *
     * clears H5P-related tables
     *
     * @throws Exception
     */
    private function clearH5pTables(): void {
        $libraryLanguageRes    = $this->database->query('TRUNCATE TABLE h5p_libraries_languages');
        $contentsLibrariesRes  = $this->database->query('TRUNCATE TABLE h5p_contents_libraries');
        $contentsRes           = $this->database->query('TRUNCATE TABLE h5p_contents');
        $librariesRes          = $this->database->query('TRUNCATE TABLE h5p_libraries');
        $librariesLanguagesRes = $this->database->query('TRUNCATE TABLE h5p_libraries_languages');
        $librariesLibrariesRes = $this->database->query('TRUNCATE TABLE h5p_libraries_libraries');
        if (in_array(false, [$libraryLanguageRes, $contentsLibrariesRes, $contentsRes, $librariesRes, $librariesLanguagesRes, $librariesLibrariesRes], true)) {
            throw new Exception('### Cache cleaner error: Database operation failed. Script will be terminated. ###');
        }
    }

    /**
     * Function clearH5pDirectories
     *
     * deletes all files and folders from the H5P-related directories within the cache directory
     *
     * @throws Exception
     */
    private function clearH5pDirectories(): void {
        $directories = ['content', 'exports', 'libraries', 'editor', 'temp'];
        try {
            foreach ($directories as $directory) {
                $this->h5p->rrmdir($this->h5p->H5PFramework->get_h5p_path() . '/' . $directory, true);
            }
        } catch (Exception $exception) {
            throw new Exception('### Cache cleaner error: ' . $exception->getMessage() . '. Script will be terminated. ###');
        }
    }

    /**
     * Function lock
     *
     * creates a lock file in order to lock the connector service
     * for the duration of the cache cleaning process
     *
     * @throws Exception
     */
    private function lock(): void {
        $lockFile = fopen(static::LOCK_FILE_NAME, "w");
        $lockFile === false && throw new Exception('### Cache cleaner error: Cannot create lock file. Script will be terminated. ###');
    }

    /**
     * Function unlock
     *
     * deletes the lock file in order to unlock the connector service
     *
     * @throws Exception
     */
    private function unlock(): void {
        $isFileDeleted = unlink(static::LOCK_FILE_NAME);
        ! $isFileDeleted && throw new Exception('### Cache cleaner error: Cannot delete lock file. Script will be terminated. ###');
    }
}
