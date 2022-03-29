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
* This class represent one package name override
*
* @copyright Copyright (c) 2014 Mathieu Parent <sathieu@debian.org>
* @author Mathieu Parent <sathieu@debian.org>
* @license Expat http://www.jclark.com/xml/copying.txt
*/
class Override {

    /**
     * Project
     *
     * @var string
     */
    private $_project;

    /**
     * Package name
     *
     * @var string
     */
    private $_package;

    /**
     * Override
     *
     * @var string
     */
    private $_override;

    /**
     * Override constraint
     *
     * @var string
     */
    private $_constraint;

    /**
     * Constructor
     *
     * As this class should not be instanciated, this fails
     */
    final public function __construct($project, $package, $override, $constraint) {
        if ($project === 'pear-extension') {
            $project = 'pear-pecl.php.net';
        }
        $this->_project = $project;
        $this->_package = $package;
        $this->_override = $override;
        $this->_constraint = $constraint;
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
        if (preg_match('/(^|^pear-|\.)'.$dependency->project.'($|\.)/i', $this->_project)
             && strcasecmp(str_replace('_', '-', $dependency->package), str_replace('_', '-', $this->_package))==0
        ) {
            if ($this->_constraint === 'none') {
                $min = NULL;
                $max = NULL;
            } else {
                // Inherit the dependency
                $min = $dependency->minVersion;
                $max = $dependency->maxVersion;
            }
            $dep = new Dependency($dependency->level, '__override__', $this->_override,
                $min, $max, $dependency);
            $dep->excludeMinVersion = $dependency->excludeMinVersion;
            $dep->excludeMaxVersion = $dependency->excludeMaxVersion;
            return $dep;
        }
        return NULL;
    }
}
