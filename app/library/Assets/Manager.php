<?php
/**
 * Paramatma (http://paramatma.io)
 *
 * @link      http://github.com/paramatma-io/paramatma for the canonical source repository
 * @copyright Copyright (c) 2015 Paramatma team
 * @license   http://opensource.org/licenses/MIT MIT License
 * @package   Paramatma
 */

namespace Paramatma\Assets;

/**
 * Paramatma\Assets\Manager
 * Manages collections of CSS/Javascript assets
 */
class Manager extends \Phalcon\Assets\Manager
{
    protected $_ufn = false;

    public function useUniqueJoinedFileName($ufn = true) {
        $this->_ufn = (bool) $ufn;
    }

    protected function _generateUniqueJoinedFileName($collectionName = null, $type) {
        $uName = '';
        $resources = $this->collection($collectionName)->getResources();

        foreach ($resources as $resource) {
            $uName .= $resource->getPath();
        }

        $uName = sha1($uName);

        $this->collection($collectionName)
            ->setTargetPath(ASSETS . $uName . '.' . $type)
            ->setTargetUri(ASSETS_DIR . '/' . $uName . '.' . $type);
    }

    /**
     * Traverses a collection calling the callback to generate its HTML
     *
     * @param \Phalcon\Assets\Collection $collection 
     * @param callback $callback 
     * @param string $type 
     */
    public function output(\Phalcon\Assets\Collection $collection, $callback, $type) {

        parent::output($collection, $callback, $type);
    }

    /**
     * Prints the HTML for CSS resources
     *
     * @param string $collectionName 
     */
    public function outputCss($collectionName = null) {
        if ($this->_ufn && !empty($collectionName)) {
            $this->_generateUniqueJoinedFileName($collectionName, 'css');
        }

        parent::outputCss($collectionName);
    }

    /**
     * Prints the HTML for JS resources
     *
     * @param string $collectionName 
     */
    public function outputJs($collectionName = null) {
        if ($this->_ufn && !empty($collectionName)) {
            $this->_generateUniqueJoinedFileName($collectionName, 'js');
        }

        parent::outputJs($collectionName);
    }
}
