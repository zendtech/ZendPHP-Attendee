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

namespace Pkgtools\Phppearchannel;

/**
* All PEAR channel related commands
*
* @copyright Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
* @author Mathieu Parent <sathieu@debian.org>
* @license Expat http://www.jclark.com/xml/copying.txt
*/
class Command extends \Pkgtools\Base\Command {

    /**
     * Print the channel name
     */
    public function runName() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->name;
    }

    /**
     * Print the channel summary
     */
    public function runSummary() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->summary;
    }

    /**
     * Print the channel alias
     */
    public function runSuggestedalias() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->suggestedalias;
    }

    /**
     * Print substvars (Debian substitution variables)
     */
    public function runSubstvars() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        // Print substvars
        echo 'phppear:channel-name=' .    $p->name . "\n";
        echo 'phppear:channel-summary=' . $p->summary . "\n";
        echo 'phppear:channel-alias=' .   $p->suggestedalias . "\n";
        $description  = 'This is the PEAR channel registry entry for ' . $p->suggestedalias . '.' . "\n";
        $description .= "\n";
        $description .= 'PEAR is a framework and distribution system for reusable PHP components. ';
        $description .= 'A PEAR channel is a website that provides package for download and a few extra meta-information for files.';
        $description = \Pkgtools\Base\Utils::substvar($description);
        echo 'phppear:channel-common-description=' . $description . "\n";
    }
}
