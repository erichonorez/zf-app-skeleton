<?php
/**
 * Context switcher inspired by the Zend_Controller_Action_Helper_ContextSwitch
 * applied to HTTP methid.
 *
 * This helper allows you to define which of yours action method in your
 * controller can be reached with which HTTP method. For instance you can
 * want that the action update be only accessed with a POST request.
 * If the request use the bad method this action helper with throw an Exception
 * with these http code:
 * <ul>
 *  <li>405: A request was made of a resource using a request method not
 *  supported by that resource (GET instead of POST for instance)</li>
 *  <li>501: if the request is made with an other method of GET - POST - PUT
 *  - DELETE</li>
 * </ul>
 * The user can also define a url to redirect the user if he use a bad
 * method (and no throw an exception)
 */
class Svomz_Controller_Action_Helper_HttpMethodContext
    extends \Zend_Controller_Action_Helper_Abstract
{
    /**
     * the http status code to used when the request used a bad method
     * to access to the resource
     * @var int
     */
    const HTTP_STATUS_METHOD_NOT_ALLOWED = 405;
    /**
     * The server does not support the functionality required
     * to fulfill the request. This is the appropriate response
     * when the server does not recognize the request method and
     * is not capable of supporting it for any resource.
     * @var int
     */
    const HTTP_STATUS_NOT_IMPLEMENTED = 501;
    /**
     * @var string
     */
    const GET = 'GET';
    /**
     * @var string
     */
    const POST = 'POST';
    /**
     * @var string
     */
    const PUT = 'PUT';
    /**
     * @var string
     */
    const DELETE = 'DELETE';
    /**
     * The currentContext of the request. It can be one of the four http methods
     * supported
     *
     * @var string
     */
    private $_currentContext = null;
    /**
     * Configured context
     * 
     * @var array
     */
    private $_contexts = array();
    /**
     * URLs to redirect the user when he uses a bad http method for the
     * resource
     * 
     * @var array
     */
    private $_redirectUrls = array();
    /**
     *
     * @var bool
     */
    private $_simulateMethods = false;
    /**
     * @var string
     */
    private $_simulatedMethodQueryStringKey = 'HTTP_METHOD';
    /**
     * Avalaible http method
     * 
     * @var array
     */
    private $_availableHttpMethods = array(
        Svomz_Controller_Action_Helper_HttpMethodContext::GET,
        Svomz_Controller_Action_Helper_HttpMethodContext::POST,
        Svomz_Controller_Action_Helper_HttpMethodContext::PUT,
        Svomz_Controller_Action_Helper_HttpMethodContext::DELETE
    );
    /**
     *
     * @var <type> 
     */
    private $_availableSimulatedHttpMethods = array(
        Svomz_Controller_Action_Helper_HttpMethodContext::PUT,
        Svomz_Controller_Action_Helper_HttpMethodContext::DELETE
    );
    /**
     * do the process before the action is executed
     */
    public function preDispatch()
    {
        $this->initContext();
    }
    /**
     * Test if the given resource (action) can be reached with the http
     * method used by the request
     * 
     * @return string the method name used to access to the resource
     */
    public function initContext()
    {
        $request = $this->getRequest();
        $action = $request->getActionName();
        $method = $request->getMethod();

        $this->setCurrentContext($method);
        /*
         * if no context is configured for the given resource ($action)
         * this one accept all http mehtod
         */
        if (!isset($this->_contexts[$action])) {
            return $method;
        }
        /**
         * if the method isn't one of the context for the given action
         * it throws an exception with 405 error code.
         * if the a specific redirection url is configured the user will be
         * redirected and the exception will not throw
         */
        if (!$this->hasContext($action, $method)) {
            $url = $this->getRedirectUrl($action);
            if (null === $url) {
                throw new Exception('', self::HTTP_STATUS_METHOD_NOT_ALLOWED);
            }
            $this->_redirect($url);
        }
        /**
         * return the method used by the actual request
         */
        return $method;
    }
    /**
     * Add a context for the given $action (resource)
     * @param string $actionName
     * @param string|array $httpMethods specifies with which http method
     * the $actionName can be requested
     * @param string $redirectUrl url to use for the redirection of the user
     * if this one not use the correct http method to reach to given resource
     * @return Svomz_Controller_Action_Helper_HttpMethodContext
     */
    public function addHttpMethodContext(
            $actionName, $httpMethods, $redirectUrl = null)
    {
        $httpMethods = (array) $httpMethods;
        foreach ($httpMethods as $httpMethod) {
            if (!$this->_isValidHttpMethod($httpMethod)) {
                throw new Exception(
                    "Can't add an invalid http method to the HttpMethodContext"
                );
            }
        }
        /*
         * set the http methods for the resource
         */
        $this->_contexts[$actionName] = $httpMethods;
        /*
         * set the redirection url
         */
        if (null !== $redirectUrl) {
            $this->_redirectUrls[$actionName] = (string) $redirectUrl;
        }
        return $this;
    }
    /**
     *
     * @return Svomz_Controller_Action_Helper_HttpMethodContext
     */
    public function direct()
    {
        return $this;
    }
    /**
     * Get the current context (http method)
     * @return string
     */
    public function getCurrentContext()
    {
        return $this->_currentContext;
    }
    /**
     * Set the current method used
     * @param string $context
     * @return Svomz_Controller_Action_Helper_HttpMethodContext
     */
    public function setCurrentContext($context)
    {
        /*
         * if it's not a valid http method
         */
        if (!$this->_isValidHttpMethod($context)) {
            throw Exception('', self::HTTP_STATUS_NOT_IMPLEMENTED);
        }

        /*
         * test if we use simulated method for DELETE and PUT
         * thus we use the query string to determine the context
         * but the http method must be POST
         */
        if ($this->_simulateMethods && $context === self::POST) {
            $param = $this->getRequest()
                      ->getParam(
                        $this->getSimulatedMethodQueryStringKey());
            /*
             * if the query string is null or the value is not a valid
             * http method
             */
            if (null !== $param) {
                if (!in_array($param, $this->_availableSimulatedHttpMethods)) {
                    throw new Exception('', self::HTTP_STATUS_NOT_IMPLEMENTED);
                }
                $context = $param;
            }       
        }
        $this->_currentContext = $context;
        return $this;
    }
    /**
     * test if the given http method is one of contexts attached for the
     * given action name
     * @param string $actionName
     * @param string $httpMethodName
     * @return bool
     */
    public function hasContext($actionName, $httpMethodName)
    {
        return in_array(
            $this->getCurrentContext(), $this->getContext($actionName)
        );
    }
    /**
     * Get all contexts configured for all action
     * @param array $actionName
     * @return array
     */
    public function getContexts()
    {
        return $this->_contexts;
    }
    /**
     * Get all context for the given action
     * @param array $actionName
     * @return array
     */
    public function getContext($actionName)
    {
        if (!isset($this->_contexts[$actionName])) {
            throw new Exception('Invalid context');
        }
        return $this->_contexts[$actionName];
    }
    /**
     * Return the redirection URLs for the resource or null if no
     * url is configured
     * 
     * @param string $actionName
     * @return string
     */
    public function getRedirectUrl($actionName)
    {
        if (!isset($this->_redirectUrls[$actionName])) {
            return null;
        }
        return $this->_redirectUrls[$actionName];
    }
    /**
     * Redirect to the URL. Excecute an exit after the redirection
     * 
     * @param string $url
     */
    private function _redirect($url)
    {
        $redirector = $this->getActionController()
                    ->getHelper('Redirector')
                    ->setExit(true);
        $redirector->setGotoUrl($url);
        $redirector->redirectAndExit();
    }
    /**
     * Test if the given string is a valid http method
     * 
     * @param string $method
     */
    private function _isValidHttpMethod($method)
    {
        return in_array($method, $this->_availableHttpMethods);
    }
    /**
     *
     */
    public function simulateMethods($bool = false)
    {
        $this->_simulateMethods = (bool) $bool;
        return $this;
    }

    public function setSimulatedMethodQueryStringKey($key)
    {
        $this->_simulatedMethodQueryStringKey = (string) $key;
        return $this;
    }
    public function getSimulatedMethodQueryStringKey()
    {
        return $this->_simulatedMethodQueryStringKey;
    }
}