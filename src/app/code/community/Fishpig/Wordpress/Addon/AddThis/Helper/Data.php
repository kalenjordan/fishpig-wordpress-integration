<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_AddThis_Helper_Data extends Fishpig_Wordpress_Helper_Abstract
{
	/**
	 * Cache array for plugin options
	 *
	 * @var array
	 */
	protected $_pluginOptions = array();
	
	/**
	 * Retrieve and set the plugin options
	 *
	 * @return void
	 */
	public function __construct()
	{
		if ($this->isEnabled()) {
			$this->_pluginOptions = (array)unserialize(
				Mage::helper('wordpress')->getWpOption('addthis_settings')
			);
	
			if ($this->_isHomepage()) {
				$this->_pluginOptions['current_page'] = 'home';
			}
			else if (Mage::registry('wordpress_page')) {
				$this->_pluginOptions['current_page'] = 'pages';
			}
			else if (Mage::registry('wordpress_category')) {
				$this->_pluginOptions['current_page'] = 'cats';
			}
			else if (Mage::registry('wordpress_archives')) {
				$this->_pluginOptions['current_page'] = 'archives';
			}
		}
	}
	
	/**
	 * Determine whether the plugin is enabled
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return Mage::helper('wordpress')->isPluginEnabled('AddThis');
	}
	
	/**
	 * Retrieve the publisher ID
	 *
	 * @return string
	 */
	public function getPublisherId()
	{
		return $this->getOption('profile') 
			? urlencode($this->getOption('profile'))
			: 'wp-' . md5($this->getUrl() . 'addthis');		
	}
	
	/**
	 * Determine whether the plugin can be displayed on the page
	 *
	 * @param string $current = null
	 * @return bool
	 */
	public function isActiveOnPage()
	{
		$current = $this->getOption('current_page');

		return $this->isEnabled() && (
			!$current || (bool)$this->getOption('addthis_showon' . $current)
		);
	}
	
	/**
	 * Retrieve the plugin version
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return $this->getOption('atversion');
	}
	
	/**
	 * Retrieve a plugin option
	 *
	 * @param string $key
	 * @param mixed $default = null
	 * @return mixed
	 */
	public function getOption($key, $default = null)
	{
		return isset($this->_pluginOptions[$key])
			? $this->_pluginOptions[$key]
			: $default;
	}
	
	/**
	 * Determine whether the current request is for the homepage
	 *
	 * @return bool
	 */
	protected function _isHomepage()
	{
		$request = Mage::app()->getRequest();

		return $request->getModuleName() === 'wordpress'
			&& $request->getControllerName() === 'index'
			&& $request->getActionName() === 'index';
	}
}
