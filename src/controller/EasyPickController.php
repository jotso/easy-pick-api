<?php

namespace Controller;

use GuzzleHttp\Client;
use Lib\Log;
use Lib\Mail;
use Lib\Utils;
use MiddleWare\EnvironmentMiddleWare;
use Model\Validator;
use Model\EasyPick;
use Slim\Container;

class EasyPickController
{
    private $_container;
    private $_easyPickModel;
    /**
     * @var array [field => validator to use]
     */
    private $_requiredFields = [
        'code' => 'code',
        'naam' => 'naam',
        'email' => 'email',
        'avw' => 'avw',
    ];

    public function __construct(Container $container)
    {
        $this->_container = $container;
        $this->_easyPickModel = new EasyPick($container);
    }

    public function initAction()
    {
        $dto = new Dto();
        $dto->setAnswer(Dto::STATUS_OK);
        $env = $this->_container->get('env');
        if ($env === EnvironmentMiddleWare::ENV_LIVE) {
            $prelaunch = $this->_easyPickModel->isPreLaunch();
            $offline = $this->_easyPickModel->isOffline();

            if ($prelaunch) {
                $dto->setAnswer(Dto::STATUS_WARNING);
                $dto->addData('haltmessage', $this->_easyPickModel->getTextByDesctiption('Prelaunch message'));
            }
            if ($offline) {
                $dto->setAnswer(Dto::STATUS_WARNING);
                $dto->addData('haltmessage', $this->_easyPickModel->getTextByDesctiption('Offline message'));
            }
        }
        return $dto->getData();
    }

    public function checkFieldAction($params)
    {
        $dto = new Dto();
        $dto->setAnswer(Dto::STATUS_OK);
        $field = $params['field'];
        if (!Validator::validatorExists($field)) {
            return $dto->addError("Not possible to validate field: $field")
                ->getData();
        }
        $value = $params[$field];
        $validator = new Validator($this->_container);
        $valid = $validator->$field($value);
        if (!$valid) {
            $dto->setAnswer(Dto::STATUS_ERROR);
            return array_merge($dto->getData(), $validator->getMessages());
        }
        return $dto->getData();
    }

    public function checkCodeAction($code)
    {
        $dto = new Dto();
        $dto->setAnswer(Dto::STATUS_OK);
        $validator = new Validator($this->_container);
        $valid = $validator->code($code);
        if (!$valid) {
            $dto->setAnswer(Dto::STATUS_ERROR);
            return array_merge($dto->getData(), $validator->getMessages());
        }
        return $dto->getData();
    }

    public function subNawAction($params)
    {
        $dto = new Dto();
        $validator = new Validator($this->_container);
        $requiredFields = $this->_getRequiredFields();
        $valid = $validator->checkRequiredFields($params, $requiredFields);
        if (!$valid) {
            $dto->setAnswer(Dto::STATUS_ERROR);
            return array_merge($dto->getData(), $validator->getMessages());
        }
        $this->_setCodeUsed($params);
        $user = [];
        $user['naam'] = $params['naam'];
        $user['datum'] = 'now()';
        $user['tijd'] = 'now()';
        $user['email'] = $params['email'];
        $user['optin'] = $params['optin'];
        if (null !== Utils::arrayValue('optin2', $params)) {
            $user['optin2'] = $params['optin2'];
        }
        $user['serial'] = $this->_generateID($params['email']);
        $user['code'] = $params['code'];
        $id = $this->_easyPickModel->insertUser($user);
        $templateParams = [
            'fullname' => $params['naam'],
            'url' => $this->_container['origin'],
        ];
        $mailTemplate = Mail::MAIL_TEMPLATE_CONFIRMATION;
        if (null !== Utils::arrayValue('mailtemplate', $params)) {
            $mailTemplate = $params['mailtemplate'];
        }
        try {
            $mail = $this->_container->get('mail');
            $content = $mail->fetchMailTemplateContent($mailTemplate);
            $sent = $mail->send($params['email'], $content, $templateParams);
            Log::info("Sent email to: " . $params['email']);
        } catch (\phpmailerException $e) {
            Log::critical(
                sprintf("phpmailerException: %s", $e->errorMessage())
            );
            return $dto->addError($e->errorMessage())->getData();
        } catch (\Exception $e) {
            Log::critical(
                sprintf("Exception: %s", $e->getMessage())
            );
            return $dto->addError($e->getMessage())->getData();
        }
        return $dto->setAnswer(Dto::STATUS_OK)->getData();
    }

    public function addToMailCamp($params)
    {
        $client = new Client();
        $formId = $params['formid'];
        $uri = $this->_container->get('settings')['mailcamp_uri'] . "?form=$formId";

        $formParams = [];
        foreach ($params as $k => $v) {
            if (is_numeric($k)) {
                $formParams["CustomFields[$k]"] = $v;
            }
        }
        $formParams['email'] = $params['email'];

        $response = $client->request('POST', $uri, [
            'form_params' => $formParams
        ]);
        return (new Dto())->setAnswer(Dto::STATUS_OK)->addData('response', (string)$response->getBody())->getData();
    }

    private function _generateID($email)
    {
        $id = random_bytes(64);
        return hash('sha256', $id . $email);
    }

    /**
     * Set the code to used, unless play_by_email is enabled.
     *
     * @param $params
     * @throws \Interop\Container\Exception\ContainerException
     */
    private function _setCodeUsed($params)
    {
        $projectConfig = $this->_container->get('project_config');
        if (null !== Utils::arrayValue('play_by_email', $projectConfig) && $projectConfig['play_by_email'] === true) {
            return;
        }
        if ($this->_container->get('env') === EnvironmentMiddleWare::ENV_LIVE) {
            $this->_easyPickModel->setCodeUsed($params['code']);
        }
    }

    private function _getRequiredFields()
    {
        $projectConfig = $this->_container->get('project_config');
        if (null === Utils::arrayValue('required_fields', $projectConfig)) {
            return $this->_requiredFields;
        }
        return $projectConfig['required_fields'];
    }
}
