<?php

/**
 * Autoloader
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel 2019
 * @package Madj2k_SpencerBrown
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Autoloader extends  \Composer\Autoload\ClassLoader
{

    /**
     * Autoloader constructor.
     * We load the configuration here
     */
    public function __construct()
    {
        //require_once(__DIR__ . '/../config/settings.php');

    }

}