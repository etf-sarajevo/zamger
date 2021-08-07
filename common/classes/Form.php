<?php

class Form{
    public static function setOptions($options){
        $optionsStr = '';
        foreach ($options as $key => $val){
        	if($key) $optionsStr .= $key.'="'.$val.'" ';
        	else $optionsStr .= $val.'="'.$val.'" ';
        }
        return $optionsStr;
    }
    public static function select($name, $values, $value, $options, $default = ''){
        $optionsStr = self::setOptions($options);
        $data = '<select name="'.$name.'" '.$optionsStr.'>';
        
        // lets insert empty value at the beginning of every array with value $default
        if(count($values)){
        	if(is_array($values[0])){
				array_unshift($values, [
					0 => '',
					1 => 'Odaberite '. $default
				]);
			}else array_unshift($values, 'Odaberite ' . $default);
		}
		
        foreach ($values as $key => $val){
            if(!is_array($val)){
                $data .= '<option value="'.($key).'" '.(($key == $value) ? 'selected' : '').'>'.($val).'</option>';
            }else $data .= '<option value="'.($val[0]).'" '.(($val[0] == $value) ? 'selected' : '').'>'.($val[1]).'</option>';
        }
        return ($data .= '</select>');
    }
    public static function text($name, $value, $options){
        $optionsStr = self::setOptions($options);
        return '<input type="text" value="'.$value.'" name="'.$name.'" '.$optionsStr.'>';
    }
    public static function number($name, $value, $options){
        $optionsStr = self::setOptions($options);
        return '<input type="number" value="'.$value.'" name="'.$name.'" '.$optionsStr.'>';
    }
    public static function email($name, $value, $options){
        $optionsStr = self::setOptions($options);
        return '<input type="email" value="'.$value.'" name="'.$name.'" '.$optionsStr.'>';
    }
    public static function hidden($name, $value, $options){
        $optionsStr = self::setOptions($options);
        return '<input type="hidden" value="'.$value.'" name="'.$name.'" '.$optionsStr.'>';
    }
}
