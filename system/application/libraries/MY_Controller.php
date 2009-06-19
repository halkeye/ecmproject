<?php
class MY_Controller extends Controller
{  
    function render()
    {
        $vars = $this->load->_ci_cached_vars; //FIXME right now i can only find the var name, not the function to get it
            
        $this->data['class'] = $this->router->class;
        $this->data['method'] = ($this->router->method == 'index') ? NULL : $this->router->method.'_';
        $this->data['view'] = $this->data['class'].'/'.$this->data['class'].'_'.$this->data['method'].'view';
        
        $this->data['heading']    = isset($vars['heading'])    ? $vars['heading']    : ucfirst($this->router->method);
        $this->data['subheading'] = isset($vars['subheading']) ? $vars['subheading'] : ucfirst($this->data['class']);
        // Load the view
        $this->load->view('layout', $this->data);
    }
} 
