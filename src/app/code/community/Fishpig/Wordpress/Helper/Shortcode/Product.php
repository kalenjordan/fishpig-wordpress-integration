<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Product extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'product';
	}
	
	/**
	 * Apply the Vimeo short code
	 *
	 * @param string &$content
	 * @param Fishpig_Wordpress_Model_Post_Abstract $object
	 * @return void
	 */	
	protected function _apply(&$content, Fishpig_Wordpress_Model_Post_Abstract $object)
	{
		if (($shortcodes = $this->_getShortcodes($content)) !== false) {
			foreach($shortcodes as $shortcode) {
				$params = $shortcode->getParams();
				$collection = Mage::getResourceModel('catalog/product_collection');
										
				try {
					if ($params->getId()) {
						$params->setIds(array($params->getId()));
					}
					else if ($params->getSku()) {
						$params->setIds(array(Mage::getResourceModel('catalog/product')->getIdBySku($params->getSku())));
					}
					else if ($params->getIds()) {
						$params->setIds(explode(',', $params->getIds()));
					}
				
					if (!$params->getIds()) {
						throw new Exception('The id, sku or ids parameter is not set for the product shortcode');
					}
					
					if (!Mage::getStoreConfigFlag('cataloginventory/options/show_out_of_stock')) {
						Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
					}

					$collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
						->addAttributeToFilter('status', 1)
						->addAttributeToFilter('visibility', array('in' => array(2, 4)))
						->addAttributeToFilter('entity_id', array('in' => $params->getIds()))
						->load();
					
					if ($collection->count() === 0) {
						throw new Exception('No valid products used in product shortcode');
					}
					
					$template = $params->getTemplate() ? $params->getTemplate() : 'wordpress/shortcode/product.phtml';
						
					$html = $this->_createBlock('catalog/product')
						->setTemplate($template)
						->setItems($collection)
						->setProducts($collection)
						->setProduct($collection->getFirstItem())
						->setProductId($collection->getFirstItem()->getId())
						->toHtml();

					$content = str_replace($shortcode->getHtml(), $html, $content);
				}
				catch (Exception $e) {
					$content = str_replace($shortcode->getHtml(), '', $content);
				}
			}
		}
	}
}
