<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.flgsd (Google Structured Data)
 *
 * @author      Vitaliy Moskalyuk  <info@2sweb.ru>
 * @copyright   Copyright © 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 *
 * @info https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html#//apple_ref/doc/uid/TP40002051-CH3-SW3
 */

defined('_JEXEC') or die();

class FlGSDAppApple extends FlGSDPlugin
{
	public function onBeforeCompileHead()
	{
		if($this->app->isClient('site'))
		{
			// https://webhint.io/docs/user-guide/hints/hint-apple-touch-icons/
			
			if($this->params->icon)
			{
				$type = $this->getMimeType(JPATH_ROOT . '/' . $this->params->icon);
				
				if($type)
				{
					$attribs['type'] = $type;
				}
				
				$size = getimagesize(JPATH_ROOT . '/' . $this->params->icon);
				
				if($size)
				{
					$attribs['sizes'] = $size[0] . 'x' . $size[1];
				}
				
				$this->addHeadLink($this->params->icon, 'apple-touch-icon', $attribs);
			}
			
			
			// https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/pinnedTabs/pinnedTabs.html
			
			if($this->params->mask_icon && $this->params->color)
			{
				$this->addHeadLink($this->params->mask_icon, 'mask-icon', ['color' => $this->params->color]);
			}
			
			
			if($this->params->title)
			{
				$this->addMetaTag('apple-mobile-web-app-title', $this->params->title);
			}
			
			
			if($this->params->startup_image)
			{
				$this->addHeadLink($this->params->startup_image, 'apple-touch-startup-image');
			}
		}
	}
	
	
	public function onExtensionAfterSave($context, &$table, $isNew)
	{
		if($table->type == 'plugin' && $table->element == 'flgsd')
		{
			$params = json_decode($table->params)->favicon->apple;
			
			if(is_file(JPATH_ROOT . '/' . $params->icon_src))
			{
				copy(JPATH_ROOT . '/' . $params->icon_src, JPATH_ROOT . '/apple-touch-icon.png');
			}
			
			if(is_file(JPATH_ROOT . '/' . $params->mask_icon_src))
			{
				copy(JPATH_ROOT . '/' . $params->icon_src, JPATH_ROOT . '/safari-pinned-tab.svg');
			}
		}
	}
}
