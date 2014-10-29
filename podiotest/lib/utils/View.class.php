<?php
/**
 * View class that handles templates, useful in MVC pattern
 */

class View 
{

    public $arr;
    public $file;
    
    public $jsVals;
    
    public function __construct() 
    {
        $this->arr = array();
        $this->file = null;
        $this->jsVals = array();
    }
    
    
    /**
     * Set the template file
     * @param string $file 
     */
    public function setTemplateFile($file)
    {
        $this->file = $file;
    }

    /**
     * Process the view and the template
     * @param Mixed $obj
     * @return string 
     * @throws Exception 
     */
    protected function get_sub_views($obj) 
    {

        foreach ($obj as $varname => $var) 
        {
            if ($var instanceof View) 
            {
                $obj->arr[$varname] = $var->get_sub_views($var);
            } 
            else 
            {
                $obj->arr[$varname] = $var;
            }
        }
        extract($obj->arr);
        ob_start();
        if (file_exists($obj->file)) {
        include $obj->file;
        } else {
        throw new Exception('The view file ' .  $obj->file . 'is not available');
        }
        $html = ob_get_clean();

        return $html;
    }
    
    /**
     * Return the view rendered in a string
     * @return string 
     */
    public function render() {
    return self::get_sub_views($this);
    }

}

