<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

abstract class Ops_Service_Optimization_Helper_Abstract_Purify
    extends Ops_Service_Optimization_Helper_Abstract_Base
{        
    public function massReplace(array $search, $replace, &$subject)
    {
        foreach ($search as $s) {
            $subject = str_replace($s, $replace, $subject);
        }
        
        return $this;
    }
    
    public function eraseFragments(&$subject, $start, $end)
    {
        for ($i=0; $i<200; $i++) {
            $posStart = strpos($subject, $start);
            if ($posStart===FALSE) break;
            $pos = $posStart+strlen($start);
            if ($pos >= strlen($subject)) {
                break;
            }
            $posEnd = strpos($subject, $end, $pos);
            if ($posEnd===FALSE) break;
            
            $subject = substr($subject, 0, $posStart) . substr($subject, $posEnd + strlen($end));
        }
        
        return $this;
    }
}
