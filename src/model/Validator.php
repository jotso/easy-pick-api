<?php

namespace Model;

use Lib\Log;
use Lib\Utils;
use Monolog\Handler\Curl\Util;
use Slim\Container;

class Validator
{
    private $_messages;
    private $_container;
    private $_easyPick;

    public function __construct(Container $container)
    {
        $this->_messages = [];
        $this->_container = $container;
        $this->_easyPick = $container->get('model_easy_pick');
    }

    public static function validatorExists(string $validator)
    {
        return is_callable(array(self::class, $validator));
    }

    public function checkRequiredFields($params, $fields)
    {
        foreach ($fields as $field => $validator) {
            $val = Utils::arrayValue($field, $params);
            switch ($validator) {
                case 'code':
                    $this->code($val);
                    break;
                case 'naam':
                    $this->naam($val);
                    break;
                case 'email':
                    $emailRep = Utils::arrayValue('emailrep', $params);
                    $this->email($val, $emailRep);
                    break;
                case 'avw':
                    if ($val === null || (int) $val == 0) {
                        $this->_addMessage('avw', 'Je bent niet akkoord gegaan met de actievoorwaarden');
                    }
                    break;
            }
        }
        if (array_key_exists('messages', $this->_messages)) {
            return false;
        }
        return true;
    }

    public function code($code, $length = 6)
    {
        $code = $this->_formatString($code, true);
        if (strlen($code) == 0) {
            $this->_addMessage('code', 'Vul een geldige code in');
            return false;
        }
        if (strlen($code) < $length) {
            $this->_addMessage('code', 'Deze code is niet compleet');
            return false;
        }
        $used = $this->_easyPick->getCodeUsed($code);
        if ($used === NULL) {
            $this->_addMessage('code', 'Deze code is ongeldig');
            return false;
        }
        if (intval($used) > 0) {
            $this->_addMessage('code', 'Deze code is reeds verzilverd');
            return false;
        }
        return true;
    }

    public function naam($val = null)
    {
        if ($this->_hasNumber($val)) {
            $this->_addMessage('naam', "Naam mag geen nummers bevatten");
            return false;
        }
        $parts = explode(' ', $val);
        if (count($parts) < 2) {
            $this->_addMessage('naam', 'Vul je volledige naam in');
            return false;
        } else if (strlen($parts[0]) < 2 || strlen($parts[1]) < 2) {
            $this->_addMessage('naam', 'Vul je volledige naam in');
            return false;
        }
        return true;
    }

    public function email($email, $emailrep = null)
    {
        strtolower($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->_addMessage('email', 'Vul je e-mailadres in');
            return false;
        } else {
            if (null !== $emailrep) {
                $emailrep = trim($emailrep);
                if (strtolower($emailrep) != strtolower($email)) {
                    $this->_addMessage('email', 'Het e-mailadres komt niet overeen');
                    return false;
                }
            }
        }
        return $this->playByEmail($email);
    }

    /**
     * If play_by_email setting is enabled, we will check if email is used already.
     *
     * @param $email
     * @param $maxTries
     * @return bool
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function playByEmail($email, $maxTries = 1)
    {
        $projectConfig = $this->_container->get('project_config');

        if (null === Utils::arrayValue('play_by_email', $projectConfig) || $projectConfig['play_by_email'] === false) {
            return true;
        }
        $user = $this->_easyPick->getUsersByEmail($email);
        if ($user === null) {
            return true;
        }
        if (null !== Utils::arrayValue('play_by_email_max_tries', $projectConfig)) {
            $maxTries = $projectConfig['play_by_email_max_tries'];
        }
        if (count($user) >= $maxTries) {
            $this->_addMessage('email', "Je hebt al $maxTries keer meegedaan");
            return false;
        }
        return true;
    }

    public function getMessages()
    {
        return $this->_messages;
    }

    private function _addMessage($field, $message)
    {
        $this->_messages['messages'][] = [
            'field' => $field,
            'message' => $message,
        ];
    }

    private function _formatString($str, $upper = false)
    {
        if ($upper) {
            $str = strtoupper($str);
        }
        $str = str_replace('-', '', $str);
        $str = str_replace(' ', '', $str);
        return $str;
    }

    private function _hasNumber($s)
    {
        if (preg_match('#[0-9]#', $s)) {
            return true;
        }
        return false;
    }
}