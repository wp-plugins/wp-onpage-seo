<?php
class Ops_Service_ImageCloak
{   
    public function getRealImageUrl($content, $index)
    {
        if (preg_match_all('~<\s*img\b[^>]*\bsrc\s*=\s*[\'"](.*?)[\'">]~isu', $content, $matches)) {
            if (isset($matches[1][$index-1])) {
                return $matches[1][$index-1];
            }
        }
        return NULL;
    }
}