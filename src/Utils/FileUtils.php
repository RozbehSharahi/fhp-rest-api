<?php
/**
 * FHP REST API
 *
 * ------------------------------------------------------------------------
 *
 *  Copyright (c) 2017 - Rozbeh Chiryai Sharahi <rozbeh.sharahi@primeit.eu>
 *
 * ------------------------------------------------------------------------
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fhp\Rest\Utils;

/**
 * Class Fhp\Rest\Utils\FileUtils
 *
 * All processes with files and directories should be here
 *
 * @package Fhp\Rest\Utils
 */
class FileUtils
{

    /**
     * Creates the database folder, in case it does not exist
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