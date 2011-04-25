<?php
/**
 * This plugin allows to developper to use the Range header to determine
 * what is the subset of items he wants.
 * After determining the range, values are injected into the request object
 */
class Svomz_Controller_Plugin_RangeDetection 
    extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var string
     */
    const START_KEY = 'range_start';
    /**
     * @var string
     */
    const END_KEY = 'range_end';
    /**
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return null
     */
    public function dispatchLoopStartup(
        Zend_Controller_Request_Abstract $request)
    {
        if (!$range = $request->getHeader('Range')) {
                return;
        }

        //Format is Range: items=0-9
        $range = explode('=', $range);
        list($start, $end) = explode('-', $range[1]);

        $request->setParams(array(
            self::START_KEY => $start,
            self::END_KEY => $end
        ));
    }

}