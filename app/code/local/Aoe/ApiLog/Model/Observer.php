<?php

/**
 * Observer
 *
 * @author Fabrizio Branca
 */
class Aoe_ApiLog_Model_Observer {

	/**
	 * Log api actions
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function controller_action_postdispatch_api(Varien_Event_Observer $observer) {

		$enable = Mage::getStoreConfig('dev/aoe_apilog/enablelogging');

		if (!$enable) {
			return;
		}

		$controllerAction = $observer->getControllerAction(); /* @var $controllerAction Mage_Api_IndexController */

		$logFormat = Mage::getStoreConfig('dev/aoe_apilog/logformat');
		if (empty($logFormat)) {
			$logFormat = 'AOE_APILOG: No logformat configuration found in dev/aoe_apilog/logformat';
		}

        $requestUri = $controllerAction->getRequest()->getRequestUri();
        if ((strpos($requestUri, 'wsdl=1') !== false) || (strpos($requestUri, '/wsdl/1/') !== false)) {
            return;
        }

        $request = file_get_contents('php://input');
        $response = $controllerAction->getResponse()->getBody();

		$replace = array(
			'###REQUESTURI###' => $controllerAction->getRequest()->getRequestUri(),
			'###CLIENTIP###' => $controllerAction->getRequest()->getClientIp(),
			'###REQUEST###' => $request,
			'###RESPONSE###' => $response,
            '###REQUEST_ONELINE###' => str_replace("\n", '', str_replace("\r", '', $request)),
            '###RESPONSE_ONELINE##' => str_replace("\n", '', str_replace("\r", '', $response)),
		);

		$message = str_replace(array_keys($replace), array_values($replace), $logFormat);
		$fileName = Mage::getStoreConfig('dev/aoe_apilog/logfilename');

		Mage::log($message, null, $fileName);
	}

}
