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

class FlGSDComponentContent extends FlGSDPlugin
{
	protected $views = 
		[
			'article' => 'COM_CONTENT_ARTICLE_CONTENT',
			'category' => 'JCATEGORIES',
			'featured' => 'JFEATURED'
		];
	
	protected $forms = 
		[
			'com_content.article' => 'com_content.article',
			'com_categories.categorycom_content' => 'com_content.category',
		];
	
	function onContentPrepareForm($form, $data)
	{
		// var_dump('onContentPrepareForm');
		
		// Check we in Admin panel and have a form.
		if(!$this->app->isClient('administrator') && !($form instanceof JForm))
		{
			return;
		}
		
		$form_name = $form->getName();
		
		if(array_key_exists($form_name, $this->forms))
		{
			$file = $this->forms_path . 'component/' . $this->forms[$form_name]. '.xml';
			
			$form->loadFile($file, false);
			
		}
		
		// var_dump($this->forms_path);
		
		// var_dump($form_name);
		// var_dump($this->app->input->getArray());
	}
	
	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		// var_dump($context);
	}
}
