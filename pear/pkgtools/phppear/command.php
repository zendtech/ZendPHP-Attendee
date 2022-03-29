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
* All PEAR and PECL related commands
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
     * Print Debian package name
     *
     * @param string $channel PEAR channel.
     * @param string $name PEAR package name.
     */
    public function runDebianname($channel = NULL, $name = NULL) {
        if (($channel === NULL) && ($name === NULL)) {
            $p = new Source($this->getProperty('_sourcedirectory'));
            $dep = $p->asDependency();
        } elseif (($channel === NULL) || ($name === NULL)) {
            throw new \InvalidArgumentException('Please specify both channel and name arguments, or none');
        } else {
            $dep = new \Pkgtools\Base\Dependency('require', 'pear-'.$channel, $name);
        }
        $overriden = \Pkgtools\Base\Overrides::override($dep);
        if ($overriden === NULL) {
            echo $dep->debName();
        } else {
            echo $overriden->debName();
        }
    }


    /**
     * Print the package channel
     */
    public function runChannel() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->channel;
    }

    /**
     * Print the package summary
     */
    public function runSummary() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->summary;
    }

    /**
     * Print the package description
     */
    public function runDescription() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->description;
    }

    /**
     * Print the package maintainers list
     */
    public function runMaintainers() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        $maints = $p->getMaintainers();
        // only keep leads and developers
        $maints = array_filter($maints, function($a) { return $a['role'] == 'lead' or $a['role'] == 'developer'; });
        // format
        $maints = array_map(function($a) { return $a['name'].' <'.$a['email'].'>'; }, $maints);
        echo implode(", ", $maints);
    }

    /**
     * Print the package release date
     */
    public function runDate() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->getDate();
    }

    /**
     * Print the package version
     */
    public function runVersion() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->getVersion('release');
    }

    /**
     * Print Debian package version
     *
     * @param string $version Input version.
     */
    public function runDebianversion($version = NULL) {
        if ($version === NULL) {
            $p = new Source($this->getProperty('_sourcedirectory'));
            $version = $p->getVersion('release');
        }
        echo \Pkgtools\Phppear\Source::canonicVersion($version);
    }

    /**
     * Print the current license
     */
    public function runLicense() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->getLicense();
    }

    /**
     * Print the PEAR package type
     */
    public function runPackagetype() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        echo $p->getPackageType();
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
        $dependencies = $p->getDependencies()->asDeb();
        // Ensure no key is missing
        $dependencies = array_merge(Array(
                'require'   => Array(),
                'recommend' => Array(),
                'suggest'   => Array(),
                'conflict'  => Array()
            ),
            $dependencies);
        // Print substvars
        echo 'phppear:Debian-Depends='    . implode($dependencies['require'], ', ')."\n";
        echo 'phppear:Debian-Recommends=' . implode($dependencies['recommend'], ', ')."\n";
        echo 'phppear:Debian-Suggests='   . implode($dependencies['suggest'], ', ')."\n";
        echo 'phppear:Debian-Breaks='     . implode($dependencies['conflict'], ', ')."\n";
        $summary = $p->summary;
        if ($summary[strlen($summary)-1] == '.') {
            $summary = substr($summary, 0, -1);
        }
        echo 'phppear:summary='           . $summary."\n";
        echo 'phppear:description='       . \Pkgtools\Base\Utils::substvar($p->description)."\n";
        echo 'phppear:channel='           . $p->channel."\n";
    }

    /**
     * Print changelog
     */
    public function runChangelog() {
        $p = new Source($this->getProperty('_sourcedirectory'));
        print $p->getChangelog();
    }

}
