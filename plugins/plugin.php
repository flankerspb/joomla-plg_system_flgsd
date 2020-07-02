<?php

defined('_JEXEC') or die();

class FlGSDPlugin extends JEvent
{
	public $params;
	
	public $forms_path;
	
	public $app;
	public $document;
	public $lang;
	public $menu;
	
	public function __construct(&$subject, $params)
	{
		parent::__construct($subject);
		
		$this->params = $params;
		
		static $init = false;
		
		$this->forms_path = dirname(__DIR__) . '/forms/';
		
		$this->app = JFactory::getApplication();
		$this->document = JFactory::getDocument();
		$this->lang = JFactory::getLanguage();
	}
	
	
	public static function isHomePage()
	{
		static $is_home = null;
		
		if($is_home === null)
		{
			$id = self::getHomePage() ? self::getHomePage()->id : '';
			
			$home_url = trim(parse_url(JRoute::_('index.php?Itemid=' . $id, true, -1), PHP_URL_PATH), '/');
			$current_url = trim(parse_url(Juri::current(), PHP_URL_PATH), '/');
			$base_url = trim(parse_url(Juri::base(), PHP_URL_PATH), '/');

			$is_home = $current_url == $home_url || $current_url == $base_url;
		}
		
		return $is_home;
	}
	
	
	public static function getHomePage()
	{
		static $home;
		
		if(!$home)
		{
			if(JLanguageMultilang::isEnabled())
			{
				$home = JFactory::getApplication()->getMenu()->getDefault(JFactory::getLanguage()->getTag());
			}
			else
			{
				$home = JFactory::getApplication()->getMenu()->getDefault();
			}
		}
		
		return $home;
	}
	
	
	protected function appendProperty(&$data, $name, &$prop)
	{
		switch(count($prop))
		{
			case 0:
				break;
			case 1:
				$data[$name] = $prop[0];
				break;
			default:
				$data[$name] = $prop;
				break;
		}
	}
	
	protected function getMimeType($file)
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$type = finfo_file($finfo, $file);
		finfo_close($finfo);
		
		return $type;
	}
	
	protected function addJSONLD($data, $entity = '')
	{
		$string = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		
		if($entity)
		{
			$entity = ' data-entity="'.$entity.'"';
		}
		
		$this->document->addCustomTag('<script type="application/ld+json"'.$entity.'>'.$string.'</script>');
		// JFactory::getDocument()->addScriptDeclaration($data, 'application/ld+json');
	}
	
	protected function addMetaTag($name, $content, $attrib = 'name')
	{
		$this->document->setMetadata(htmlspecialchars($name), htmlspecialchars($content), htmlspecialchars($attrib));
	}
	
	protected function addHeadLink($href, $relation, $attribs = [])
	{
		$this->document->addHeadLink($href, $relation, 'rel', $attribs);
	}
	
	protected function updateFile($new_params, $old_params)
	{
		// Status not changed
		if($new_params->enable == $old_params->enable)
		{
			if($new_params->enable)
			{
				// Params was changed
				if($old_params != $new_params)
				{
					if($old_params->file != $new_params->file)
					{
						if(is_file(JPATH_ROOT . '/' . $old_params->file))
						{
							$this->app->enqueueMessage(JText::sprintf('FLGSD_MESSAGE_FILE_DELETED', $old_params->file));
							
							unlink(JPATH_ROOT . '/' . $old_params->file);
						}
						
						$this->app->enqueueMessage(JText::sprintf('FLGSD_MESSAGE_FILE_CREATED', $new_params->file));
					}
					else
					{
						$this->app->enqueueMessage(JText::sprintf('FLGSD_MESSAGE_FILE_UPDATED', $new_params->file));
					}
					
					$this->generateFile($new_params);
				}
				else if(!is_file(JPATH_ROOT . '/' . $new_params->file))
				{
					$this->app->enqueueMessage(JText::sprintf('FLGSD_MESSAGE_FILE_CREATED', $new_params->file));
					
					$this->generateFile($new_params);
				}
			}
		}
		// Status changed
		else
		{
			if(!$new_params->enable)
			{
				if(is_file(JPATH_ROOT . '/' . $old_params->file))
				{
					$this->app->enqueueMessage(JText::sprintf('FLGSD_MESSAGE_FILE_DELETED', $old_params->file));
				
					unlink(JPATH_ROOT . '/' . $old_params->file);
				}
			}
		}
	}
}
