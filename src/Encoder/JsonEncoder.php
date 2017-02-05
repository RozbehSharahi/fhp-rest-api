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

namespace Fhp\Rest\Encoder;

/**
 * Class Fhp\Rest\Encoder\JsonEncoder
 *
 * No documentation was created yet
 *
 * @author Rozbeh Chiryai Sharahi <rozbeh.sharahi@primeit.eu>
 * @package Fhp\Rest\Encoder
 */
class JsonEncoder
{

    /**
     * @var bool
     */
    protected $decodeAssoc = true;

    /**
     * @var integer
     */
    protected $encodeOptions = JSON_PRETTY_PRINT;

    /**
     * @param string $content
     * @return array|bool|null|\stdClass
     * @throws \Exception
     */
    public function decode($content)
    {
        try {
            return json_decode($content, $this->decodeAssoc);
        } catch (\Exception $e) {
            throw new \Exception("Json content is not valid!");
        }
    }

    /**
     * @param $fileName
     * @return array|bool|null|\stdClass
     * @throws \Exception
     */
    public function decodeFile($fileName)
    {
        if (!file_exists($fileName)) {
            throw new \Exception('Json file ' . $fileName . ' does not exist and can not be parsed');
        }

        try {
            return $this->decode(file_get_contents($fileName));
        } catch (\Exception $e) {
            throw new \Exception("Json file '.$fileName.' is not a valid json file!");
        }
    }

    /**
     * @param mixed $content
     */
    public function encode($content)
    {
        return json_encode($content, $this->encodeOptions);
    }

    /**
     * @param string $fileName
     * @param mixed $content
     * @return $this
     */
    public function encodeFile($fileName, $content)
    {
        file_put_contents($fileName, $this->encode($content));
        return $this;
    }

}