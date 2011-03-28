<?php
//doesn't work with namespace
//namespace Svomz\Controller\Action\Helper;
/**
 * Action helper
 */
class Svomz_Controller_Action_Helper_ServiceContainer
    extends \Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var sfServiceContainer
     */
    protected $_container;
    /**
     * Initialization
     */
    public function init()
    {
        $this->_container = $this->getActionController()
                ->getInvokeArg('bootstrap')->getContainer();
    }
    /**
     * Get a service or a parameter
     * @param string $name
     * @return mixed
     */
    public function direct($name)
    {
        if($this->_container->hasService($name)) {
            return $this->_container->getService($name);
        }
        else if($this->_container->hasParameter($name)) {
            return $this->_container->getParameter($name);
        }
        return null;
    }
    /**
     * Get the container object
     * @return sfServiceContainerBuilder
     */
    public function getContainer() {
        return $this->_container;
    }
}