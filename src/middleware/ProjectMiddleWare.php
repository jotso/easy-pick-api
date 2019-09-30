<?php

namespace MiddleWare;

use Controller\Dto;
use Exception\EasyPickException;
use Lib\Log;
use Lib\Utils;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Yaml\Yaml;
use Exception\MiddleWareException;

class ProjectMiddleWare
{
    private $_container;
    private $_dto;

    public function __construct(Container $container)
    {
        $this->_container = $container;
        $this->_dto = new Dto();
    }

    /**
     * MiddleWare that will find and validate project configuration.
     *
     * @param $request
     * @param $response
     * @param $next
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $next)
    {

        if ($this->_canPass($request)) {
            $response = $next($request, $response);
            return $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        }
        try {
            $params = $request->getAttribute('route')->getArguments();
            $projectConfig = $this->_findProjectConfig($params);
            $this->_container['project_id'] = $params['projectId'];
            $this->_container['project_config'] = $projectConfig;
            $this->_checkMatchingDomains();

        } catch (\Exception $e) {
            $errorMsg = sprintf("Unable to handle request: %s", $e->getMessage());
            Log::critical($errorMsg);
            return $response->withStatus(400)
                ->withHeader('Content-Type', 'text/json')
                ->write($this->_dto->addError($errorMsg)->getJsonData());
        }
        $response = $next($request, $response);

        return $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');;
    }

    /**
     * Attempt to retrieve the project config based on the projectId in the request parameters.
     *
     * @param $params
     * @return mixed
     * @throws MiddleWareException
     */
    private function _findProjectConfig($params)
    {
        if (! array_key_exists('projectId', $params) || null === $params['projectId']) {
            throw new MiddleWareException("Missing projectId");
        }
        $projectId = $params['projectId'];

        $easyPickConfig = realpath(__DIR__ . '/../config/easy_pick.yaml');
        if (Utils::isPhpunit()) {
            $easyPickConfig = realpath(__DIR__ . '/../config/phpunit_test_easy_pick.yaml');
        }
        $projects = Yaml::parseFile($easyPickConfig);

        if (! array_key_exists($projectId, $projects['projects'])) {
            throw new MiddleWareException("Project configuration with projectId $projectId not found");
        }
        return $projects['projects'][$projectId];
    }

    /**
     * Will check if the domain in the origin header matches the domain configured in easy_pick.yaml.
     *
     * @throws MiddleWareException
     */
    private function _checkMatchingDomains()
    {
        $projectConfig = $this->_container['project_config'];

        if (! array_key_exists('domain', $projectConfig) || empty($projectConfig['domain'])) {
            throw new MiddleWareException('Project configuration error');
        }
        $host = parse_url($this->_container['origin'], PHP_URL_HOST);

        if (false !== strpos($host, EnvironmentMiddleWare::ENV_LOCAL, 0)) {
            return;
        }
        if (false !== strpos($host, 'www', 0)) {
            $host = substr_replace($host, "", 0, 4);
        }
        if (false !== strpos($host, EnvironmentMiddleWare::ENV_DEV, 0)) {
            $host = substr_replace($host, "", 0, strlen(EnvironmentMiddleWare::ENV_DEV) + 1);
        }
        if (false !== strpos($host, EnvironmentMiddleWare::ENV_STAGING, 0)) {
            $host = substr_replace($host, "", 0, strlen(EnvironmentMiddleWare::ENV_STAGING) + 1);
        }
        if (false !== strpos($host, EnvironmentMiddleWare::ENV_TEST, 0)) {
            $host = substr_replace($host, "", 0, strlen(EnvironmentMiddleWare::ENV_TEST) + 1);
        }
        if ($projectConfig['domain'] !== $host) {
            throw new MiddleWareException(
                sprintf("Not allowed to query domain %s from origin %s", $projectConfig['domain'], $host)
            );
        };
    }

    /**
     * Some requests can skip the ProjectMiddleWare routines.
     *
     * @param Request $request
     * @return bool
     */
    private function _canPass(Request $request) {
        // Catching the preflight request
        if (in_array('OPTIONS', $request->getAttribute('route')->getMethods())) {
            return true;
        }
        if (strpos($request->getUri()->getPath(), 'toegangskaarten') !== false) {
            return true;
        }
        return false;
    }
}
