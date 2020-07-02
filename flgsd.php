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

use Joomla\Registry\Registry;

	//очередность
	//onAfterInitialise
	//onAfterRoute
	//onAfterDispatch
	//onBeforeRender
	//onBeforeCompileHead
	//onAfterRender


class plgSystemFlGSD extends JPlugin
{
	/**
	 * Plugin default variables
	 * -----------------
	 * @var boolean $autoloadLanguage;
	 * @var JApplication $app;
	 * @var JDatabaseDriver $db;
	 * @var JRegistry $params;
	 * @var string $_name;
	 * @var string $_type;
	 * @var JEventDispatcher $_subject;
	 * @var Array $_errors;
	 */
	
	protected $autoloadLanguage = true;
	
	protected $app;
	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		
		JLoader::Register('FlGSDPlugin', __DIR__ . '/plugins/plugin.php');
		
		if($this->app->isClient('administrator'))
		{
			foreach($this->params->toObject() as $group => $items)
			{
				switch($group)
				{
					case 'component':
					case 'app':
					case 'schemaorg':
						foreach($items as $item => $params)
						{
							if($params->enable && isset($params->state))
							{
								$class = 'FlGSD'.$group.$item;
								$file = __DIR__ . '/plugins/'.$group.'/'.$item.'.php';
								
								JLoader::Register($class, $file);
								new $class($this->_subject, $params);
							}
						}
						break;
				}
			}
		}
	}
	
	
	function onAfterRoute()
	{
		if($this->app->isClient('site'))
		{
			if(JFactory::getDocument()->getType() == 'html')
			{
				foreach($this->params->toObject() as $group => $items)
				{
					switch($group)
					{
						case 'app':
						case 'schemaorg':
							foreach($items as $item => $params)
							{
								if($params->enable && isset($params->state))
								{
									$class = 'FlGSD'.$group.$item;
									$file = __DIR__ . '/plugins/'.$group.'/'.$item.'.php';
								
									JLoader::Register($class, $file);
									new $class($this->_subject, $params);
								}
							}
							break;
						case 'component':
							$component = str_replace('com_', '', $this->app->input->getArray()['option']);
							
							if(isset($items->$component) && $items->$component->enable && isset($items->$component->state))
							{
								$class = 'FlGSDComponent' . $component;
								$file = __DIR__ . '/plugins/'.$group.'/' . $component . '.php';
								
								JLoader::Register($class, $file);
								new $class($this->_subject, $value);
							}
							
							break;
					}
				}
			}
		}
	}
	
	
	function onContentPrepareForm($form, $data)
	{
		// Check we in Admin panel and have a form.
		if(!$this->app->isClient('administrator') && !($form instanceof JForm))
		{
			return;
		}
		
		// Run in plugin
		if($form->getName() == 'com_plugins.plugin' && $form->getField('plg_system_flgsd'))
		{
			// Set plugin forms.
			$path = __DIR__ . '/forms/plugin/';
			$forms = [];
			
			//var_dump($this->params);
			
			foreach(scandir($path) as $file)
			{
				$pathinfo = pathinfo($file);
				
				if(is_file($path . $file) && $pathinfo['extension'] == 'xml')
				{
					$forms[$pathinfo['filename']] = $path . $file;
				}
			}
			
			ksort($forms);
			
			$xml_s = '<?xml version="1.0" encoding="UTF-8"?><form><fields name="params"><fieldset name="basic">';
			
			$xml_e = '</fieldset></fields></form>';
			
			$group = null;
			
			foreach($forms as $key => $file)
			{
				$parts = explode('.', $key);
				
				if($group != $parts[1])
				{
					$group = $parts[1];
					
					$xml = '<field type="note" label="FLGSD_GROUP_' . strtoupper($group) . '"/>';
					
					$form->load($xml_s.$xml.$xml_e);
				}
				
				if(isset($parts[3]))
				{
					$plugin = $parts[3];
					
					$xml = '<fields name="' . $group . '"><fields name="' . $plugin . '">'
							 
							 . '<field name="enable" label="FLGSD_' . strtoupper($plugin) . '" type="radio" class="btn-group btn-group-yesno" default="0"><option value="0">JOFF</option><option value="1">JON</option></field>'
							 
							 . '</fields></fields>';
					
					$form->load($xml_s.$xml.$xml_e);
					
					$option = $this->params->get($group);
					
					if($option && isset($option->$plugin->enable) && $option->$plugin->enable)
					{
						$form->loadFile($file, false);
					}
				}
				else
				{
					$form->loadFile($file, false);
				}
			}
		}
	}
	
	
	private function writeConfigFile()
	{
		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');

		// Set the configuration file path.
		$file = JPATH_CONFIGURATION . '/configuration.php';
		
		include_once($file);
		
		$object = new JConfig();
		
		$config = new Registry();
		
		$config->loadObject($object);
		// $config->set('languageasd', 'asd');
		// $config->set('sitenameasd', 'asdasd');
		
		// Get the new FTP credentials.
		$ftp = JClientHelper::getCredentials('ftp', true);

		$app = JFactory::getApplication();

		// Attempt to make the file writeable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0644'))
		{
			$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTWRITABLE'), 'notice');
		}

		// Attempt to write the configuration file as a PHP class named JConfig.
		$configuration = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));

		if (!JFile::write($file, $configuration))
		{
			throw new RuntimeException(JText::_('COM_CONFIG_ERROR_WRITE_FAILED'));
		}

		// Attempt to make the file unwriteable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0444'))
		{
			$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'), 'notice');
		}

		return true;
	}
}
