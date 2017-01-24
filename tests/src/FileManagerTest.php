<?php

use Fhp\Rest\FileManager;

class FileManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException Exception
     */
    public function testDangerousDirectorySettings()
    {
        $fileManager = new FileManager();
        $fileManager->createDirectory('');
    }

}