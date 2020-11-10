<?php
namespace Madj2k\SpencerBrown\Utility;

/**
 * GeneralUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel 2019
 * @package Madj2k_SpencerBrown
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GeneralUtility
{

    /**
     * @param string $string
     * @param bool $upperCamelCase
     * @return string
     * @see https://stackoverflow.com/questions/2791998/convert-dashes-to-camelcase-in-php
     */
    static public function underscoreToCamelCase($string, $upperCamelCase = false)
    {

        $str = str_replace('_', '', ucwords($string, '_'));
        if (!$upperCamelCase) {
            $str = lcfirst($str);
        }
        return $str;
        //===
    }

    /**
     * @param string $string
     * @return string
     * @see https://stackoverflow.com/questions/1993721/how-to-convert-pascalcase-to-pascal-case/35719689#35719689
     */
    static public function camelCaseToUnderscore($string)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }


    /**
     * @param \Madj2k\SpencerBrown\Repository\RepositoryAbstract $repository
     * @return string
     */
    static public function getModelClassFromRepository(\Madj2k\SpencerBrown\Repository\RepositoryAbstract $repository)
    {
        return str_replace('Repository' , '', str_replace('Repository\\', 'Model\\', get_class($repository)));
    }

    /**
     * @param \Madj2k\SpencerBrown\Repository\RepositoryAbstract $repository
     * @return string
     * @throws \ReflectionException
     */
    static public function getTableNameFromRepository(\Madj2k\SpencerBrown\Repository\RepositoryAbstract $repository)
    {
        $className = (new \ReflectionClass($repository))->getShortName();
        return self::camelCaseToUnderscore(str_replace('Repository' , '', $className));
    }

}