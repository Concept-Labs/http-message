<?php
namespace Concept\Http\Message\Request;


interface ServerRequestAttributesInterface
{
    public const SERVER_ATTRIBUTES_TO_ADD = [
        'REQUEST_TIME',
        'REQUEST_METHOD',
        'REQUEST_URI',
        'QUERY_STRING',
        'SERVER_PROTOCOL',
        'HTTP_HOST',
        'HTTP_USER_AGENT',
        'HTTP_ACCEPT',
        'HTTP_ACCEPT_LANGUAGE',
        'HTTP_ACCEPT_ENCODING',
        'HTTP_CONNECTION',
        'HTTP_CACHE_CONTROL',
        'HTTP_UPGRADE_INSECURE_REQUESTS',
        'HTTP_DNT',
        'HTTP_SEC_FETCH_SITE',
        'HTTP_SEC_FETCH_MODE',
        'HTTP_SEC_CH_UA',
        'HTTP_SEC_CH_UA_MOBILE',
        'HTTP_SEC_CH_UA_PLATFORM',
    ];
}