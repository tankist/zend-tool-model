<?php
class Skaya_View_Helper_GoogleAnalytics extends Zend_View_Helper_Abstract {

	protected $_trackerId;

	/**
	 * Tracker options instance
	 */
	static protected $_trackerOptionsByIds = array();

	/**
	 * Available Trackers options
	 */
	static protected $_availableOptions = array(
		// Standard Options
		'trackPageview',
		'deleteCustomVar',
		'getName',
		'getAccount',
		'getVersion',
		'getVisitorCustomVar',
		'setAccount',
		'setCustomVar',
		'setSampleRate',
		'setSessionCookieTimeout',
		'setVisitorCookieTimeout',

		// ECommerce Options
		'addItem',
		'addTrans',
		'trackTrans',

		// Tracking Options
		'getClientInfo',
		'getDetectFlash',
		'getDetectTitle',
		'setClientInfo',
		'setDetectFlash',
		'setDetectTitle',
		'setAllowHash',
		'setDomainName',
		'setCookiePath',
		'setAllowLinker',
		'cookiePathCopy',
		'getLinkerUrl',
		'link',
		'linkByPost',

		// Campaign Options
		'setAllowAnchor',
		'setCampNameKey',
		'setCampMediumKey',
		'setCampSourceKey',
		'setCampTermKey',
		'setCampContentKey',
		'setCampNOKey',
		'setCampaignTrack',
		'setCampaignCookieTimeout',
		'setReferrerOverride',

		// Other
		'addOrganic',
		'addIgnoredOrganic',
		'addIgnoredRef',
		'clearOrganic',
		'clearIgnoredOrganic',
		'clearIgnoredRef',
		'setSampleRate',
		'trackEvent',
		'trackPageLoadTime',
	);

	/**
	 *
	 * @param string $trackerId the google analytics tracker id
	 * @param array $options
	 *
	 * @return Skaya_View_Helper_GoogleAnalytics
	 */
	public function GoogleAnalytics($trackerId = null, array $options = array()) {
		if (!is_null($trackerId)) {
			$this->_trackerId = $trackerId;
			$this->setAccount($trackerId);

			if (!empty($options)) {
				$this->addTrackerOptions($trackerId, $options);
			}
		}

		return $this;
	}

	/**
	 * Alias to _addTrackerOption
	 *
	 * @param string $optionsName
	 * @param array $optionsArgs
	 *
	 * @return Skaya_View_Helper_GoogleAnalytics for more fluent interface
	 */
	public function __call($optionsName, $optionsArgs) {
		if (in_array($optionsName, self::$_availableOptions) === false) {
			throw new Exception('Unknown "' . $optionsName . '" GoogleAnalytics options');
		}

		if (empty($optionsArgs)) {
			$optionsArgs = array();
		}

		$this->_addTrackerOption($this->_trackerId, $optionsName, $optionsArgs);

		return $this;
	}

	/**
	 * Add options from array
	 *
	 * @param string $trackerId the google analytics tracker id
	 * @param array of array option with first value has option name
	 *
	 * @return Skaya_View_Helper_GoogleAnalytics for more fluent interface
	 */
	public function addTrackerOptions($trackerId, array $options) {
		foreach ($options as $optionsArgs) {

			$optionsName = array_shift($optionsArgs);

			$this->_addTrackerOption($trackerId, $optionsName, $optionsArgs);
		}

		return $this;
	}

	/**
	 * Add a tracker option
	 *
	 * @param string $trackerId the google analytics tracker id
	 * @param string $optionsName option name
	 * @param array $optionsArgs option arguments
	 *
	 * @return Skaya_View_Helper_GoogleAnalytics for more fluent interface
	 */
	protected function _addTrackerOption($trackerId, $optionsName, array $optionsArgs = array()) {
		$trackerOptions = &$this->_getTrackerOptions($trackerId);

		array_unshift($optionsArgs, $optionsName);

		$trackerOptions[] = $optionsArgs;

		return $this;
	}

	/**
	 * Get tracker's options by tracker id
	 *
	 * @param string $trackerId the google analytics tracker id
	 *
	 * @return array an array of options for requested tracker id
	 */
	protected function &_getTrackerOptions($trackerId) {
		if (!isset(self::$_trackerOptionsByIds[$trackerId])) {
			self::$_trackerOptionsByIds[$trackerId] = array();
		}

		return self::$_trackerOptionsByIds[$trackerId];
	}

	//
	// Render
	//

	/**
	 * Cast to string representation
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->toString();
	}

	/**
	 * Rendering Google Anaytics Tracker script
	 * @return string
	 */
	public function toString() {
		$xhtml = array();
		$xhtml[] = 'var _gaq = _gaq || [];';

        $i = 0;
		$optionsRender = array();
        foreach (self::$_trackerOptionsByIds as $trackerId => $options) {

	        // build tracker name
	        $trackerInstance = ($i > 0 ? 'pageTracker' . $i . '.' : '');

	        // add options
	        foreach ($options as $optionsData) {

		        // build tracker func call
		        $optionName = "'" . $trackerInstance . "_" . array_shift($optionsData) . "'";

		        // escape options arg
		        $optionArgs = array();
		        foreach ($optionsData as $arg) {
			        $optionArgs[] = is_numeric($arg) ? $arg : "'" . addslashes($arg) . "'";
		        }
		        array_unshift($optionArgs, $optionName);

		        // add options
		        $optionsRender[] = "[" . join(', ', $optionArgs) . "]";
	        }

	        $i++;
        }

		$xhtml[] = "_gaq.push(" . join(",\n", $optionsRender) . ");";

        $xhtml[] = "(function() {";
        $xhtml[] = "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;";
        $xhtml[] = "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';";
        $xhtml[] = "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);";
        $xhtml[] = "})();";

        return $this->view->inlineScript()->appendScript(implode("\n", $xhtml))->__toString();
    }

}
