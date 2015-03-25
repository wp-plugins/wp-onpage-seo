<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_GetRelatedTerms
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    const CACHE_OPTION = 'related_terms_cache';
    const CACHE_LIFETIME = 86400;
    const API_BASE_URL = 'https://api.datamarket.azure.com/Data.ashx/Bing/Search/RelatedSearch';

    protected $_cache;

    public function __invoke($keyword = NULL)
    {
        $this->_loadCache();

        if (is_null($keyword)) {
            $keyword = $this->_parent->getData('keyword');
        }
        $locale = str_replace('_', '-', get_locale());
        $cacheKey = sha1($keyword . "\n" . $locale);

        if (isset($this->_cache[$cacheKey][0])) {
            return $this->_cache[$cacheKey][0];
        }

        if ('' == $this->_getApiKey()) {
            return array(); // Not configured
        }

        $response = $this->_request(self::API_BASE_URL, array(
            'Query'     => "'{$keyword}'",
            'Market'    => "'{$locale}'",
            '$format'   => 'JSON',
        ));

        $response = @json_decode($response);
        if (FALSE === $response) {
            require_once OPS_APPLICATION_PATH . '/services/Exception.php';
            throw new Ops_Service_Exception('Error decoding API response');
        }

        $result = array();
        if (isset($response->d->results)) {
            foreach ($response->d->results as $value) {
                $result[] = $value->Title;
            }
        } else {
            require_once OPS_APPLICATION_PATH . '/services/Exception.php';
            throw new Ops_Service_Exception('Invalid response format');
        }

        return $result;
    }

    public function _request($url, $params)
    {
        if ($params) {
            $url .= '?' . http_build_query($params, NULL, '&');
        }

        $apiKey = $this->_getApiKey();

        $curl = curl_init($url);

        curl_setopt_array($curl, array(
            CURLOPT_USERPWD => $apiKey . ':' . $apiKey,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            //CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_FAILONERROR => TRUE,
        ));

        $response = curl_exec($curl);

        if (FALSE === $response) {
            $error = curl_error($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);

            switch ($code) {
                case 401:
                    $error = 'Invalid Bing API access key';
                    break;

                case 403:
                    $error = 'You are not subscribed to the Bing API service';
                    break;

                case 503:
                    $error = 'Query limit exceeded';
                    break;

                default:
                    if ('' == $error) {
                        $error = $code? "HTTP error #{$code}" : 'Unknown error';
                    }
            }

            throw new Ops_Service_Optimization_Helper_GetRelatedTerms_Exception ($error, $code);
        }

        curl_close($curl);

        return $response;
    }

    protected function _getApiKey()
    {
        $result = Ops_Application::getModel('Options')->getValue('bing_api_key');

        return $result;
    }

    protected function _loadCache()
    {
        if (!is_null($this->_cache)) {
            return;
        }

        $result = Ops_Application::getModel('Options')->getValue(self::CACHE_OPTION);
        if (!is_array($result)) {
            $this->_cache = array();
            return;
        }

        $expire = time() - self::CACHE_LIFETIME;
        // Garbage collection
        foreach ($result as $key=>$item) {
            if (!isset($item[1]) || $item[1] < $expire) {
                unset($result[$key]);
            }
        }

        $this->_cache = $result;
    }

    protected function _saveCache()
    {
        Ops_Application::getModel('Options')->setValue(self::CACHE_OPTION,
            $this->_cache);
    }
}

require_once OPS_APPLICATION_PATH . '/services/Exception.php';
class Ops_Service_Optimization_Helper_GetRelatedTerms_Exception
    extends Ops_Service_Exception
{

}