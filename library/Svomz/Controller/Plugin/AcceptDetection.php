<?php
/**
 * Handle the Accept http header and inject to corresponding format into
 * the request object
 */
class Svomz_Controller_Plugin_AcceptDetection
    extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(
        Zend_Controller_Request_Abstract $request)
    {
        $header = $request->getHeader('Accept');

        switch (true) {
            case (strstr($header, 'application/json')):
                $request->setParam('format', 'json');
                break;
            case (strstr($header, 'application/xml')
                    && !strstr($header, 'html')):
                $request->setParam('format', 'xml');
                break;
        }
    }

}