<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_LightboxGallery_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Options cache
	 *
	 * @var array
	 */
	protected $_options = null;
	
	/**
	 * Prefill the options
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->_options = (array)@unserialize(
			Mage::helper('wordpress')->getWpOption('lightbox_gallery_data')
		);
	}
	
	/**
	 * Retrieve an option value
	 *
	 * @param string $key
	 * @param mixed $default = null
	 * @return mixed
	 */
	public function getOption($key, $default = null)
	{
		$keys = explode('/', trim($key, '/'));
		$value = $this->_options;
		
		while(($xkey = array_shift($keys)) !== null) {
			if (!isset($value[$xkey])) {
				return $default;
			}	
			
			$value = $value[$xkey];
		}
		
		return $value;
	}
	
	/**
	 * Determine wether the Lightbox Gallery plugin is enabled in WordPress
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return Mage::helper('wordpress')->isPluginEnabled('lightbox-gallery');
	}
	
	/**
	 * Add the Lightbox Gallery JS/CSS
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function addLightboxGalleryIncludesObserver(Varien_Event_Observer $observer)
	{
		$response = $observer
			->getEvent()
				->getFront()
					->getResponse();
		
		if ($this->_canInclude($response->getBody())) {
			$includes = $this->_getIncludes(strpos($response->getBody(), 'jquery') === false);

			$response->setBody(
				str_replace('</head>', $includes . '</head>', $response)
			);
		}
		
		return $this;
	}
	
	/**
	 * Determine whether to include the JS/CSS
	 *
	 * @param string $html
	 * @return bool
	 */
	protected function _canInclude($html)
	{
		return $this->isEnabled() 
			&&  (strpos($html, 'rel="gallery') !== false
				|| strpos($html, 'wp-content/uploads') !== false);
	}
	
	/**
	 * Retrieve the include HTML
	 *
	 * @return string
	 */
	protected function _getIncludes($includeJquery = true)
	{
		if ($includeJquery) {
			$scripts = array(
				'wp-includes/js/jquery/jquery.js?ver=1.10.2',
				'wp-includes/js/jquery/jquery-migrate.min.js?ver=1.2.1',
			);
		}
		else {
			$scripts = array();
		}
		
		$styles = array(
			'wp-content/plugins/lightbox-gallery/lightbox-gallery.css',
		);
		
		$html = array();

		if ($this->getType() === 'lightbox') {
			$html[] = '<script type="text/javascript">var lightbox_path = "' . Mage::helper('wordpress')->getBaseUrl('wp-content/plugins/lightbox-gallery/') . '";</script>';
			$html[] = '<script type="text/javascript">var graphicsDir = "' . Mage::helper('wordpress')->getBaseUrl('wp-content/plugins/lightbox-gallery/graphics/') . '";</script>';

			$scripts = array_merge($scripts, array(
				'wp-content/plugins/lightbox-gallery/js/jquery.lightbox.js?ver=3.8',
				'wp-content/plugins/lightbox-gallery/js/jquery.dimensions.js?ver=3.8',
				'wp-content/plugins/lightbox-gallery/js/jquery.bgiframe.js?ver=3.8',
				'wp-content/plugins/lightbox-gallery/js/jquery.tooltip.js?ver=3.8',
				'wp-content/plugins/lightbox-gallery/lightbox-gallery.js?ver=3.8',
			));	
		}
		else {
			$scripts = array_merge($scripts, array(
				'wp-content/plugins/lightbox-gallery/js/jquery.colorbox.js?ver=3.8',
				'wp-content/plugins/lightbox-gallery/js/jquery.tooltip.js?ver=3.8',
				'wp-content/plugins/lightbox-gallery/lightbox-gallery.js?ver=3.8',
			));
		}
		
		$base = Mage::helper('wordpress')->getBaseUrl();

		foreach($scripts as $src) {
			$html[] = sprintf('<script type="text/javascript" src="%s"></script>', $base . $src);
		}
		
		foreach($styles as $href) {
			$html[] = sprintf('<link rel="stylesheet" type="text/css" href="%s" />', $base . $href);
		}

		return implode("\n", $html);
	}
	
	/**
	 * Get the script to be called after the gallery HTML
	 *
	 * @param int $galleryId
	 * @return string
	 */
	public function getAfterGalleryScript($galleryId)
	{
		if (!$this->isEnabled()) {
			return '';
		}

		if ($this->getType() === 'lightbox') {
			return sprintf('jQuery(document).ready(function () {jQuery(".gallery%d a").lightBox({captionPosition:"gallery"});});', $galleryId);
		}

		return sprintf('jQuery(document).ready(function () {jQuery(".gallery1 a").attr("rel","gallery%s");	jQuery(\'a[rel="gallery%s"]\').colorbox({title: function(){ return jQuery(this).children().attr("alt"); }});});', $galleryId, $galleryId);
	}
	
	/**
	 * Get the JS library type
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->getOption('global_settings/lightbox_gallery_loading_type');
	}
}
