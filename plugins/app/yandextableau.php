<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.flgsd (Google Structured Data)
 *
 * @author      Vitaliy Moskalyuk  <info@2sweb.ru>
 * @copyright   Copyright © 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 *
 * @info https://tech.yandex.ru/browser/tableau/doc/dg/concepts/create-widget-docpage/
 */

defined('_JEXEC') or die();

class FlGSDAppYandexTableau extends FlGSDPlugin
{
	public function onBeforeCompileHead()
	{
		if($this->app->isClient('site'))
		{
			switch($this->params->mode)
			{
				case 'tag' :
					$content = [];
					$content[] = 'logo=' . Juri::root() . $this->params->logo;
					$content[] = 'color=' . $this->params->color;
					
					if($this->params->feed)
					{
						$content[] = 'feed=' . $this->params->feed;
					}
					
					$this->addMetaTag('yandex-tableau-widget', implode(', ', $content));
					
					break;
				case 'json' :
					
					break;
			}
		}
	}
}
