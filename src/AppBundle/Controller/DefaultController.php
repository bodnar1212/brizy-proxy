<?php

namespace AppBundle\Controller;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction($resource, Request $request)
    {
        $originalUrl =  $this->findOriginalUrl($request->getHost());
        if (!$originalUrl) {
            return new Response("This host can't use proxy", 500);
        }

        $url     = $originalUrl . "/" . $resource;
        $method  = $request->getMethod();
        $headers = $this->normalizeHeaders($request->headers->all());
        $client  = new Client([
            'headers'    => $headers,
            'exceptions' => false
        ]);

        $data = [
            'query'       => $request->query->all(),
            'form_params' => $request->request->all()
        ];

        $response = $client->request($method, $url, $data);

        $headers  = $response->getHeaders();
        $response = new Response($response->getBody(), $response->getStatusCode());
        $response->headers->add($headers);

        return $response;
    }

    private function normalizeHeaders(array $request_headers)
    {
        $headers = [];
        foreach ($request_headers as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                $value = $value[0];
            } else {
                $value = "";
            }

            $headers[][$key] = $value;
        }

        return $headers;
    }

    private function findOriginalUrl($host)
    {
        foreach ($this->getKeyWords() as $keyWord) {
            if (strpos($host, $keyWord) !== false) {
                return $this->getUrlsByKeyWord()[$keyWord];
            }
        }

        return null;
    }

    private function getUrlsByKeyWord()
    {
        return [
            'compiler'  => 'http://compiler.brizy.io',
            'static'    => 'https://static.brizy.io',
            '127.0.0.1' => 'http://www.brizycompiler.run'
        ];
    }

    private function getKeyWords()
    {
        return [
            'compiler',
            'static',
            '127.0.0.1'
        ];
    }

}
