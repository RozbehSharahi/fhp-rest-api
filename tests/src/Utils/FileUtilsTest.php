<?php

use Fhp\Rest\Utils\FileUtils;

class FileUtilsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider dangerousDirectoryProvider
     * @expectedException Exception
     */
    public function testDangerousDirectorySettings($directory)
    {
        $fileManager = new FileUtils();
        $fileManager->createDirectory($directory);
    }

    /**
     * @return array
     */
    public function dangerousDirectoryProvider()
    {
        return [
            [''],
            ['/'],
            [' '],
        ];
    }

}