<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.flgsd (Google Structured Data)
 *
 * @author      Vitaliy Moskalyuk  <info@2sweb.ru>
 * @copyright   Copyright © 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 *
 * @info https://developer.mozilla.org/en/docs/Web/Manifest
 */

defined('_JEXEC') or die();

class FlGSDAppMSApplication extends FlGSDPlugin
{
	const FILE_NAME = 'browserconfig.xml';
	
	public function onBeforeCompileHead()
	{
		if($this->app->isClient('site') && is_file(JPATH_ROOT . '/' . self::FILE_NAME))
		{
			$this->addMetaTag('msapplication-config', self::FILE_NAME);
		}
	}
	
	
	public function onExtensionAfterSave($context, &$table, $isNew)
	{
		if($table->type == 'plugin' && $table->element == 'flgsd')
		{
			$params = json_decode($table->params)->favicon->msapplication;
			
			$this->updateFile($params, $this->params);
		}
	}
	
	
	public function generateFile($params)
	{
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><browserconfig><msapplication></msapplication></browserconfig>');
		
		$tile = $xml->msapplication->addChild('tile');
		$tile->addChild('square70x70logo')->addAttribute('src', $params->tile->small);
		$tile->addChild('square150x150logo')->addAttribute('src', $params->tile->medium);
		$tile->addChild('square310x310logo')->addAttribute('src', $params->tile->large);
		$tile->addChild('wide310x150logo')->addAttribute('src', $params->tile->wide);
		$tile->addChild('TileColor', $params->tile->color);
		
		$xml->asXML(JPATH_ROOT . '/' . self::FILE_NAME);
	}
}
