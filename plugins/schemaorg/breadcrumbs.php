<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.flgsd (Google Structured Data)
 *
 * @author      Vitaliy Moskalyuk  <info@2sweb.ru>
 * @copyright   Copyright © 2018 Vitaliy Moskalyuk. All rights reserved.
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die();

class FlGSDSchemaOrgBreadcrumbs extends FlGSDPlugin
{
	public function onBeforeCompileHead()
	{
		if($this->app->isClient('site'))
		{
			$this->setBreadcrumbs($this->params);
		}
	}
	
	
	public function setBreadcrumbs($params)
	{
		// if Home
		if($this->isHomePage())
		{
			return;
		}
		
		// Get the PathWay object from the application
		$pathway = JFactory::getApplication()->getPathway();
		$items   = $pathway->getPathWay();
		$count   = count($items);
		
		// if empty
		if(!$count)
		{
			return;
		}
		
		$data = [];
		$data['@context'] = 'http://schema.org';
		$data['@type'] = 'BreadcrumbList';
		
		$crumbs = [];
		$i = 0;
		
		// Add Home item
		if($params->append_home)
		{
			switch($params->home_title)
			{
				case 'sitename':
					$home_title = $this->app->get('sitename');
					break;
				case 'custom':
					if($params->home_custom_title)
					{
						$home_title = JText::_($params->home_custom_title);
						break;
					}
				case 'domain':
				default:
					$home_title = $_SERVER['SERVER_NAME'];
					break;
			}
			
			$crumb = [];
			$crumb['@type'] = 'ListItem';
			$crumb['position'] = 1;
			$crumb['item'] = [];
			$crumb['item']['@id'] = JRoute::_('index.php?Itemid=' . $this->getHomePage()->id, true, -1);
			$crumb['item']['name'] = htmlspecialchars($home_title, ENT_COMPAT, 'UTF-8');
			$crumbs[$i] = $crumb;
			$i++;
		}
		
		for ($j = 0; $j < $count; $j++)
		{
			$crumb = [];
			$crumb['@type'] = 'ListItem';
			$crumb['position'] = $j + $i + 1;
			$crumb['item'] = [];
			$crumb['item']['@id'] = JRoute::_($items[$j]->link, true, -1);
			$crumb['item']['name'] = stripslashes(htmlspecialchars($items[$j]->name, ENT_COMPAT, 'UTF-8'));
			
			$crumbs[$i+$j] = $crumb;
		}
		
		// Fix last item's missing URL
		end($crumbs);
		if(empty($crumb['item']['@id']))
		{
			$crumbs[key($crumbs)]['item']['@id'] = Juri::current();
		}
		
		$data['itemListElement'] = $crumbs;
		
		$this->addJSONLD($data, 'breadcrumbs');
	}
}

