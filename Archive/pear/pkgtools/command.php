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

namespace Pkgtools;

use \Pkgtools\Base\Logger;

/**
* This class parses command-line
*
* @copyright Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
* @author Mathieu Parent <sathieu@debian.org>
* @license Expat http://www.jclark.com/xml/copying.txt
*/
class Command extends \Pkgtools\Base\Command {

    /**
     * Verbosity level
     *
     * @var int
     */
    protected $_verbose = 0;

    /**
     * Source directory
     *
     * @var string
     */
    protected $_sourcedirectory = '.';

    /**
     * Add command options
     */
    public function addOptions() {
        parent::addOptions();

        $this->addOption('--verbose', '-v',
            Array('action' => 'callback', 'callback' => Array($this, 'increaseVerbosity'),
                  'help' => 'increase verbosity'));
        $this->addOption('--sourcedirectory', '-D',
            Array('help' => 'set source directory'));
    }

    /**
     * Increase verbosity
     */
    public function increaseVerbosity() {
        Logger::setLevel(Logger::getEffectiveLevel() - 10);
    }
}
