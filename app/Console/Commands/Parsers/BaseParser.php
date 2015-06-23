<?php

namespace App\Console\Commands\Parsers;

use Yangqi\Htmldom\Htmldom;

abstract class BaseParser implements IParser
{
    protected $domainURL;
    protected $catalogURL;
    protected $pageEncoding;

    public function info($message)
    {
        if (!is_string($message)) {
            echo 'Non string.' . PHP_EOL;
            return;
        }

        echo($message . PHP_EOL);
    }

    protected function downloadWithCURL($url)
    {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($c, CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2255.0 Safari/537.36');
        return curl_exec($c);
    }

    /*
    * Get page encoding.
    */
    protected function getEncoding($url)
    {
        $metaCharset = new Htmldom($url);
        $foundCharset = $metaCharset->find('meta[charset]');

        if ($foundCharset) {
            return last($foundCharset)->getAttribute('charset');
        }

        return mb_detect_encoding($this->downloadWithCURL($url));
    }

    protected function toUTF8($text)
    {
        return trim(iconv($this->pageEncoding, 'utf-8', $text));
    }

    protected function generateNeedfulHtmldom($source)
    {
        return new Htmldom($this->downloadWithCURL($source));
    }

    protected function parseModification(array $data)
    {
        $modification = null;
        $domainAnchor = null;
        $brandModelID = null;

        if (array_key_exists('modification', $data)) {
            $modification = $data['modification'];
        }

        if (array_key_exists('domainAnchor', $data)) {
            $domainAnchor = $data['domainAnchor'];
        }

        if (array_key_exists('brandModelID', $data)) {
            $brandModelID = $data['brandModelID'];
        }

        if (empty($modification) or
            empty($domainAnchor) or
            empty($brandModelID)
        ) {
            return;
        }

        $this->parseModificationDirect($modification, $domainAnchor, $brandModelID);
    }

    protected function handleName($name)
    {
        $name = trim($name);
        return $name;
    }

    protected function handleModificationName($name)
    {
        $name = $this->handleName($name);
        $name = str_replace('Характеристики ', '', $name);
        $name = str_replace('&ndash;', '–', $name);
        $name = str_replace('&hellip;', '…', $name);
        return $name;
    }

    abstract protected function parseModificationDirect($modification, $domainAnchor, $brandModelID);
}