<?php
require_once OPS_APPLICATION_PATH 
    . '/services/Optimization/Helper/Abstract/Purify.php';

class Ops_Service_Optimization_Helper_PurifyHtml
    extends Ops_Service_Optimization_Helper_Abstract_Purify
{    
    public function __invoke($html)
    {
        //Remove comments 
        $this->eraseFragments($html, '<!--', '-->');
        
        //Remove scripts
        $html = preg_replace('~<\s*script\s*>.*?<\s*/\s*script\s*>~is', '', $html);
        
        //Normalize spaces and whitespace
        $this->massReplace(array("\n", "\r", "\n", "\t", '&nbsp;', '&#160;'), /*, chr(194) . chr(160), chr(160)) */
            ' ', $html);
             
        //Remove control chars
        $this->massReplace(
            array(
                chr(0), chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), 
                chr(8), chr(9), chr(10), chr(11), chr(12), chr(13), chr(14), 
                chr(15), chr(16), chr(17), chr(18), chr(19), chr(20), chr(21), 
                chr(22), chr(23), chr(24), chr(25), chr(26), chr(27), chr(28), 
                chr(29), chr(30), chr(31),
                
                chr(194) . chr(128), chr(194) . chr(129), chr(194) . chr(130), 
                chr(194) . chr(131), chr(194) . chr(132), chr(194) . chr(133), 
                chr(194) . chr(134), chr(194) . chr(135), chr(194) . chr(136), 
                chr(194) . chr(137), chr(194) . chr(138), chr(194) . chr(139), 
                chr(194) . chr(140), chr(194) . chr(141), chr(194) . chr(142), 
                chr(194) . chr(143), chr(194) . chr(144), chr(194) . chr(145), 
                chr(194) . chr(146), chr(194) . chr(147), chr(194) . chr(148), 
                chr(194) . chr(149), chr(194) . chr(150), chr(194) . chr(151),
                chr(194) . chr(152), chr(194) . chr(153), chr(194) . chr(154), 
                chr(194) . chr(155), chr(194) . chr(156), chr(194) . chr(157), 
                chr(194) . chr(158), chr(194) . chr(159),
            ), 
            '', $html);
        
        return wp_check_invalid_utf8($html, TRUE);
    } 
}