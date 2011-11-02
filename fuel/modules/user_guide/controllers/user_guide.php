<?php
/**
 * FUEL CMS
 * http://www.getfuelcms.com
 *
 * An open source Content Management System based on the 
 * Codeigniter framework (http://codeigniter.com)
 *
 * @package		FUEL CMS
 * @author		David McReynolds @ Daylight Studio
 * @copyright	Copyright (c) 2011, Run for Daylight LLC.
 * @license		http://www.getfuelcms.com/user_guide/general/license
 * @link		http://www.getfuelcms.com
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * User guide 
 *
 * @package		FUEL CMS
 * @subpackage	Libraries
 * @category	Libraries
 * @author		David McReynolds @ Daylight Studio
 * @link		http://www.getfuelcms.com/user_guide/modules/user_guide
 */

// --------------------------------------------------------------------

require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');

class User_guide extends Fuel_base_controller {
	
	public $current_page;
	
	function __construct()
	{
		parent::__construct(FALSE);
		if ($this->fuel->user_guide->config('authenticate'))
		{
			$this->fuel->admin->check_login();
		}
	}
	
	function _remap()
	{
		$this->load->module_library(FUEL_FOLDER, 'fuel_pagevars');
		$this->current_page = $this->fuel->user_guide->current_page();

		$this->fuel_pagevars->vars_path = USER_GUIDE_PATH.'views/_variables/';
		$vars = array();
		
		// get modules
		$modules = array('', 'fuel');
		$modules = array_merge($modules, $this->config->item('modules_allowed', 'fuel'));

		$vars = $this->fuel->user_guide->get_vars($this->current_page);
		
		foreach($modules as $m)
		{
			if ((!$this->fuel->user_guide->config('authenticate') OR $this->fuel->auth->has_permission('user_guide_'.$m)) AND 
				file_exists(MODULES_PATH.$m.'/views/_docs/index'.EXT)) 
			{
				$module_view = $this->load->module_view($m, '_docs/index', array(), TRUE);
				$mod_page_title = $this->fuel->user_guide->get_page_title($module_view);
				$vars['modules'][$m] = (!empty($mod_page_title)) ? $mod_page_title : humanize($m).' Module';
			}
		}
		
		// render page
		// pull from modules folder if URI says so	
		$uri_path_index = count(explode('/', $this->fuel->user_guide->config('root_url'))) + 1;
		$module_page = uri_path(FALSE, $uri_path_index);
		$module_view_path = (!empty($module_page)) ? '_docs/'.$module_page : '_docs/index';
		
		if ($this->fuel->user_guide->get_page_segment(1) == 'modules' AND 
			($this->fuel->user_guide->get_page_segment(2) AND file_exists(MODULES_PATH.$this->fuel->user_guide->get_page_segment(2).'/views/'.$module_view_path.EXT)))
		{
			$module = $this->fuel->user_guide->get_page_segment(2);

			
			if (!$this->fuel->user_guide->config('authenticate') OR $this->fuel->auth->has_permission('user_guide_'.$module))
			{
				$vars['body'] = $this->load->module_view($module, $module_view_path, $vars, TRUE);
				if ($this->fuel->user_guide->get_page_segment(3))
				{
					$vars['sections'] = array($vars['modules'][$module] => 'modules/'.$module);
				}
			}
		}
		else
		{
			if (!is_file(USER_GUIDE_PATH.'views/'.$this->current_page.EXT))
			{
				show_404();
			}
			$vars['body'] = $this->load->module_view(USER_GUIDE_FOLDER, $this->current_page, $vars, TRUE);
			if ($this->fuel->user_guide->get_page_segment(2))
			{
				$vars['sections'] = $this->fuel->user_guide->get_breadcrumb($this->current_page);
			}
		}
		$vars['page_title'] = $this->fuel->user_guide->get_page_title($vars['body']);
		
		$this->load->module_view(USER_GUIDE_FOLDER, '_layouts/user_guide', $vars);
	}
}

/* End of file user_guide.php */
/* Location: ./fuel/modules/user_guide/controllers/user_guide.php */