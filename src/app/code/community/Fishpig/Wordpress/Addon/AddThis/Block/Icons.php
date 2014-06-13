<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_AddThis_Block_Icons extends Fishpig_Wordpress_Block_Abstract
{
	/**
	 * Generate and return the ShareThis markup
	 *
	 * @return string
	 */
	protected function _toHtml()
	{
		if (Mage::helper('wp_addon_addthis')->isActiveOnPage()) {
			return $this->getHtml($this->getPost());
		}
		
		return parent::_toHtml();
	}
	
	/**
	 * Retrieve the icon HTML for the post
	 *
	 * @param Fishpig_Wordpress_Model_Post_Abstract $post
	 * @return string
	 */
	public function getHtml(Fishpig_Wordpress_Model_Post_Abstract $post)
	{
		return $this->_getHtml($post, Mage::helper('wp_addon_addthis')->getOption($this->getPosition()));
	}
	
	/**
	 * Retrieve the HTML 
	 *
	 * @param Fishpig_Wordpress_Model_Post_Abstract
	 * @param string $type
	 * @return string
	 */
	protected function _getHtml($post, $type)
	{
		if ($type === 'custom_string') {
			$html  = str_replace('<div class="addthis_toolbox', '<div %s class="addthis_toolbox', Mage::helper('wp_addon_addthis')->getOption($this->getPosition() . '_custom_string'));
		}
		else if (($html = $this->_getButtonHtmlTemplate($type)) === '') {
			return '';
		}

		$source = sprintf(
			$html['src'],
			'addthis:url="' . $post->getPermalink() . '" addthis:title="' . addslashes($this->escapeHtml($post->getPostTitle())) . '"'
		);
		
		
		$items = Mage::helper('wp_addon_addthis')->getOption($this->getPosition() . '_chosen_list');
		
		if (!$items) {
			$items = $html['default'];
		}
		
		if (strpos($items, ',') !== false) {
			$items = explode(',', $items);
			
			$buttonHtml = '';
			
			foreach($items as $item) {	
				$buttonHtml .= "\n" . $this->_getItemHtml(trim($item), $type);
			}
			
			$source = str_replace('--BUTTONS--', $buttonHtml, $source);
		}

		return $source;
	}
	
	/**
	 * Retrieve the button template
	 *
	 * @param string $type
	 * @return string
	 */
	protected function _getButtonHtmlTemplate($type)
	{
		$buttons = array(
			'fb_tw_p1_sc' => array(
				'src' => '<div class="addthis_toolbox addthis_default_style " %s  >--BUTTONS--</div>',
				'default' => 'facebook_like, tweet, pinterest_pinit, counter',
			),
			'large_toolbox' => array(
				'src' => '<div class="addthis_toolbox addthis_default_style addthis_32x32_style" %s >--BUTTONS--</div>',
				'default' => 'facebook, twitter, email, pinterest_share, compact, bubble',
			),
			'small_toolbox' => array(
				'src' => '<div class="addthis_toolbox addthis_default_style addthis_" %s >--BUTTONS--</div>',
				'default' => 'facebook, twitter, email, pinterest_share, compact, bubble',
			),
			'plus_one_share_counter' => array(
				'src' => '<div class="addthis_toolbox addthis_default_style" %s >--BUTTONS</div>',
				'default' => 'google_plusone, counter',
			),
			'small_toolbox_with_share' =>  array(
				'src' => '<div class="addthis_toolbox addthis_default_style " %s >--BUTTONS--</div>',
				'default' => 'compact, separator, preferred_1, preferred_2, preferred_3, preferred_4',
			),
			'fb_tw_sc' => array(
				'src' => '<div class="addthis_toolbox addthis_default_style " %s  >--BUTTONS--</div>',
				'default' => 'facebook_like, tweet, counter'
			),
			'simple_button' => array(
				'src' => '<div class="addthis_toolbox addthis_default_style " %s>--BUTTONS</div>',
				'default' => 'compact',
			),		
			'button' => array(
				'src' => '<div><a class="addthis_button" href="//addthis.com/bookmark.php?v='.$this->getVersion().'" %s><img src="//cache.addthis.com/cachefly/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/></a></div>',
				'default' => false,
			),
			'share_counter' => array(
				'src' => '<div class="addthis_toolbox addthis_default_style " %s  ><a class="addthis_counter"></a></div>',
				'default' => false,
			),
		);
		
		return isset($buttons[$type]) 
			? $buttons[$type]
			: '';
	}
	
	public function getVersion()
	{
		return Mage::helper('wp_addon_addthis')->getVersion();
	}

	protected function _getItemHtml($item, $type = null)
	{
		$defaults = array(
			'google_plusone' => '<a class="addthis_button_google_plusone" g:plusone:size="medium" ></a>',

			'bubble' => '<a class="addthis_counter addthis_bubble_style"></a>',
			'separator' => '<span class="addthis_separator">|</span>',
			
			'large_toolbox_counter' => '<a class="addthis_counter addthis_bubble_style"></a>', 

			'small_toolbox' => '<a class="addthis_button_compact"></a>',
			'small_toolbox_counter' => '<a class="addthis_counter addthis_bubble_style"></a>', 

			'fb_tw_p1_sc_counter' => '<a class="addthis_counter addthis_pill_style"></a>',
			'fb_tw_p1_sc_compact' => ' ',

		);
		
		if (isset($defaults[$item])) {
			return $defaults[$item];
		}
		
		if (!is_null($type)) {
			if (isset($defaults[$type . '_' . $item])) {
				return $defaults[$type . '_' . $item];
			}
		}

		return sprintf('<a class="addthis_button_%s __def"></a>', $item);
	}
}
