<?php

namespace Lib;

use Exception\EasyPickException;
use MiddleWare\EnvironmentMiddleWare;
use PHPMailer\PHPMailer\PHPMailer;
use Slim\Container;

class Mail
{
    const MAIL_TEMPLATE_CONFIRMATION = 'confirmation.twig';

    private $_container;

    public function __construct(Container $container)
    {
        $this->_container = $container;
    }

    public function send($mailTo, $templateStr, $templateParams = [])
    {
        $config = $this->_container->get('project_config');
        $mailFrom = $config['mail_from'];
        $mailFromName = $config['mail_name'];
        $mailSubject = $config['mail_subject'];

        $msgHTML = $this->_container->get('view')->fetchFromString($templateStr, $templateParams);

        $mail = new PHPMailer(true);
        $mailResult = false;
        $mail->CharSet = 'utf-8';
        $mail->SMTPAuth = false;
        $mail->AddAddress($mailTo);
        $mail->SetFrom($mailFrom, $mailFromName);
        $mail->Subject = $mailSubject;
        $mail->MsgHTML($msgHTML, dirname(__FILE__));
        $mail->IsHTML(true);
        $mailResult = $mail->Send();

        return $mailResult;
    }

    /**
     * Fetch the content of $template respectively over the file system, or over the internet.
     *
     * @param $template the template name
     * @return false|string
     * @throws EasyPickException
     */
    public function fetchMailTemplateContent($template)
    {
        $env = $this->_container->get('env');
        $domain = $this->_container->get('project_config')['domain'];
        $localPath = __DIR__ . "/../../../" . Utils::getEnvPrefix($env) . "$domain/mail/$template";
        $mailcontent = file_get_contents($localPath);
        if (false !== $mailcontent) {
            return $mailcontent;
        }
        Log::info("Failed to fetch template over file system: $localPath");
        $origin = $this->_container['origin'];
        $remotePath = "$origin/mail/$template";
        $context = stream_context_create(
            array(
                "http" => array(
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                )
            )
        );
        $mailcontent = file_get_contents($remotePath, false, $context);
        if (false !== $mailcontent) {
            return $mailcontent;
        }
        throw new EasyPickException("Unable to fetch mail content from: $remotePath, or: $localPath");
    }
}
