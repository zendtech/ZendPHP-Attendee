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

/**
* All Composer related commands
*
* @copyright Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
* @author Mathieu Parent <sathieu@debian.org>
* @license Expat http://www.jclark.com/xml/copying.txt
*/
class Command extends \Pkgtools\Base\Command {

    /**
     * Print the package name
     */
    public function runName() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->name;
    }

    /**
     * Print the package description
     */
    public function runDescription() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->description;
    }

    /**
     * Print dependencies
     */
    public function runDependencies() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        print $p->getDependencies();
    }

    /**
     * Print substvars (Debian substitution variables)
     */
    public function runSubstvars() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        // Print substvars
        echo "phpcomposer:name="        . $p->name."\n";
        $description = \Pkgtools\Base\Utils::substvar($p->description);
        if ($description[strlen($description)-1] == '.') {
            $description = substr($description, 0, -1);
        }
        echo 'phpcomposer:description=' . $description."\n";
        $dependencies = $p->getDependencies()->asDeb();
        foreach ($dependencies as $level => $deps) {
            echo "phpcomposer:Debian-$level=" . implode($deps, ', ')."\n";
        }
    }
}
