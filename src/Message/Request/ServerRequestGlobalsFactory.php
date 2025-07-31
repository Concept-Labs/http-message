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
        ?array $headers = null, 
        ?array $queryParams = null, 
        ?array $cookieParams = null, 
        ?array $uploadedFiles = null, 
        ?array $parsedBody = null
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
