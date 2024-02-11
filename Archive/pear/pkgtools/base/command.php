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
* This class parses command-line
*
* @copyright Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
* @author Mathieu Parent <sathieu@debian.org>
* @license Expat http://www.jclark.com/xml/copying.txt
*/
abstract class Command {

    /**
     * Parent command
     *
     * @var BaseCommand
     */
    private $_parentCommand;

    /**
     * Sub-command
     *
     * @var BaseCommand
     */
    private $_subCommand = NULL;

    /**
     * Options
     *
     * @var Array
     */
    private $_options = Array();

    /**
     * Available actions
     *
     * @var Array
     */
    public $ACTIONS = array(
        'store',
        // Not implemented: 'store_const',
        'store_true',
        'store_false',
        // Not implemented: 'append',
        // Not implemented: 'append_const',
        'count',
        'callback',
        'help',
        // Not implemented: 'version',
    );

    /**
     * Constructor
     *
     * @param BaseCommand $parentCommand
     */
    final public function __construct($parentCommand = NULL) {
        $this->_parentCommand = $parentCommand;
        $this->addOptions();
    }

    /**
     * Add an option to the parser
     *
     * @param string $opt1
     * @param string ...
     * @param Array $attributes
     */
    final public function addOption() {
        $opts = func_get_args();
        if (count($opts) < 2) {
            throw new \InvalidArgumentException('addOption() used with unsufficient argument');
        }
        $attributes = array_pop($opts);
        if (empty($attributes['action'])) {
            $attributes['action'] = 'store';
        } elseif (!in_array($attributes['action'], $this->ACTIONS)) {
            throw new \InvalidArgumentException(sprintf("invalid action: '%s'", $attributes['action']));
        }
        if (!array_key_exists('dest', $attributes)) {
            if (substr($opts[0], 0, 2) == '--') {
                $attributes['dest'] = '_'.substr($opts[0], 2);
            } elseif ($opts[0][0] == '-') {
                $attributes['dest'] = '_'.substr($opts[0], 1);
            } else {
                throw new \InvalidArgumentException('Option should begin with a dash');
            }
        }
        foreach ($opts as $opt) {
            $this->_options[$opt] = $attributes;
        }
    }

    /**
     * Parse arguments and run
     */
    final public function parseArgs($args = NULL) {
        if (is_null($args)) {
            $args = $_SERVER['argv'];
            // Remove script name
            array_shift($args);
        }
        while ($arg = array_shift($args)) {
            if (array_key_exists($arg, $this->_options)) {
                switch ($this->_options[$arg]['action']) {
                    case 'store':
                        $this->{$this->_options[$arg]['dest']} = array_shift($args);
                        continue 2;
                    case 'store_true':
                        $this->{$this->_options[$arg]['dest']} = true;
                        continue 2;
                    case 'store_false':
                        $this->{$this->_options[$arg]['dest']} = false;
                        continue 2;
                    case 'count':
                        $this->{$this->_options[$arg]['dest']}++;
                        continue 2;
                    case 'callback':
                        call_user_func_array($this->_options[$arg]['callback'], Array(
                            $this->_options[$arg],
                            $arg,
                            NULL, // value
                            $this,
                            Array(), // $args
                            Array(), // $kwargs
                            ));
                        continue 2;
                    case 'help':
                        return $this->help();
                    default:
                        throw new \InvalidArgumentException(sprintf("invalid action: '%s'", $this->_options[$arg]['action']));
                }
            }
            if (strlen($arg) && ($arg[0] == '-')) {
                throw new \InvalidArgumentException("Unknown option $arg");
            }
            $class = preg_replace('/Command$/', ucfirst($arg).'\\Command', get_class($this));
            // Try to load \Pkgtools\...\$Arg\Command class
            try {
                if (class_exists($class)) {
                    $this->_subCommand = new $class($this);
                    return $this->_subCommand->parseArgs($args);
                }
            } catch (\LogicException $e) {
                // spl_autoload thrown an LogicException
                // "Class $class could not be loaded"
                if ($e->getTrace()[0]['function'] != 'spl_autoload') {
                   throw $e;
                }
            }
            // Try to use $this->run$Arg()
            if (method_exists($this, 'run'.ucfirst($arg))) {
                Logger::debug('Launching %s::%s().', get_class($this), 'run'.ucfirst($arg));
                return call_user_func_array(Array($this, 'run'.ucfirst($arg)), $args);
            }
            throw new \InvalidArgumentException("Unknown sub-command $arg");
        }
        // Try to use $this->run()
        if (method_exists($this, 'run')) {
            Logger::debug('Launching %s::%s().', get_class($this), 'run');
            return call_user_func(Array($this, 'run'));
        }
        throw new \InvalidArgumentException("Missing sub-command name");
    }

    /**
     * Recursively get option value
     */
    final public function getProperty($name) {
        if (property_exists($this,$name)) {
            return $this->{$name};
        }
        if (!is_null($this->_parentCommand)) {
            return $this->_parentCommand->getProperty($name);
        }
        throw new \InvalidArgumentException("Unknown property: '$name'");
    }

    /**
     * Split doc comment body and attributes
     */
    static final protected function parseDocComment($str) {
        $comments = preg_replace('@^/\*\*(.*)\*/$@s', '\1', $str);
        $comments = preg_replace('@^\s*\*@m', '', $comments);
        $comments = explode("\n", $comments);
        $comments = array_map('trim', $comments);
        $ret = Array();
        $ret['body'] = '';
        foreach($comments as $comment) {
            if (preg_match('/@([a-z]+)\s+(.*)/', $comment, $matches)) {
                $ret[$matches[1]][] = $matches[2];
            } else {
                $ret['body'].= $comment."\n";
            }
        }
        $ret['body'] = trim($ret['body']);
        return $ret;
    }

    /**
     * Print command help
     */
    final public function help() {
        echo "Usage:\n";
        echo '    '.str_replace('command', 'COMMAND', strtolower(strtr(\get_class($this), '\\', ' ')))."\n";
        echo "\n";

        echo "Options:\n";
        foreach($this->_options as $k => $opt) {
            echo "    $k:";
            if (isset($opt['help'])) {
                echo ' '.$opt['help'];
            }
            echo "\n";
        }
        echo "\n";

        echo "Commands:\n";
        $rc = new \ReflectionClass($this);
        foreach($rc->getMethods() as $method) {
            if (substr($method->name, 0, 3) != 'run') {
                continue;
            }
            $command = strtolower(substr($method->name, 3));
            $comment = $this::parseDocComment($method->getDocComment());
            echo "  $command: ".$comment['body']."\n";
        }
        // Commands from external files
        if ($classFile = $rc->getFileName()) {
            $classFileInfo = new \SplFileInfo($classFile);
            $directoryIterator = $classFileInfo->getPathInfo('\\DirectoryIterator');
            $comments = Array();
            foreach ($directoryIterator as $fileInfo) {
                if ($fileInfo->isDot() || !$fileInfo->isDir() || ($fileInfo->getFilename() === 'base')) {
                    continue;
                }
                $class = preg_replace('/Command$/', ucfirst($fileInfo->getFilename()).'\\Command', get_class($this));
                // Try to load \Pkgtools\...\$Filename\Command class
                try {
                    if (class_exists($class)) {
                        $rc = new \ReflectionClass($class);
                        $comment = $rc->getDocComment();
                        $comment = $this::parseDocComment($rc->getDocComment());
                        $comments[] = '  '.$fileInfo->getFilename().': '.$comment['body']."\n";
                    }
                } catch (\LogicException $e) {
                    // spl_autoload thrown an LogicException
                    // "Class $class could not be loaded"
                    if ($e->getTrace()[0]['function'] != 'spl_autoload') {
                       throw $e;
                    }
                }
            }
            sort($comments); // Make output deterministic
            echo implode($comments);
        }
    }

    // **********************************************************************
    // Overridables methods
    // **********************************************************************
    /**
     * Add command options
     */
    public function addOptions() {
        $this->addOption('--help', '-h',
            Array('action' => 'help', 'help' => 'print help'));
    }

    /**
     * Without arguments: print help
     */
    public function run() {
        $this->help();
    }
}
