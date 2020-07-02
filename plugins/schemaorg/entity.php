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

class FlGSDSchemaOrgEntity extends FlGSDPlugin
{
	public function onBeforeCompileHead()
	{
		if($this->app->isClient('site'))
		{
			$this->setEntity($this->params);
		}
	}
	
	
	public function setEntity($params)
	{
		$input = $this->app->input->getArray();
		
		if($params->ignore_contacts)
		{
			if($input['option'] == 'com_contact')
			{
				if($params->ignore_contacts == 'all')
					return;
				
				if($input['view'] == 'contact')
				{
					if($params->ignore_contacts == 'contacts')
						return;
					
					if($params->ignore_contacts == 'current' && $input['id'] == $params->cid)
						return;
				}
			}
		}
		
		$contact = $this->getContact($params->cid);
		
		if(!$contact)
		{
			return;
		}
		
		$data = [];
		$data['@context'] = 'http://schema.org';
		
		//set types
		$types[] = $params->type;
		
		if($params->type == 'Organization' && isset($params->Organization) && is_array($params->Organization))
		{
			$types = array_merge($types, $params->Organization);
		}
		
		$this->appendProperty($data, '@type', $types);
		
		//set name
		$data['name'] = $contact->name;
		
		//set url
		$data['url'] = Juri::root();
		
		//set image
		if($contact->image)
		{
			if($params->image)
				$data['image'] = Juri::root().$contact->image;
			
			if($params->logo && $params->type == 'Organization')
				$data['logo'] = Juri::root().$contact->image;
		}
		
		//set phones
		if($params->phones)
		{
			$phoneRegExp = "~[^\d\+]~";
			
			$telephone = preg_replace($phoneRegExp, '', $contact->telephone);
			
			if($telephone)
				$data['telephone'] = $telephone;
			
			$mobile = preg_replace($phoneRegExp, '', $contact->mobile);
			
			if($mobile)
				$data['telephone'] = $mobile;
			
			$fax = preg_replace($phoneRegExp, '', $contact->fax);
			
			if($fax)
				$data['faxNumber'] = $fax;
		}
		
		
		//set address
		if($params->address && $contact->address && $contact->suburb)
		{
			$address = [];
			$address['@type'] = 'PostalAddress';
			
			$address['streetAddress'] = $contact->address;
			$address['addressLocality'] = $contact->suburb;
			
			if($contact->state)
				$address['addressRegion'] = $contact->state;
			
			if($contact->country)
				$address['addressCountry'] = $contact->country;
			
			if($contact->postcode)
				$address['postalCode'] = $contact->postcode;
			
			$data['address'] = $address;
		}
		
		
		//set pages
		if($params->pages && $contact->published)
		{
			$needle = 'index.php?option=com_contact&view=contact&id=' . $params->cid;
			$menu = JFactory::getApplication()->getMenu();
			$items = $menu->getItems('link', $needle);
			
			$pages = [];
			
			if(count($items))
			{
				foreach ($items as $item)
				{
					$pages[] = JRoute::_('index.php?Itemid=' . $item->id, true, -1);
				}
			}
			else
			{
				$pages[] = JRoute::_($needle, true, -1);
			}
			
			$this->appendProperty($data, 'mainEntityOfPage', $pages);
		}
		
		
		//set links
		if(is_array($params->links))
		{
			$links = [];
			
			if(in_array('webpage', $params->links))
			{
				if($contact->webpage)
					$links[] = $contact->webpage;
			}
			
			if(in_array('links', $params->links))
			{
				if($contact->params->linka)
					$links[] = $contact->params->linka;
				if($contact->params->linkb)
					$links[] = $contact->params->linkb;
				if($contact->params->linkc)
					$links[] = $contact->params->linkc;
				if($contact->params->linkd)
					$links[] = $contact->params->linkd;
				if($contact->params->linke)
					$links[] = $contact->params->linke;
			}
			
			$this->appendProperty($data, 'sameAs', $links);
		}
		
		$this->addJSONLD($data, 'entity');
	}
	
	protected static function getContact($cid)
	{
		if(!$cid)
		{
			return null;
		}
		
		static $contacts = [];
		
		if(isset($contacts[$cid]))
		{
			return $contacts[$cid];
		}
		
		// Get database
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from('#__contact_details')
			->where('id=' . $cid);
		$db->setQuery($query);
		
		$contact = $db->loadObject();
		
		if($contact)
		{
			$contact->params = json_decode($contact->params);
			$contact->metadata = json_decode($contact->metadata);
		}
		else
		{
			$contact = null;
		}
		
		$contacts[$cid] = $contact;
		
		return $contacts[$cid];
	}
}

