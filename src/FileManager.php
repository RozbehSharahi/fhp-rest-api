<?php

namespace Fhp\Rest;

/**
 * Class Fhp\Rest\FileManager
 *
 * All processes with files and directories should be here
 *
 * @package Fhp\Rest
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
        if (!empty(trim($directory)) && trim($directory) != '/') {
            if (!is_dir($directory) && !is_file($directory)) {
                mkdir($directory);
            }
        } else {
            throw new \Exception('Database directory is not defined');
        }
    }

}