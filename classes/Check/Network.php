<?php

namespace App\Check;

use App\Commands\Check;

class Network
{
    public function __construct()
    {
    }

    /**
     * @param array $urls
     * @param string $method
     * @return array
     * @throws \Exception
     */
    public function getCurlInstances(array $urls, string $method = 'get'): array
    {
        $curlInstances = [];
        foreach ($urls as $url) {
            switch ($method) {
                case 'get':
                    $curlInstances[$url] = $this->getCurlInstanceGet($url);
                    break;
                case 'post':
                    $curlInstances[$url] = $this->getCurlInstancePost($url);
                    break;
                default:
                    throw new \Exception('unknown http method ' . $method);
                    break;
            }
        }
        return $curlInstances;
    }

    private function getCurlInstanceGet(string $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        return $ch;
    }

    private function getCurlInstancePost(string $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        return $ch;
    }

    public function execAll(array $instances): array
    {
        $chunks = array_chunk($instances, CHUNK_SIZE, true);
        $results = [];
        foreach ($chunks as $chunk) {
            $results = array_merge($results, $this->execChunk($chunk));
        }
        return $results;
    }

    public function execChunk(array $instances): array
    {
        $results = [];
        $mh = curl_multi_init();
        foreach ($instances as $url => $instance) {
            curl_multi_add_handle($mh, $instance);
        }
        $this->execSelection($mh);
        foreach ($instances as $url => $instance) {
            $results[$url] = curl_getinfo($instance, CURLINFO_RESPONSE_CODE);
            curl_multi_remove_handle($mh, $instance);
        }
        return $results;
    }

    private function execSelection(&$mh)
    {
        do {
            $status = curl_multi_exec($mh, $active);
            usleep(15000);
            if ($active) {
                curl_multi_select($mh);
            }
        } while ($active && in_array($status, [CURLM_OK, CURLM_CALL_MULTI_PERFORM], true));
        return $mh;
    }
}
