<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Sidebar extends Fishpig_Wordpress_Block_Abstract
{
	/**
	 * Allow dynamic sidebar/column placement
	 *
	 * @var array
	 */
	static $_lockedWidgetAreas = array();
	
	/**
	 * Stores all templates for each widget block
	 *
	 * @var array
	 */
	protected $_widgets = array();

	/**
	 * Add a widget type
	 *
	 * @param string $name
	 * @param string $block
	 * @param string $template
	 * @return Fishpig_Wordpress_Block_Sidebar
	 */
	public function addWidgetType($name, $block, $template = null)
	{
		if (!isset($this->_widgets[$name])) {
			$this->_widgets[$name] = array(
				'block' => strpos($block, '/') !== false ? $block : 'wordpress/' . $block,
				'template' => $template
			);
		}
	
		return $this;
	}
	
	/**
	 * Retrieve information about a widget type
	 *
	 * @param string $name
	 * @return false|array
	 */
	public function getWidgetType($name)
	{
		return isset($this->_widgets[$name]) ? $this->_widgets[$name] : false;
	}
	
	/**
	 * Load all enabled widgets
	 *
	 * @return Fishpig_Wordpress_Block_Sidebar
	 */
	protected function _beforeToHtml()
	{
		if (isset(self::$_lockedWidgetAreas[$this->getWidgetArea()])) {
			return $this;
		}
		
		self::$_lockedWidgetAreas[$this->getWidgetArea()] = true;

		if ($widgets = $this->getWidgetsArray()) {
			$this->_initAvailableWidgets();
			
			foreach($widgets as $widgetType) {
				$name = $this->_getWidgetName($widgetType);
				$widgetIndex = $this->_getWidgetIndex($widgetType);

				if ($widget = $this->getWidgetType($name)) {
					if ($block = $this->getLayout()->createBlock($widget['block'])) {
						if (isset($widget['template']) && !empty($widget['template'])) {
							$block->setTemplate($widget['template']);
						}

						$block->setWidgetType($name);
						$block->setWidgetIndex($widgetIndex);
						
						$this->setChild('wordpress_widget_' . $widgetType, $block);
					}
				}
			}
		}
		
		if (!$this->getTemplate()) {
			$this->setTemplate('wordpress/sidebar.phtml');
		}

		return parent::_beforeToHtml();
	}
	
	/**
	 * Retrieve the widget name
	 * Strip the trailing number and hyphen
	 *
	 * @param string $widget
	 * @return string
	 */
	protected function _getWidgetName($widget)
	{
		return rtrim(preg_replace("/[^a-z_-]/i", '', $widget), '-');
	}
	
	/**
	 * Retrieve the widget name
	 * Strip the trailing number and hyphen
	 *
	 * @param string $widget
	 * @return string
	 */
	protected function _getWidgetIndex($widget)
	{
		if (preg_match("/([0-9]{1,})/",$widget, $results)) {
			return $results[1];
		}
		
		return false;
	}
	
	/**
	 * Retrieve the sidebar widgets as an array
	 *
	 * @return false|array
	 */
	public function getWidgetsArray()
	{
		if ($this->getWidgetArea()) {
			$widgets = $this->helper('wordpress')->getWpOption('sidebars_widgets');

			if ($widgets) {
				$widgets = unserialize($widgets);
				
				if (isset($widgets[$this->getWidgetArea()])) {
					return $widgets[$this->getWidgetArea()];
				}
			}
		}

		return false;
	}
	
	/**
	 * Initialize the widgets from the config.xml
	 *
	 * @return $this
	 */
	protected function _initAvailableWidgets()
	{
		$availableWidgets = (array)Mage::app()->getConfig()->getNode()->wordpress->sidebar->widgets;
		
		foreach($availableWidgets as $name => $widget) {
			$widget = (array)$widget;	
	
			$this->addWidgetType($name, $widget['block'], isset($widget['template']) ? $widget['template'] : null);
		}
		
		return $this;
	}
	
	/**
	 * Determine whether or not to display the sidebar
	 *
	 * @return int
	 */
	public function canDisplay()
	{
		return 1;
	}
	
	/**
	 * Set the widget area.
	 * This allows for support for Simple Page Sidebars
	 *
	 * @param string $widgetArea
	 * @return $this
	 */
	public function setWidgetArea($widgetArea)
	{
		if ($this->hasWidgetArea()) {
			return $this;
		}
		
		$this->setData('widget_area', $widgetArea);

		$widgetArea = null;

		if ($post = Mage::registry('wordpress_post')) {
			$widgetArea = $post->getMetaValue('_sidebar_name');
		}
		else if ($page = Mage::registry('wordpress_page')) {
			$widgetArea = $page->getMetaValue('_sidebar_name');
		}
		
		if (!$widgetArea) {
			return $this;
		}

		$widgetArea = 'page-sidebar-' . preg_replace('/([^a-z0-9_-]{1,})/', '', strtolower(trim($widgetArea)));
		
		return $this->setData('widget_area', $widgetArea);
	}
}
