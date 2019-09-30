<?php

namespace MiddleWare;

use Controller\Dto;
use Exception\MiddleWareException;
use Lib\Log;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class EnvironmentMiddleWare
{

    const ENV_LIVE = 'live';
    const ENV_TEST = 'test';
    const ENV_STAGING = 'staging';
    const ENV_DEV = 'dev';
    const ENV_LOCAL = 'localhost';
    const ENV_PHPUNIT = 'phpunit';

    private $_container;
    private $_dto;

    public function __construct(Container $container)
    {
        $this->_container = $container;
        $this->_dto = new Dto();
    }

    /**
     * MiddleWare that will try to retrieve the environment of the requesting party.
     *
     * @param $request
     * @param $response
     * @param $next
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        try {
            $origin = $this->_getOrigin($request);
            $this->_container['origin'] = $origin[0];
            $stage = $this->_getStage($origin);
            $this->_container['env'] = $stage;
        } catch (\Exception $e) {
            $errorMsg = sprintf("Unable to handle request: %s", $e->getMessage());
            Log::critical($errorMsg);
            return $response->withStatus(400)
                ->withHeader('Content-Type', 'text/json')
                ->write(
                    $this->_dto->addError($errorMsg)->getJsonData()
                );
        }
        $response = $next($request, $response);
        return $response;
    }

    private function _getOrigin(Request $request)
    {
        $origin = $request->getHeader('HTTP_ORIGIN');
        if (empty($origin)) {
            throw new MiddleWareException("Missing Origin header");
        }
        return $origin;
    }

    private function _getStage($origin)
    {
        $host = parse_url($origin[0], PHP_URL_HOST);
        $prefix = explode('.', $host)[0];
        if (in_array($prefix, [self::ENV_DEV, self::ENV_LOCAL])) {
            return self::ENV_DEV;
        }
        if (self::ENV_STAGING === $prefix) {
            return self::ENV_STAGING;
        }
        if (self::ENV_TEST === $prefix) {
            return self::ENV_TEST;
        }
        return self::ENV_LIVE;
    }
}