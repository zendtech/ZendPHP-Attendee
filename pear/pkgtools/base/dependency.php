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

namespace Pkgtools\Base;

/**
* This class represents a dependency
*
* @copyright Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
* @author Mathieu Parent <sathieu@debian.org>
* @license Expat http://www.jclark.com/xml/copying.txt
*/
class Dependency {

    /**
     * Dependency level
     * One of:
     * - require
     * - require-dev
     * - recommend
     * - suggest
     * - conflict
     * - provide
     * - replace
     *
     * @var string
     */
    private $_level = NULL;

    /**
     * Package project
     * (Composer terminology)
     * Use an empty string for platform packages
     *
     * @var string
     */
    private $_project = NULL;

    /**
     * Package name
     *
     * @var string
     */
    private $_package = NULL;

    /**
     * Minimum version
     *
     * @var string
     */
    private $_minVersion = NULL;

    /**
     * Exclude minimum version
     * If true : $vers > $minVersion
     * If false: $vers >= $minVersion
     *
     * @var bool
     */
    private $_excludeMinVersion = false;

    /**
     * Maximum version
     *
     * @var string
     */
    private $_maxVersion = NULL;

    /**
     * Exclude maximum version
     * If true : $vers < $maxVersion
     * If false: $vers <= $maxVersion
     *
     * @var bool
     */
    private $_excludeMaxVersion = true;

    /**
     * Original (before override) dependency
     *
     * @var Dependency|NULL
     */
    private $_original = true;

    /**
     * Constructor
     *
     * @param string $level
     * @param string $project
     * @param string $package
     * @param string $minVersion
     * @param string $maxVersion
     * @param Dependency $original Original (before override) dependency
     */
    final public function __construct($level, $project, $package, $minVersion = NULL, $maxVersion = NULL, $original = NULL) {
        $this->level = $level;
        $this->project = $project;
        $this->package = $package;
        $this->minVersion = $minVersion;
        $this->maxVersion = $maxVersion;
        $this->original = $original;
    }

    /**
     * As string
     */
    final public function __toString() {
        $min = false;
        if ($this->minVersion) {
            if ($this->excludeMinVersion) {
                $min = '> '.$this->minVersion;
            } else {
                $min = '>= '.$this->minVersion;
            }
        }
        $max = false;
        if ($this->maxVersion) {
            if ($this->excludeMaxVersion) {
                $max = '< '.$this->maxVersion;
            } else {
                $max = '<= '.$this->maxVersion;
            }
        }
        if ($min && $max) {
            $version = " ($min, $max)";
        } elseif ($min) {
            $version = " ($min)";
        } elseif ($max) {
            $version = " ($max)";
        } else {
            $version = '';
        }
        return sprintf("%s:%s/%s%s", $this->level, $this->project, $this->package, $version);
    }

    /**
     * Raw properties getter
     *
     * @param string $property
     */
    function __get($property) {
        switch($property) {
            case 'level':
            case 'project':
            case 'package':
            case 'minVersion':
            case 'excludeMinVersion':
            case 'maxVersion':
            case 'excludeMaxVersion':
            case 'original':
                return $this->{"_$property"};
            default:
                throw new \InvalidArgumentException("Unknown property: '$property'");
        }
    }

    /**
     * Raw properties setter
     *
     * @param string $property
     * @param mixed $value
     */
    function __set($property, $value) {
        switch($property) {
            case 'level':
                if (!in_array($value, Array('require', 'require-dev', 'recommend', 'suggest', 'conflict', 'provide', 'replace'))) {
                    throw new \InvalidArgumentException("Unknown dependency level: '$value'");
                }
                break;
            case 'project':
                if (!preg_match('/^[a-zA-Z0-9_.-]*$/', $value)) {
                    throw new \InvalidArgumentException("Malformed dependency $property: '$value'");
                }
                // Prevent misuse
                if ($value == 'pear-extension') {
                    throw new \InvalidArgumentException("Invalid dependency project: $value");
                }
                break;
            case 'package':
                if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $value)) {
                    throw new \InvalidArgumentException("Malformed dependency $property: '$value'");
                }
                // Canonalize PECL extension
                if (substr($value, 0, 4) == 'ext-') {
                    $this->_project = 'pear-pecl.php.net';
                    $this->_package = substr($value, 4);
                    return;
                }
                break;
            case 'minVersion':
            case 'maxVersion':
                if (!is_null($value) && ($value !== 'self.version') && !preg_match('/^[0-9][a-zA-Z0-9_.~-]*$/', $value)) {
                    throw new \InvalidArgumentException("Malformed dependency $property: '$value'");
                }
                break;
            case 'excludeMinVersion':
            case 'excludeMaxVersion':
                if (!is_bool($value)) {
                    throw new \InvalidArgumentException("Malformed dependency $property: '$value'");
                }
                break;
            case 'original':
                if (!is_null($value) && !($value instanceof Dependency)) {
                    throw new \InvalidArgumentException("Malformed dependency $property: '$value'");
                }
                break;
            default:
                throw new \InvalidArgumentException("Unknown property: '$property'");
        }
        $this->{"_$property"} = $value;
    }

    /**
     * Debian package name
     */
    function debName() {
        $prefix = 'php';
        $project =  $this->project;
        $package = strtolower($this->package);
        // Builtin or none overrides
        if (($project == '__override__') && (in_array($package, Array('builtin', 'none')))) {
            return NULL;
        }
        // Generic overrides
        if ($project == '__override__') {
            return $package;
        }
        // PHP
        if (($project == '') && ($package == 'php')) {
            return 'php-common';
        }
        if (($project == '') && ($package == 'php-cli')) {
            return 'php-cli';
        }
        // lib-*
        if (($project == '') && substr($package, 0, 4) == 'lib-') {
            $lib = substr($package, 4);
            switch($lib) {
                case 'curl':
                    return 'libcurl3';
                case 'iconv':
                    // static lib
                    return NULL;
                case 'icu':
                    return 'libicu52';
                case 'libxml':
                    return 'libxml2';
                case 'openssl':
                    return 'libssl1.0.0';
                case 'pcre':
                    return 'libpcre3'; // FIXME: epoch=1
                case 'uuid':
                    // static lib
                    return NULL;
                case 'xsl':
                    return 'libxslt1.1';
                default:
                    Logger::info('Unknown dependency: "%s".', $package);
                    return NULL;
            }
        }
        // PEAR package
        if (substr($project, 0, 5) == 'pear-') {
            $channel_url = substr($project, 5);
            if ($channel_url === 'pecl.php.net') {
                $prefix = 'php';
            }
            // Split channel url by dots
            $channel_components = explode(".", $channel_url);
            // Split package name by underscores
            $package_components = explode('_', $package);
            // Drop last part of url (TLD):
            $tld = array_pop($channel_components);
            if (($tld === 'net') && (end($channel_components) === 'sourceforge')) {
                // consider sourceforge.net as a TLD
                array_pop($channel_components);
            }
            // Drop first part of url if equal to pear, pecl or www
            if (isset($channel_components[0]) && in_array($channel_components[0], Array('pear', 'pecl', 'www'))) {
                array_shift($channel_components);
            }
            // Drop first part of url if equal to php
            if (isset($channel_components[0]) && ($channel_components[0] == 'php')) {
                array_shift($channel_components);
            }
            // Drop first part of package if equal to last part of url
            if (end($channel_components) == $package_components[0]) {
                array_shift($package_components);
            }
            // PEAR: Build the debian name from remaining components
            $all_components = array_merge(Array($prefix), $channel_components, $package_components);
            return preg_replace('/[^a-zA-Z0-9.-]/', '-', implode('-', $all_components));
        }
        // Return package name if package name begins with project name
        if (strpos($package, $project) === 0) {
            return "$prefix-$package";
        }
        // Split project name by hyphen
        $project_components = explode('-', $project);
        // Split package name by hyphen
        $package_components = explode('-', $package);
        // Drop first part of package if equal to last part of url
        if (end($project_components) == $package_components[0]) {
            array_shift($package_components);
        }
        // Composer: Build the debian name from remaining components
        $all_components = array_merge(Array($prefix), $project_components, $package_components);
        return preg_replace('/[^a-zA-Z0-9.-]/', '-', implode('-', $all_components));
    }

    /**
     * Debian version
     */
    static function toDebVersion($version) {
        if ($version == 'self.version') {
            return '${binary:Version}';
        }
        return $version;
    }

    /**
     * Dependency as deb format
     */
    function debDependency() {
        $name = $this->debName();
        if (is_null($name)) {
            return NULL;
        }
	if ($name == 'php-common') {
	    return $name;
	}
        if (!$this->minVersion && !$this->maxVersion) {
            return $name;
        }
        if ($this->minVersion == $this->maxVersion) {
            return $name.' (= '.$this::toDebVersion($this->minVersion).')';
        }
        $min = false;
        if ($this->minVersion) {
            if ($this->excludeMinVersion) {
                $min = $name.' (>> '.$this::toDebVersion($this->minVersion).')';
            } else {
                $min = $name.' (>= '.$this::toDebVersion($this->minVersion).')';
            }
        }
        $max = false;
        if ($this->maxVersion) {
            if ($this->excludeMaxVersion) {
                $max = $name.' (<< '.$this::toDebVersion($this->maxVersion).')';
            } else {
                $max = $name.' (<= '.$this::toDebVersion($this->maxVersion).')';
            }
        }
        if ($min && $max) {
            return "$min, $max";
        }
        if ($min) {
            return $min;
        }
        if ($max) {
            return $max;
        }

    }
}
