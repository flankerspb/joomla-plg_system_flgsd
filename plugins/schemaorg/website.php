<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.flgsd (Google Structured Data)
 *
 * @author      Vitaliy Moskalyuk  <info@2sweb.ru>
 * @copyright   Copyright © 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 *
 *
 * @info https://schema.org/WebSite
 */

defined('_JEXEC') or die();

// copyrightHolder
// copyrightYear
// dateCreated
// dateModified
// datePublished
// isFamilyFriendly

class FlGSDSchemaOrgWebsite extends FlGSDPlugin
{
	public function onBeforeCompileHead()
	{
		if($this->app->isClient('site'))
		{
			$this->setWebsite($this->params);
		}
	}
	
	
	public function setWebsite($params)
	{
		// if not Home
		if(!$this->isHomePage())
		{
			return;
		}
		
		$data = [];
		$data['@context'] = 'http://schema.org';
		$data['@type'] = 'WebSite';
		$data['name'] = htmlspecialchars($this->app->get('sitename'));
		$data['url'] = Juri::root();
		
		if(is_object($params->alter_names))
		{
			$names = [];
			
			foreach ($params->alter_names as $name)
			{
				if ($name->value)
					$names[] = $name->value;
			}
			
			self::appendProperty($data, 'alternateName', $names);
		}
		
		if(is_object($params->same_as))
		{
			$links = [];
			
			foreach ($params->same_as as $link)
			{
				if ($link->value)
					$links[] = $link->value;
			}
			
			self::appendProperty($data, 'sameAs', $links);
		}
		
		if(($params->langs))
		{
			$languages = JLanguageHelper::getContentLanguages();
			
			if(count($languages))
			{
				$langs = [];
				
				foreach($languages as $lang)
				{
					$langs[] = $lang->sef;
				}
				
				self::appendProperty($data, 'inLanguage', $langs);
			}
		}
		
		if(is_object($params->credits))
		{
			$credits = [];
			
			foreach ($params->credits as $value)
			{
				$item = [];
				$item['@type'] = $value->entity;
				$item['name'] = htmlspecialchars($value->name);
				$item['url'] = $value->url;
				
				foreach ($value->type as $type)
				{
					$credits[$type][] = $item;
				}
			}
			
			foreach ($credits as $key => $value)
			{
				self::appendProperty($data, $key, $value);
			}
		}
		
		$this->addJSONLD($data, 'website');
	}
}

