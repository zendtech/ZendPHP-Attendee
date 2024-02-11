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
* This class provide various utils
*
* @copyright Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
* @author Mathieu Parent <sathieu@debian.org>
* @license Expat http://www.jclark.com/xml/copying.txt
*/
class Utils {
    /**
     * Constructor
     *
     * As this class should not be instanciated, this fails
     */
    final public function __construct() {
        throw new \LogicException(__class__.' could not be instanciated');
    }

    /**
     * Format string to substvar format:
     * - Replace tabs and drop excessive spaces
     * - Drop starting spaces
     * - Indent bullets
     * - Wrap to 80 chars
     * - Convert new lines to ${Newline}
     * - Split paragraphs
     *
     * @param bool $force If true, reload even if already loaded
     */
    static public function substvar($input) {
        // Replace tabs and drop excessive spaces
        $tmp = preg_replace('/\h+/', ' ', $input);
        // Drop starting spaces
        $tmp = preg_replace('/^ /m', '', $tmp);
        // Indent bullets
        $tmp = preg_replace('/^\*/m', ' *', $tmp);
        // Wrap to 80 chars
        $tmp = wordwrap($tmp, 78);
        // Convert new lines to ${Newline}
        $tmp = str_replace("\n", '${Newline}', $tmp);
        return $tmp;
    }
}
