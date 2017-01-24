<?php

use Fhp\Rest\FileManager;

class FileManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider dangerousDirectoryProvider
     * @expectedException Exception
     */
    public function testDangerousDirectorySettings($directory)
    {
        $fileManager = new FileManager();
        $fileManager->createDirectory($directory);
    }

    /**
     * @return array
     */
    public function dangerousDirectoryProvider() {
        return [
            [''],
            ['/'],
            [' '],
        ];
    }

}