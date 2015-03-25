<?php

require_once(dirname(__FILE__) . '/HelperAbstract.php');
  
abstract class Ops_View_Helper_HtmlTagAbstract 
    extends Ops_View_Helper_HelperAbstract
{
    protected function _renderTag($tag=null, $content='', $attributes=array())
    {        
        if (!is_int($content)) {
            $result = "<{$tag}{$this->_prepareAttributes($attributes)}>{$content}</{$tag}>";    
        } else {
            switch ($content) {
                case Ops_View_Engine::TAG_CLOSED:
                    $result = "</{$tag}>";
                    break;
                case Ops_View_Engine::TAG_OPEN:
                    $result = "<{$tag}{$this->_prepareAttributes($attributes)}>";
                    break;
                default:  //Self-closing
                    $result = "<{$tag}{$this->_prepareAttributes($attributes)} />";
            }     
        }
        
        return $result;
    }
    
    protected function _prepareAttributes(array $attributes) 
    {
        if (!count($attributes)) {
            return '';
        }
        
        if (is_array(@$attributes['class'])) {
            if (count($attributes['class'])) {
                $attributes['class'] = $this->_renderClass($attributes['class']);
            } else {
                unset($attributes['class']);    
            }
        }   
        if (is_array(@$attributes['style'])) {
            if (count($attributes['style'])) {
                $attributes['style'] = $this->_renderStyle($attributes['style']);  
            } else {
                unset($attributes['style']);    
            }
        } 
        
        $attribCol = array();
        foreach ($attributes as $name=>$val) {
            if (is_null($val)) {
                continue;      
            }
            
            if (is_bool($val)) {
                if ($val) {
                    $val = $name;    
                } else {
                    continue;
                }
            }
            
            $name = $this->_view->escape($name);     
            $val = $this->_view->escape($val);
            $attribCol[] = "{$name}=\"{$val}\"";
        } 
        $result = ' ' . implode(' ', $attribCol);   
        
        return $result;
    }
   
    protected function _renderClass($classes) 
    {
        return implode(' ', $classes);    
    }
   
    protected function _renderStyle($styles) 
    {
        $result = array();
        foreach ($styles as $name=>$value) {
            $result[] = "{$name}: {$value}";
        }   
        
        return implode('; ', $result);
    }
}    
