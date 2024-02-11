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

use \Pkgtools\Base\Logger;

/**
* This class provide package name overrides
*
* @copyright Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
* @author Mathieu Parent <sathieu@debian.org>
* @license Expat http://www.jclark.com/xml/copying.txt
*/
class Overrides {

    /**
     * Overrides map
     * An array of \Pkgtools\Base\Override, or NULL if uninitialized
     *
     * @var Array|NULL
     */
    private static $_overrides = NULL;

    /**
     * Constructor
     *
     * As this class should not be instanciated, this fails
     */
    final public function __construct() {
        throw new \LogicException(__class__.' could not be instanciated');
    }

    /**
     * Load overrides from files
     *
     * @param bool $force If true, reload even if already loaded
     */
    static public function load($force = FALSE) {
        // Already loaded
        if (!is_null(self::$_overrides) && !$force) {
            return;
        }
        self::$_overrides = Array();
        self::loadFiles();
       // Builtin extensions
        $builtin_extensions = Array(
            // Statically compiled extensions
            'calendar', 'core', 'ctype', 'date', 'dba',
            'ereg', 'exif', 'fileinfo', 'filter', 'ftp', 'gettext', 'hash',
            'iconv', 'libxml', 'openssl', 'pcntl', 'pcre', 'phar', 'posix',
            'reflection', 'session', 'shmop', 'sockets',
            'spl', 'standard', 'sysvmsg', 'sysvsem', 'sysvshm', 'tokenizer',
            'xmlreader', 'xmlwriter', 'zlib',
            // Dynamically compiled extensions
            'pdo',
            // Extensions provided or no longer builtin
            //'mhash', 'json',
        );
        foreach ($builtin_extensions as $builtin_extension) {
            self::$_overrides[] = new Override('pear-pecl.php.net', $builtin_extension, 'builtin', '');
        }
    }

    /**
     * Load overrides from one file
     *
     * @param string $overrides_file File path
     */
    static private function loadFile($overrides_file) {
        if (file_exists($overrides_file)) {
            $fh = fopen($overrides_file, 'r');
            if ($fh === false) {
                throw new \LogicException("Unable to open '$overrides_file'");
            }
            while (($line = fgets($fh)) !== false) {
                $fields = preg_split("/[\s]+/", $line);
                if (count($fields) < 3) {
                    Logger::warning('Ignoring line, too short: "%s" in file "%s".', $line, $overrides_file);
                    continue;
                }
                $constraint = isset($fields[3]) ? $fields[3] : '';
                self::$_overrides[] = new Override('pear-'.$fields[0], $fields[1], $fields[2], $constraint);
            }
            if (!feof($fh)) {
                throw new \LogicException("Unable to read '$overrides_file'");
            }
            fclose($fh);
        }
    }

    /**
     * Load overrides from one file
     *
     * @param string $overrides_file File path
     */
    static private function loadFiles() {
        self::loadFile('debian/pkg-php-tools-overrides');
        // /usr/share/pkg-php-tools/overrides
        $overrides_dir = dirname(dirname(dirname(dirname(__FILE__)))) . '/pkg-php-tools/overrides';
        if (is_dir($overrides_dir)) {
            if ($dh = opendir($overrides_dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (($file == '.') || ($file == '..')) {
                        continue;
                    }
                    self::loadFile($overrides_dir.'/'.$file);
                }
                closedir($dh);
            } else {
                throw new \LogicException("Unable to open '$overrides_dir'");
            }
        }
    }

    /**
     * Apply an override
     * Return:
     * - NULL if no override found
     * - a Dependency object
     *
     * @param Dependency $dependency
     * @return Dependency|NULL
     */
    function override($dependency) {
        self::load();
        foreach(self::$_overrides as $override) {
            $overriden = $override->override($dependency);
            if (!is_null($overriden)) {
                //printLog("Overriding: $dependency -> $overriden");
                return $overriden;
            }
        }
        return NULL;
    }
}
