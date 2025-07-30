<?php
namespace Concept\Http\Message\Request;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequestGlobalsFactory extends ServerRequestFactory
{
   
    /**
     * {@inheritDoc}
     */
    public function createServerRequest(
        string $method,
        $uri, 
        array $serverParams = [],
        ?array $headers = [], // Додано заголовки як параметр
        ?array $queryParams = [], 
        ?array $cookieParams = [], 
        ?array $uploadedFiles = [], 
        ?array $parsedBody = []
    ): ServerRequestInterface
    {
        $headers = $headers ?? getallheaders();
        $queryParams = $queryParams ?? $_GET;
        $cookieParams = $cookieParams ?? $_COOKIE;
        $uploadedFiles = $uploadedFiles ?? $_FILES;
        $parsedBody = $parsedBody ?? $_POST;


        return parent::createServerRequest(
            $method,
            $uri,
            $serverParams,
            $headers,
            $queryParams,
            $cookieParams,
            $uploadedFiles,
            $parsedBody
        );
    }

}
