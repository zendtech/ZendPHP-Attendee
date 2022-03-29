<?php
/*
 * Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

namespace Pkgtools\Phpcomposer;

use \Pkgtools\Base\Logger;

/**
* This class parses composer.json
*
* @copyright Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
* @author Mathieu Parent <sathieu@debian.org>
* @license Expat http://www.jclark.com/xml/copying.txt
*/
class Source {
    /**
     * composer.json file path
     *
     * @var string
     */
    protected $_path = NULL;

    /**
     * Decoded composer.json
     *
     * @var mixed
     */
    protected $_json = NULL;

    /**
     * Constructor
     *
     * @param string $filename
     */
    function __construct($dir_name) {
        // Find composer.json
        $dir_name = realpath($dir_name);
        if (is_file("$dir_name/composer.json")) {
            $this->_path = "$dir_name/composer.json";
        }
        if (is_null($this->_path)) {
            throw new \InvalidArgumentException('composer.json not found');
        }
        // Load file
        $data = file_get_contents($this->_path);
        if ($data === false) {
            throw new \InvalidArgumentException("Unable to open composer.json ($this->_path)");
        }
        // Parse JSON
        if (!function_exists('json_decode')) {
            throw new \InvalidArgumentException('JSON extension is not installed or not loaded');
        }
        $this->_json = json_decode($data, true);
        if ($this->_json ===  NULL) {
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    $json_error = 'No errors';
                break;
                case JSON_ERROR_DEPTH:
                    $json_error = 'Maximum stack depth exceeded';
                break;
                case JSON_ERROR_STATE_MISMATCH:
                    $json_error = 'Underflow or the modes mismatch';
                break;
                case JSON_ERROR_CTRL_CHAR:
                    $json_error = 'Unexpected control character found';
                break;
                case JSON_ERROR_SYNTAX:
                    $json_error = 'Syntax error, malformed JSON';
                break;
                case JSON_ERROR_UTF8:
                    $json_error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
                default:
                    $json_error = 'Unknown error';
                break;
            }
            if (function_exists('json_last_error_msg')) {
                $json_error_msg = json_last_error_msg();
            } else {
                $json_error_msg = '';
            }
            throw new \InvalidArgumentException("Error parsing composer.json: $json_error ($json_error_msg)");
        }
    }

    /**
     * Raw properties getter
     */
    function __get($property) {
        switch($property) {
            case 'name':
            case 'description':
                return $this->_json[$property];
            default:
                throw new \InvalidArgumentException("Unknown property: '$property'");
        }
    }

    /**
     * Does the package has a file with role "script"
     */
    function hasPhpScript() {
        return !empty($this->_json['bin']);
    }

    /**
     * Dependencies
     */
    function getDependencies() {
        $result = new \Pkgtools\Base\Dependencies();
        $levels = Array(
            'require',
            'require-dev',
            'recommend',
            'suggest',
            'conflict',
            'provide',
            'replace',
        );
        if ($this->hasPhpScript()) {
            $dep = new \Pkgtools\Base\Dependency('require', '', 'php-cli');
            $result[] = $dep;
        } else {
            $dep = new \Pkgtools\Base\Dependency('require', '', 'php');
            $result[] = $dep;
        }
        foreach ($levels as $level) {
            if (!empty($this->_json[$level])) {
                foreach($this->_json[$level] as $project_package => $versions) {
                    Logger::debug('Parsing dependency %s:%s (%s) from file "%s".', $level, $project_package, $versions, $this->_path);
                    if (strpos($project_package, '/') !== FALSE) {
                        list($project, $package) = explode('/', $project_package, 2);
                    } else {
                        $project = '';
                        $package = $project_package;
                    }
                    $dep = new \Pkgtools\Base\Dependency($level, $project, $package);
                    if (strpos($versions, '|') !== FALSE) {
                        Logger::warning('OR-ed versions are not supported %s:%s (%s) in file "%s".', $level, $project_package, $versions, $this->_path);
                    } else {
                        try {
                            $operator_regexp = '(==?|!=|<>|>=?|<=?|~|\^)'; // $1
                            $versions = preg_replace("/([^,])\s+$operator_regexp/", '\1,\2', $versions);
                            foreach(explode(',', $versions) as $version) {
                                // Construct regexp
                                $version_regexp   = 'v?([0-9.*]*|self\.version|dev-\w+)'; // $2
                                $stability_regexp = '(-dev|-patch\d*|-alpha\d*|-beta\d*|-RC\d*)?'; // $3
                                $stabilityflag_regexp = '((?i)@dev|@alpha|@beta|@RC|@stable)?'; // $4
                                $inlinealias_regexp = '(?:\s+as\s+(\S+))?'; // $5
                                if (preg_match("/^\s*$operator_regexp?\s*$version_regexp$stability_regexp\s*$stabilityflag_regexp$inlinealias_regexp\s*$/", $version, $operator_matches)) {
                                    $operator = $operator_matches[1];
                                    $base_version = $operator_matches[2];
                                    if (!empty($operator_matches[3])) {
                                        switch($operator_matches[3][1]) {
                                            case 'd': // dev
                                            case 'p': // patch
                                                $version = $base_version . '~~' . substr($operator_matches[3], 1);
                                                break;
                                            default:
                                                $version = $base_version . '~' . substr($operator_matches[3], 1);
                                        }
                                    } else {
                                        $version = $base_version;
                                    }
                                    if (substr($version, 0, 4) == 'dev-') {
                                        Logger::info('Branch alias mapped to "*" %s:%s (%s) in file "%s".', $level, $project_package, $versions, $this->_path);
                                        $version = '*';
                                    }
                                } else {
                                    throw new \InvalidArgumentException("Unable to parse version '$version' with dependency $project_package ($versions)");
                                }
                                if (($operator == '') || ($operator == '=') || ($operator == '==')) {
                                    if (($version == '*') || ($version == '')) {
                                        // no version constraints
                                    } elseif (substr($version, -1) == '*') {
                                        // x.y.* -> (>= x.y), (<< x.y+1~~)
                                        $version_components = explode('.', $version);
                                        array_pop($version_components); // Pop '*'
                                        $last_version_component = array_pop($version_components);
                                        $dep->minVersion = implode('.', array_merge($version_components, Array($last_version_component)));
                                        $dep->maxVersion = implode('.', array_merge($version_components, Array($last_version_component + 1))) . '~~';
                                    } else {
                                        $dep->minVersion = $version;
                                        $dep->maxVersion = $version;
                                        $dep->excludeMaxVersion = false;
                                    }
                                } elseif (($operator == '!=') || ($operator == '<>')) {
                                    // We turn this into a conflict
                                    $dep2 = clone($dep);
                                    $dep2->level = 'conflict';
                                    $dep->minVersion = $version;
                                    $dep->maxVersion = $version;
                                    $dep->excludeMaxVersion = false;
                                    $result[] = $dep2;
                                } elseif (($operator == '>') || ($operator == '>=')) {
                                    $dep->minVersion = $version;
                                    $dep->excludeMinVersion = $operator == '>';
                                } elseif ($operator == '<') {
                                    $dep->maxVersion = $base_version.'~~';
                                    $dep->excludeMaxVersion = true;
                                } elseif ($operator == '<=') {
                                    $dep->maxVersion = $version;
                                    $dep->excludeMaxVersion = false;
                                } elseif ($operator == '~') {
                                    // ~x.y.z -> (>= x.y.z), (<< x.y+1~~)
                                    $version_components = explode('.', $version);
                                    if (count($version_components) > 1) {
                                        array_pop($version_components);
                                        $last_version_component = array_pop($version_components);
                                    } else {
                                        $last_version_component = array_pop($version_components);
                                    }
                                    $dep->minVersion = $version;
                                    $dep->maxVersion = implode('.', array_merge($version_components, Array($last_version_component + 1))) . '~~';
                                } elseif ($operator == '^') {
                                    // ^x.y.z -> (>= x.y.z), (<< x+1~~)   if x >= 1
                                    // ^x.y.z -> (>= x.y.z), (<< x.y+1~~) if x == 0
                                    $version_components = explode('.', $version);
                                    $prefix_components = Array();
                                    $significant_version_component = array_shift($version_components);
                                    if ($significant_version_component == '0') {
                                        array_unshift($prefix_components, $significant_version_component);
                                        $significant_version_component = array_shift($version_components);
                                    }
                                    $dep->minVersion = $version;
                                    $dep->maxVersion = implode('.', array_merge(
                                        $prefix_components,
                                        Array($significant_version_component + 1))). '~~';
                                } else {
                                    throw new \InvalidArgumentException("Unable to parse version operator '$operator' with dependency $project_package ($versions)");
                                }
                            }
                        } catch(\Exception $e) {
                            // suggest can have free text in place of version constraints
                            if ($dep->level != 'suggest') {
                                throw new \Exception($e->getMessage(). " with dependency $project_package ($versions)", $e->getCode(), $e);
                            }
                        }
                    }
                    $result[] = $dep;
                }
            }
        }
        return $result;
    }
}
