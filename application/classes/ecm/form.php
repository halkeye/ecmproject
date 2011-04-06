<?php defined('SYSPATH') OR die('No Direct Script Access');

class ECM_Form {
    public static function parseSplitDate(array & $post, $fieldName)
    {
        $ret = implode('-', 
            array(
                @sprintf("%04d", $post[$fieldName . '-year']), 
                @sprintf("%02d", $post[$fieldName . '-month']), 
                @sprintf("%02d", $post[$fieldName . '-day'])
            )
        );  
        unset ($post[$fieldName . '-year']); 
        unset ($post[$fieldName . '-month']);
        unset ($post[$fieldName . '-day']);
        return $ret;
    }
}
