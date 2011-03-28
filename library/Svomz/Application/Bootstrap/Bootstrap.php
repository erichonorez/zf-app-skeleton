<?php
use Svomz\Application\Container\ServiceContainerFactory,
    Svomz\Controller\Action\Helper\ServiceContainer;
/**
 * Bootsptrap class that use the Symfony Dependency Injection Component like
 * container.
 *
 * @author Eric Honorez <eric.honorez@gmail.com>
 */
class Svomz_Application_Bootstrap_Bootstrap
    extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * @var sfServiceContainerBuilder 
     */
    protected $_container = null;
    /**
     * Get the container used as registry by the bootstrap.
     *
     * @return sfServiceContainerBuilder
     */
    public function getContainer()
    {
        $options = $this->getOption('bootstrap');
        /*
         * Register needed autoloaders
         */
        $this->registerSfServiceAutoloaders($options);

        if(null === $this->_container) {
            /*
             * Get the service container based on $options
             */
            $this->_container =
                ServiceContainerFactory::getContainer($options['container']);
        }
        return parent::getContainer();
    }
    /**
     * Autoloading of the sfServiceContainer*
     *
     * The include path to Symfony DI Component can be configured in the
     * application.ini with the 'includePath' directive
     * @param array $config
     */
    private function registerSfServiceAutoloaders(array $config = array())
    {
        $sfIncludePath = isset($config['includePath'])
                ? $config['includePath'] : APPLICATION_PATH
                        . '/../library/Symfony/Component/DependencyInjection';

        require_once $sfIncludePath . '/sfServiceContainerAutoloader.php';
        sfServiceContainerAutoloader::register();
    }
    
}