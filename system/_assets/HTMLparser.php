<?php

/* ===================================
 * Author: Nazarkin Roman
 * -----------------------------------
 * Contacts:
 * email - roman@nazarkin.su
 * icq - 642971062
 * skype - roman444ik
 * -----------------------------------
 * GitHub:
 * https://github.com/NazarkinRoman
 * ===================================
*/

class HTMLparser
{

    public $__encoding,
        $__url,
        $__error,
        $title,
        $description;

    private $idn;

    function __construct($url)
    {
        $this->idn   = new IDNAConvert();
        $this->__url = $this->idn->encode_uri($url);

        // check headers
        $url = $this->getURL($this->__url, true);
        if ($this->__error !== null) {
            return false;
        }

        $headers = get_headers($url, 1);
        if (isset($headers['Content-Type'])) {
            $this->processContentType($headers['Content-Type']);
        }

        $this->parsePage();
    }

    /**
     * Parse `Content-Type` string for encoding
     *
     * @param $str
     * @return bool
     */
    private function processContentType($str)
    {
        if (is_array($str)) {
            $str = $str[0];
        }
        preg_match('/charset=([a-z0-9-]{4,})/', $str, $charset);

        if ($charset !== false && is_array($charset) && isset($charset[1])) {
            $result = str_replace('-', '', $charset[1]);
        } else {
            $result = null;
        }

        if ($result !== null && in_array($result, array('utf8', 'cp1251', 'windows1251'))) {
            $this->__encoding = $result;
            return true;
        }

        return false;
    }

    /**
     * Parse page HTML contents. Take meta-tags and encoding params.
     *
     * @return mixed
     */
    private function parsePage()
    {
        if (!class_exists('DOMDocument')) {
            return;
        }

        $html     = $this->getURL($this->__url);
        $metadata = array();

        // parsing begins here
        if ($this->__encoding == null || $this->__encoding == 'utf8') {
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        }

        $doc = new DOMDocument();
        if (!@$doc->loadHTML($html)) {
            return;
        }
        $nodes = $doc->getElementsByTagName('title');

        // get title & other meta
        $metadata['title'] = $nodes->item(0)->nodeValue;
        $metas             = $doc->getElementsByTagName('meta');

        for ($i = 0; $i < $metas->length; $i++) {
            $meta = $metas->item($i);

            if ($meta->getAttribute('name') == 'description') {
                $metadata['description'] = $meta->getAttribute('content');
            }

            if ($this->__encoding === null
                && strtolower($meta->getAttribute('http-equiv')) == 'content-type'
                && $this->processContentType($meta->getAttribute('content'))
            ) {
                return $this->parsePage();
            }
        }

        array_walk($metadata, array($this, 'stripStr'));

        $this->title       = ($metadata['title'] !== null) ? $metadata['title'] : null;
        $this->description = (isset($metadata['description']) && $metadata['description'] !== null)
            ? $metadata['description'] : null;
    }

    /**
     * Strip from string any HTML code
     *
     * @param $str
     */
    public static function stripStr(&$str)
    {
        $str = preg_replace("'<style[^>]*>.*</style>'siU", '', $str); // strip js
        $str = preg_replace("'<script[^>]*>.*</script>'siU", '', $str); // strip css
        $str = trim(preg_replace('|\s+|', ' ', $str)); // strip spaces

        if (!preg_match('/[a-zA-Zа-яА-ЯёЁ0-9]{3,}/', $str)) {
            $str = null;
        }

        if (strlen($str) < 3) {
            $str = null;
        }

        if (strlen($str) > 350) {
            $str = trim(substr($str, 0, 350)) . '..';
        }
    }

    /**
     * Download page contents
     *
     * @param      $url
     * @param bool $onlyurl
     * @return mixed|string
     */
    private function getURL($url, $onlyurl = false)
    {
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $out = $this->curlRedirect($curl);
            if (curl_errno($curl)) {
                $this->__error = curl_error($curl);
            } elseif ($onlyurl) {
                $this->setURL(curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
                $out = $this->__url;
            }
            curl_close($curl);

            return $out;
        }
    }

    /**
     * Adds support for CURLOPT_FOLLOWLOCATION on systems with `safe_mode = On`
     *
     * @param     $ch
     * @param int $maxredirect
     * @return mixed
     */
    private function curlRedirect($ch, $maxredirect = 5)
    {
        if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            return curl_exec($ch);
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $this->setURL(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));

        $rch = curl_copy_handle($ch);
        curl_setopt($rch, CURLOPT_HEADER, true);
        curl_setopt($rch, CURLOPT_NOBODY, true);
        curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
        do {
            curl_setopt($rch, CURLOPT_URL, $this->__url);
            $header = curl_exec($rch);
            if (curl_errno($rch)) {
                $code = 0;
            } else {
                $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                if ($code == 301 || $code == 302) {
                    preg_match('/Location:(.*?)\n/', $header, $matches);
                    $this->setURL(trim(array_pop($matches)));
                } else {
                    $code = 0;
                }
            }
        } while ($code && $maxredirect--);
        curl_close($rch);
        curl_setopt($ch, CURLOPT_URL, $this->__url);

        return curl_exec($ch);
    }

    /**
     * Set URL variable
     *
     * @param string $url
     */
    private function setURL($url)
    {
        $this->__url = $this->idn->encode_uri($url);
    }

}