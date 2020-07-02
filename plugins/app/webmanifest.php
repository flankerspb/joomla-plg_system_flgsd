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
 *       https://www.w3.org/TR/appmanifest/
					https://realfavicongenerator.net/
 */

defined('_JEXEC') or die();

class FlGSDAppWebManifest extends FlGSDPlugin
{
	const FILE_NAME = '/site.webmanifest';
	
	const ICONS_NAME = 'icon';
	
	const ICONS = 
	[
		'all' =>         [36, 48, 72, 96, 144, 192, 256, 384, 512],
		'recommended' => [192, 512],
	];
	
	public function onBeforeCompileHead()
	{
		if($this->app->isClient('site') && is_file(JPATH_ROOT . '/' . self::FILE_NAME))
		{
			$this->addHeadLink(self::FILE_NAME, 'manifest');
			
			if($this->params->declare_icon)
			{
				$this->addHeadLink(self::ICONS_NAME.'-192x192.png', 'icon', ['type' => 'image/png', 'sizes' => '192x192']);
			}
		}
		
		// var_dump($this->params);
		
		$this->generateFile($this->params);
	}
	
	
	public function onExtensionAfterSave($context, &$table, $isNew)
	{
		if($table->type == 'plugin' && $table->element == 'flgsd')
		{
			$params = json_decode($table->params)->favicon->webmanifest;
			
			$this->updateFile($params, $this->params);
		}
	}
	
	
	protected function generateFile($params)
	{
		$manifest = [];
		
		$manifest['name'] = $params->name;
		
		
		if($params->short_name)
		{
			$manifest['short_name'] = $params->short_name;
		}
		
		
		if($params->description)
		{
			$manifest['description'] = $params->description;
		}
		
		
		if($params->lang)
		{
			$manifest['lang'] = $params->lang;
		}
		
		
		if($params->theme_color)
		{
			$manifest['theme_color'] = $params->theme_color;
			
			$background_color = $params->background_color ? $params->background_color : $params->theme_color;
			
			if($background_color)
			{
				$manifest['background_color'] = $background_color;
			}
		}
		
		
		foreach(self::ICONS[$params->icons] as $size)
		{
			$icon = [];
			
			$name = self::ICONS_NAME.'-'.$size.'x'.$size.'.png';
			
			$icon['src'] = $name;
			$icon['sizes'] = "{$size}x{$size}";
			$icon['type'] = 'image/png';
			
			//$icon['purpose']
			
			$manifest['icons'][] = $icon;
		}
		
		// if($params->icon_svg)
		// {
			// $icon = [];
			
			// $icon['src'] = $params->icon_svg;
			// //$icon['sizes'] = "any";
			// $icon['type'] = 'image/svg+xml';
			
			// $manifest['icons'][] = $icon;
		// }
		
		
		if($params->display)
		{
			$manifest['display'] = $params->display;
			
			if($params->start_url)
			{
				$manifest['start_url'] = $params->start_url;
				
				if($params->scope)
				{
					$manifest['scope'] = $params->scope;
				}
			}
			
			if($params->orientation)
			{
				$manifest['orientation'] = $params->orientation;
			}
		}
		
		
		if(isset($params->categories) && $params->categories)
		{
			$manifest['categories'] = $params->categories;
		}
		
		
		foreach($params->screenshots as $value)
		{
			if(is_file(JPATH_ROOT . '/' . $value->src))
			{
				$screenshot['src'] = Juri::root() . $value->src;
				
				$type = $this->getMimeType(JPATH_ROOT . '/' . $value->src);
				
				if($type)
				{
					$screenshot['type'] = $type;
				}
				
				$size = getimagesize(JPATH_ROOT . '/' . $value->src);
				
				if($size)
				{
					$screenshot['sizes'] = $size[0] . 'x' . $size[1];
				}
				
				$manifest['screenshots'][] = $screenshot;
			}
		}
		
		
		foreach($params->related_applications as $value)
		{
			$manifest['related_applications'][] = $value;
		}
		
		
		if($params->prefer_related_applications && count($params->categories))
		{
			$manifest['prefer_related_applications'] = true;
		}
		
		
		if($params->iarc_rating_id)
		{
			$manifest['iarc_rating_id'] = $params->iarc_rating_id;
		}
		
		
		$string = json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		
		file_put_contents(JPATH_ROOT . '/' . self::FILE_NAME, $string);
	}
}
