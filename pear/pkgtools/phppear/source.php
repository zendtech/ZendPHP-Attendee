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

namespace Pkgtools\Phppear;

/**
* This class parses package.xml
*
* @copyright Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
* @author Mathieu Parent <sathieu@debian.org>
* @license Expat http://www.jclark.com/xml/copying.txt
*/
class Source {
    /**
     * package.xml file path
     *
     * @var string
     */
    protected $_path = NULL;

    /**
     * SimpleXML
     *
     * @var SimpleXMLElement
     */
    protected $_xml = NULL;

    /**
     * package.xml version
     *
     * @var string
     */
    protected $_version = NULL;

    /**
     * Constructor
     *
     * @param string $filename
     */
    function __construct($dir_name) {
        // Find package.xml
        $dir_name = realpath($dir_name);
        foreach (Array('package2.xml', 'package.xml') as $try) {
            if (is_file("$dir_name/$try")) {
                $this->_path = "$dir_name/$try";
                break;
            }
        }
        if (is_null($this->_path)) {
            throw new \InvalidArgumentException('package.xml not found');
        }
        // Parse XML
        $use_errors = libxml_use_internal_errors(true);
        $this->_xml = simplexml_load_file($this->_path);
        /*
        foreach (libxml_get_errors() as $error) {
            var_dump($error);
        }
        // */
        libxml_clear_errors();
        libxml_use_internal_errors($use_errors);
        if ($this->_xml ===  FALSE) {
            throw new \InvalidArgumentException('Error parsing package.xml');
        }
        if ($this->_xml->getName() !== 'package') {
            throw new \InvalidArgumentException('Invalid package.xml: root is not <package>');
        }
        $attributes = $this->_xml->attributes();
        if (empty($attributes['version']) || !in_array($attributes['version'], Array('1.0','2.0', '2.1'))) {
            throw new \InvalidArgumentException('Invalid package.xml: incorrect or unsupported version '.$attributes['version']);
        }
        $this->_version = $attributes['version'];
    }

    /**
     * Raw properties getter
     *
     * @param string $property
     */
    function __get($property) {
        switch($property) {
            case 'name':
            case 'summary':
            case 'description':
                return (string) $this->_xml->{$property};
            case 'channel':
                if ($this->_version == '1.0') {
                    return 'pear.php.net';
                } else {
                    return (string) $this->_xml->{$property};
                }
            default:
                throw new \InvalidArgumentException("Unknown property: '$property'");
        }
    }

    /**
     * Get maintainers
     */
    function getMaintainers() {
        $ret = Array();
        if ($this->_version == '1.0') {
            foreach($this->_xml->maintainers as $maintainer) {
                $ret[] = Array(
                    'user'  => (string) $maintainer->user,
                    'name'  => (string) $maintainer->name,
                    'email' => (string) $maintainer->email,
                    'role'  => (string) $maintainer->role,
                );
            }
        } else {
            foreach(Array('lead', 'developer', 'contributor', 'helper') as $role) {
                foreach($this->_xml->{$role} as $maintainer) {
                    $ret[] = Array(
                        'user'   => (string) $maintainer->user,
                        'name'   => (string) $maintainer->name,
                        'email'  => (string) $maintainer->email,
                        'role'   => (string) $role,
                        'active' => (string) $maintainer->active,
                    );
                }
            }
		}
		return $ret;
    }

    /**
     * Get current date
     */
    function getDate() {
        if ($this->_version == '1.0') {
            return (string) $this->_xml->release->date;
        } else {
            return (string) $this->_xml->date;
        }
    }

    /**
     * Get current version
     *
     * @param string $key release|api
     */
    function getVersion($key = 'release') {
        if ($this->_version == '1.0') {
            return (string) $this->_xml->release->version;
        } else {
            return (string) $this->_xml->version->{$key};
        }
    }

    /**
     * Get current stability
     *
     * @param string $key release|api
     */
    function getStability($key = 'release') {
        if ($this->_version == '1.0') {
            return (string) $this->_xml->release->state;
        } else {
            return (string) $this->_xml->stability->{$key};
        }
    }

    /**
     * Get current license
     */
    function getLicense() {
        if ($this->_version == '1.0') {
            return trim((string) $this->_xml->release->license);
        } else {
            return trim((string) $this->_xml->license);
        }
    }

    /**
     * Get current notes
     */
    function getNotes() {
        if ($this->_version == '1.0') {
            return trim((string) $this->_xml->release->notes);
        } else {
            return trim((string) $this->_xml->notes);
        }
    }

    /**
     * Get package type
     *
     * @return string php|extsrc|extbin|zendextsrc|zendextbin|bundle
     */
    function getPackageType() {
        if ($this->_version == '1.0') {
            return 'php';
        }
        $types = Array(
            'phprelease'        => 'php',
            'extsrcrelease'     => 'extsrc',
            'extbinrelease'     => 'extbin',
            'zendextsrcrelease' => 'zendextsrc',
            'zendextbinrelease' => 'zendextbin',
            'bundle'            => 'bundle'
        );
        foreach($types as $tag => $type) {
            if($this->_xml->{$tag}) {
                return $type;
            }
        }
        throw new \InvalidArgumentException('Unable to get package type');
    }

    /**
     * Does the package has a file with role "script"
     */
    function hasPhpScript() {
        if ($this->_version == '1.0') {
            $scripts = $this->_xml->xpath('//file[@role="script"]');
        } else {
            $this->_xml->registerXPathNamespace('p', 'http://pear.php.net/dtd/package-2.0');
            $scripts = $this->_xml->xpath('//p:file[@role="script"]');
        }
        return !empty($scripts);
    }

    /**
     * Canonic version. ie:
     * x.ya1 -> x.y~a1
     */
    static public function canonicVersion($version) {
        if (preg_match('/^(.*\d)(alpha\d*|a\d*|beta\d*|b\d*|rc\d*)$/i', $version, $matches)) {
            return $matches[1] . '~' . $matches[2];
        } else {
            return $version;
        }
    }

    /**
     * Dependencies
     */
    function getDependencies() {
        $result = new \Pkgtools\Base\Dependencies();
        if ($this->_version == '1.0') {
            // FIXME package.xm v1.0 getDependencies()
            return $result;
        }
        if ($this->hasPhpScript()) {
            $dep = new \Pkgtools\Base\Dependency('require', '', 'php-cli');
            $result[] = $dep;
        }
        $deps0 = $this->_xml->dependencies;
        foreach ($deps0->children() as $native_level => $deps1) {
            switch($native_level) {
                case 'required':
                    $level = 'require';
                    break;
                case 'optional':
                    $level = 'recommend';
                    break;
                case 'group':
                    $level = 'suggest';
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown PEAR dependency level: '$native_level'");
            }
            foreach ($deps1->children() as $native_type => $deps2) {
                switch($native_type) {
                    case 'pearinstaller':
                        // ignore
                        continue(2);
                    case 'php':
                        $project = '';
                        $name = 'php';
                        break;
                    case 'package':
                    case 'subpackage':
                        $project = 'pear-'.$deps2->channel;
                        $name = (string) $deps2->name;
                        break;
                    case 'extension':
                        $project = '';
                        $name = 'ext-'.$deps2->name;
                        break;
                    case 'os':
                        // We ignore OS dependencies
                        continue 2;
                    default:
                        throw new \InvalidArgumentException("Unknown PEAR dependency type '$native_type'");
                }
                $dep = new \Pkgtools\Base\Dependency($level, $project, $name);
                if ($deps2->conflicts) {
                    $dep->level = 'conflict';
                }
                if ($deps2->min) {
                    $dep->minVersion = self::canonicVersion((string) $deps2->min);
                }
                if (!empty($deps2->min) && ((string) $deps2->min == (string) $deps2->exclude)) {
                    $dep->excludeMinVersion = true;
                }
                if ($deps2->max) {
                    $dep->maxVersion = self::canonicVersion((string) $deps2->max);
                }
                $dep->excludeMaxVersion = false;
                if ((string) $deps2->max == (string) $deps2->exclude) {
                    $dep->excludeMaxVersion = true;
                }
                $result[] = $dep;
            }
        }
        if ($this->_xml->extsrcrelease) {
            $phpapi = rtrim('phpapi-'.`/usr/bin/php-config --phpapi`);
            $dep = new \Pkgtools\Base\Dependency('require', '__override__', $phpapi);
            $result[] = $dep;
        }
        return $result;
    }

    /**
     * Changelog
     */
    function getChangelog() {
        $ret  = "Version ".$this->getVersion()." - ".$this->getDate()." (".$this->getStability().")\n";
        $ret .= "----------------------------------------\n";
        $ret .= "Notes:\n";
        $ret .= "  ".str_replace("\n", "\n  ", wordwrap(preg_replace('/\s+/', ' ', $this->getNotes())))."\n\n";

        if ($this->_version == '1.0') {
            $releases = $this->_xml->xpath('p:changelog/p:release');
            foreach($releases as $release) {
                $ret .= "Version ".$release->version." - ".$release->date." (".$release->state.")\n";
                $ret .= "----------------------------------------\n";
                $ret .= "Notes:\n";
                $ret .= "  ".str_replace("\n", "\n  ", wordwrap(preg_replace('/\s+/', " ", trim($release->notes))))."\n\n";
            }
        } else {
            $this->_xml->registerXPathNamespace('p', 'http://pear.php.net/dtd/package-2.0');
            $releases = $this->_xml->xpath('p:changelog/p:release');
            $releases = array_reverse($releases);
            foreach($releases as $release) {
                $ret .= "Version ".$release->version->release." - ".$release->date." (".$release->stability->release.")\n";
                $ret .= "----------------------------------------\n";
                $ret .= "Notes:\n";
                $ret .= "  ".str_replace("\n", "\n  ", wordwrap(preg_replace('/\s+/', ' ', trim($release->notes))))."\n\n";
            }
        }
        return $ret;
    }

    /**
     * Convert to a dependency
     */
    function asDependency($level = 'require') {
        $dep = new \Pkgtools\Base\Dependency($level, 'pear-'.$this->channel, $this->name);
        return $dep;
    }
}
