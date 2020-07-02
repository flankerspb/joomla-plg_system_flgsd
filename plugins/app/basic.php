<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.flgsd (Google Structured Data)
 *
 * @author      Vitaliy Moskalyuk  <info@2sweb.ru>
 * @copyright   Copyright © 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 *
 */

defined('_JEXEC') or die();

class FlGSDAppBasic extends FlGSDPlugin
{
	public function onBeforeCompileHead()
	{
		if($this->app->isClient('site'))
		{
			if($this->params->app_name)
			{
				$this->addMetaTag('application-name', $this->params->app_name);
			}
			
			foreach($this->params->icons as $value)
			{
				if(is_file(JPATH_ROOT . '/' . $value->src))
				{
					$attribs = [];
					
					$type = $this->getMimeType(JPATH_ROOT . '/' . $value->src);
					
					var_dump($type);
					
					if($type)
					{
						$attribs['type'] = $type;
						
						switch($type)
						{
							case 'image/x-icon':
							case 'image/vnd.microsoft.icon':
								break;
							case 'image/svg+xml':
								$attribs['sizes'] = 'any';
								break;
							case 'image/png':
								$size = getimagesize(JPATH_ROOT . '/' . $value->src);
								
								if($size)
								{
									$attribs['sizes'] = $size[0] . 'x' . $size[1];
								}
								
								break;
						}
						
						$this->addHeadLink($value->src, 'icon', $attribs);
					}
				}
			}
		}
	}
}
