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
 * This class provide logging facility.
 * It is loosely inspired by python's logging
 *
 * @copyright Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
 * @author Mathieu Parent <sathieu@debian.org>
 * @license Expat http://www.jclark.com/xml/copying.txt
 */
class Logger {
    /**
     * Logging levels
     */
    const CRITICAL = 50;
    const ERROR    = 40;
    const WARNING  = 30;
    const INFO     = 20;
    const DEBUG    = 10;
    const NOTSET   = 0;

    /**
     * Current log level
     */
    private static $_level = self::WARNING;

    /**
     * Constructor
     *
     * As this class should not be instanciated, this fails
     */
    final public function __construct() {
        throw new \LogicException(__class__.' could not be instanciated');
    }

    /**
     * Set log level
     *
     * @param int $level
     */
    final public static function setLevel($level) {
        self::$_level = (int) $level;
    }

    /**
     * Get log level
     */
    final public static function getEffectiveLevel() {
        return self::$_level;
    }

    /**
     * Log a message
     *
     * @param int $level
     * @param string $message
     * @param string $args ...
     */
    final public static function log($level, $message) {
        if ($level >= self::$_level) {
            $args = func_get_args();
            array_shift($args); // $level
            if ($handle = fopen("php://stderr", "a")) {
                fwrite($handle, call_user_func_array('sprintf', $args) . "\n");
                fclose($handle);
            }
        }
    }

    /**
     * Logs a message with level DEBUG
     *
     * @param string $message
     * @param string $args ...
     */
    final public static function debug($message) {
        $args = func_get_args();
        array_unshift($args, self::DEBUG);
        call_user_func_array('self::log', $args);
    }

    /**
     * Logs a message with level INFO
     *
     * @param string $message
     * @param string $args ...
     */
    final public static function info($message) {
        $args = func_get_args();
        array_unshift($args, self::INFO);
        call_user_func_array('self::log', $args);
    }

    /**
     * Logs a message with level WARNING
     *
     * @param string $message
     * @param string $args ...
     */
    final public static function warning($message) {
        $args = func_get_args();
        array_unshift($args, self::WARNING);
        call_user_func_array('self::log', $args);
    }

    /**
     * Logs a message with level ERROR
     *
     * @param string $message
     * @param string $args ...
     */
    final public static function error($message) {
        $args = func_get_args();
        array_unshift($args, self::ERROR);
        call_user_func_array('self::log', $args);
    }

    /**
     * Logs a message with level CRITICAL
     *
     * @param string $message
     * @param string $args ...
     */
    final public static function critical($message) {
        $args = func_get_args();
        array_unshift($args, self::CRITICAL);
        call_user_func_array('self::log', $args);
    }
}
