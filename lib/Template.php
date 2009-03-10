<?php

class Template 
{
   private $vars = array();
   private $registry = null;
   private $smarty = null;

   private $layoutFile = null;
   private $templateFile = null;

   const TEMPLATE_EXTENSION = '.tpl';

   function Template($registry)
   {
       $this->registry = $registry;

       $this->smarty = new Smarty();
       $this->smarty->template_dir = array(THEME_PATH.DIR_SEPARATOR.THEME.DIR_SEPARATOR.'templates');
       $this->smarty->compile_dir  = THEME_PATH.DIR_SEPARATOR.THEME.DIR_SEPARATOR.'compiled';
       $this->smarty->cache_dir    = THEME_PATH.DIR_SEPARATOR.THEME.DIR_SEPARATOR.'cache';
       $this->smarty->config_dir   = THEME_PATH.DIR_SEPARATOR.THEME.DIR_SEPARATOR.'configs';
       $this->smarty->assign('_this->smarty', $this->smarty);
       $this->smarty->assign('_baseURL', BASE_URL);
       $this->smarty->assign('_themeURL', BASE_URL.'/'.THEME_PATH.'/'.THEME.'/');

       $this->layoutFile = 'layout.tpl';
   }

   /*
    * Show the template
    * @param templateName should be in the format of $module-$action[-$extra]
    */
   function display($templateName)
   {
        $this->smarty->display($templateName.self::TEMPLATE_EXTENSION);
   }

   function render()
   {
       $this->content = '';
       if ($this->templateFile) 
       {
           $this->content = $this->smarty->fetch($this->templateFile.self::TEMPLATE_EXTENSION);
       }
       $this->smarty->display('layout'.self::TEMPLATE_EXTENSION);
   }

   function addTemplateDir($dir)
   {
       $this->smarty->template_dir[] = $dir;
   }
   
   function setLayout($layoutFile) { $this->layoutFile = $layoutFile; }
   function setTemplate($templateFile) { $this->templateFile = $templateFile; }

   /* FIXME: Should this be here? */
   function getLink($module, $action)
   {
       /* FIXME: Check to see if clean urls is turned on */
       return BASE_URL .'/' . $module .'/'. $action;
   }

   function __set($key, $var)
   {
      $this->smarty->assign($key, $var);
   }

   function __get($key)
   {
      return $this->smarty->get_template_vars($key);
   }    
}
