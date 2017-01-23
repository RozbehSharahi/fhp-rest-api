<?php

namespace LazerRest;

/**
 * Class LazerRest\FileManager
 *
 * All processes with files and directories should be here
 *
 * @package LazerRest
 */
class FileManager
{

    /**
     * Creates the database folder if it does not exist
     *
     * @param string $directory
     * @throws \Exception
     */
    public function createDirectory($directory)
    {
        if (!empty($directory) && $directory != '/') {
            if (!is_dir($directory) && !is_file($directory)) {
                mkdir($directory);
            }
        } else {
            throw new \Exception('Database directory is not defined');
        }
    }

}