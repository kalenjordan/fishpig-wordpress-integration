<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_AddThis_Block_Js extends Fishpig_Wordpress_Block_Abstract
{
	/**
	 * Generate and return the ShareThis JS
	 *
	 * @return string
	 */
	protected function _toHtml()
	{
		$helper = Mage::helper('wp_addon_addthis');

		if ($helper->isActiveOnPage()) {
			$config = array(
				'data_track_clickback' => (bool)$helper->getOption('addthis_append_data'),
				'data_track_addressbar' => (bool)$helper->getOption('data_track_addressbar'),
				'data_track_textcopy' => (bool)$helper->getOption('addthis_copytracking2'),
				'ui_atversion' => $helper->getVersion(),
				'ui_header_background' => $helper->getOption('addthis_header_background'),
				'ui_header_color' => $helper->getOption('addthis_header_color'),
				'ui_cobrand' => $helper->getOption('addthis_brand'),
				'ui_508_compliant' => (bool)$helper->getOption('addthis_508'),
			);
			
			if ($helper->getOption('data_ga_property')) {
				$config['data_ga_property'] = $helper->getOption('data_ga_property');
				$config['data_ga_social'] = true;
			}

			if ($helper->getOption('addthis_language') == '2') {
				$config['ui_language'] = $helper->getOption('addthis_language');
			}
			
			$share = array();
			
			if ($helper->getOption('addthis_twitter_template')) {
				$share['passthrough']['twitter']['via'] = $this->_getFirstTwitterUsername($helper->getOption('addthis_twitter_template'));
			}
			
			if ($helper->getOption('addthis_bitly_login') && $helper->getOption('addthis_bitly_key')) {
				$share['url_transforms']['shorten']['twitter'] = 'bitly';
				$share['shorteners']['bitly']['login'] = $helper->getOption('addthis_bitly_login');
				$share['shorteners']['bitly']['apiKey'] = $helper->getOption('addthis_bitly_key');
			}

			return implode('', array(
				sprintf('<link rel="stylesheet" href="%s" media="all" />', Mage::helper('wordpress')->getBaseUrl('wp-content/plugins/addthis/css/output.css?ver=3.9')),
				'<script type="text/javascript">var addthis_product = \'wpp-3.5.1\';',
				$this->_mergeArrayJson($config, $helper->getOption('addthis_config_json'), "var addthis_config = '%s';"),
				sprintf("var addthis_options = {%s};", trim($helper->getOption('addthis_options'), '{}')),
				$this->_mergeArrayJson($share, $helper->getOption('addthis_share_json'), "if (typeof(addthis_share) == \"undefined\"){ addthis_share = %s;}"),
				'</script>',
				sprintf('<script type="text/javascript" src="//s7.addthis.com/js/%s/addthis_widget.js#pubid=%s"></script>', $helper->getVersion(), $helper->getPublisherId()),
			));
		}

		return parent::_toHtml();
	}

	/**
	 * Merge an array with a JSON string
	 *
	 * @param array $config
	 * @param string $json
	 * @param string $returnStrng
	 * @return string
	 */
	protected function _mergeArrayJson($config, $json = null, $returnString = '')
	{
		if (!is_null($json)) {
			$config = array_merge($config, json_decode($json, true));
		}
		
		foreach($config as $key => $value) {
			if (is_null($value)) {
				unset($config[$key]);
			}
		}
		
		if (count($config) > 0) {
			return sprintf($returnString, json_encode($config));
		}
		
		return '';
	}

	/**
	 * Retrieve the first Twitter username found
	 *
	 * @param string $raw
	 * @return string
	 */
	protected function _getFirstTwitterUsername($raw)
	{
		if (preg_match_all('/@(\w+)\b/i', $raw, $matches)) {
			return $matches[1][0];
		}
		
		if (preg_match_all('/(\w+)\b/i', $raw, $matches)) {
			return $matches[1][0];
		}
		
		return '';
	}
}
