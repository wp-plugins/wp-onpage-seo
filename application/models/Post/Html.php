<?php
class Ops_Model_Post_Html
{
    protected $_proxyUrl = 'http://www.wpthorp.com/plugin-proxy/proxy.php';

    protected $_timeout = 60;

    protected $_userAgent = 'Mozilla/5.0 (Windows NT 5.1; rv:13.0) Gecko/20100101 Firefox/13.0.1';

    protected $_encodingDetectOrder = array(
        'UTF-8',
        'ISO-8859-1',
        'CP1251',
    );

    public function get($post)
    {
        $postId = $post->ID;

        //Unlock session
        $sessionId = session_id();
        if ('' != $sessionId) {
            session_commit();
        }

        $url = add_query_arg(
            array(
                'preview' => 'true',
                'ocs_preview' => 'true',
                'adminbar' => 'false'
            ),
            get_permalink($post->ID)
        );

        $response = $this->_request($url);

        if ('' != $sessionId) {
            @session_start();
        }

        if ('' != $response['error']) {
            require_once OPS_APPLICATION_PATH . '/services/Exception.php';
            throw new Ops_Service_Exception(
                "Error getting post HTML: {$response['error']}");
        }

        return $this->_getUtf8Html($response);
    }

    /*
    public function actionApiCurl($handle)
    {
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, TRUE);
    }
    */

    /**
    * Ensures HTML is UTF-8 encoded
    */
    protected function _getUtf8Html(array $response)
    {
        $result = $response['body'];

        $contentType = $response['content_type'];
        if (empty($contentType) || strpos($contentType, 'text/html')===0) {
            $encoding = function_exists('mb_detect_encoding')
                ? strtolower(mb_detect_encoding($result, $this->_encodingDetectOrder))
                : NULL;

            if (empty($encoding)) {
                if (preg_match('~<head\b[^>]*>(.*?)</head>~is', $result, $matches)) {
                    $head = $matches[1];
                    if (preg_match('~<meta\b.*?\bhttp-equiv="Content-Type".*?\bcontent=["\'](.*?)["\']~is', $head, $matches)) {
                        $encodingHead = $this->_extractEncoding($matches[1]);
                        if (!empty($encodingHead)) {
                            $encoding = $encodingHead;
                        }
                    }
                }
            }

            if (!in_array($encoding, array('utf-8', 'utf8'))) {
                if (!empty($encoding)) {
                    $result = iconv($encoding, 'utf-8', $result);
                } else {
                    $result = utf8_encode($result);
                }

                if (empty($result)) {
                    $result = $response['body'];
                }
            }
        }

        return wp_check_invalid_utf8($result, TRUE);
    }

    protected function _extractEncoding($contentType)
    {
        if (preg_match('~\bcharset=(.*)[;$]~is', $contentType, $matches)) {
            $result = strtolower(trim($matches[1]));
        } else {
            $result = '';
        }

        return $result;
    }

    protected function _request($url)
    {
        $result = $this->_doRequest($url);
        if (CURLE_COULDNT_CONNECT == $result['errno']
            || (isset($result['code']) && in_array($result['code'], array(403, 404)))
        ) {
            // Workaround for security check on the hosting: remove "//" after a scheme
            $url = preg_replace('~^(https?)\://~i', '${1}:', $url);
            $result = $this->_httpRequest($this->_proxyUrl . '?url=' . urlencode($url));
            if (399 == $result['code']) {
                if ($error = @json_decode($result['body'])) {
                    $result['error'] = isset($error->message)? $error->message : 'unknown error';
                    $result['error'] .= ' (returned from proxy)';
            	} else {
            		$result['error'] = json_last_error();
            	}
            } else if ('' != $result['error']) {
                $result['error'] .= ' (while connecting to proxy)';
            }
        }

        return $result;
    }

    // Workaround for redirects not supported
    protected function _doRequest($url)
    {
        for ($i = 0; $i < 30; $i++) {
            $result = $this->_httpRequest($url);
            if (isset($result['headers']['location'])
                && $url != $result['headers']['location']
                && 200 != $result['code']
                && '' == $result['error']
            ) {
                $oldUrl = $url;

                $url = $result['headers']['location'];

                // Workaround against <url> malformed error
                $url = remove_query_arg('doing_wp_cron', $url);
                if ('/' == $url[0]) {
                    // Relative URL
                    $url = parse_url($oldUrl, PHP_URL_SCHEME) . '://' . parse_url($oldUrl, PHP_URL_HOST) . $url;
                }

            } else {
                break;
            }
        }

        if ('' == $result['error'] && 200 != $result['code']) {
            $result['error'] = 'HTTP Error (' . $result['code'] . ')';
        }

        return $result;
    }

    protected function _httpRequest($url)
    {
        $result = array(
            'code' => 0,
            'error' => '',
            'errno' => '',
            'body' => '',
            'content_type' => NULL,
            'headers' => array(),
        );

        $curl = curl_init($url);

        curl_setopt_array($curl, array(
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HEADER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Cookie: ' . $_SERVER['HTTP_COOKIE']),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_FAILONERROR => TRUE,
            CURLOPT_USERAGENT => $this->_userAgent,
        ));

        $response = curl_exec($curl);

        $result['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (FALSE === $response) {
            $result['error'] = curl_error($curl);
            $result['errno'] = curl_errno($curl);
            if ('' == $result['error']) {
                $result['error'] = 'Unknown error';
            }
        } else {
            list($headers, $result['body']) = explode("\r\n\r\n", $response, 2);
            $result['headers'] = $this->_parseHeaders($headers);
            $result['content_type'] = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        }

        curl_close($curl);

        return $result;
    }

    protected function _parseHeaders($headers)
    {
        $lines  = explode("\r\n", trim($headers));
        $result = array('_status' => trim($lines[0]));
        unset($lines[0]);

        foreach ($lines as $line) {
            list($name, $value) = explode(':', $line, 2);
            $result[strtolower(trim($name))] = trim($value);
        }

        return $result;
    }
}