<?php

namespace App\libs\App;

use BaseProject\Admin\Helper\Parameter;
use PHPMailer\PHPMailer\PHPMailer;

class BaseProjectMailer extends PHPMailer
{

    /**
     * BaseProjectMailer constructor.
     * @inheritdoc
     */
    public function __construct($exceptions = null)
    {
        parent::__construct($exceptions);
        /** @var Parameter $parameters */
        $parameters = Helper::getInstance('Admin_Parameter');

        $this->isSMTP();
        $this->SMTPAuth = true;
        $this->Host = $parameters->getParameter('configEmail/connection/hosts')->getValue();
        $this->Port = $parameters->getParameter('configEmail/connection/port')->getValue();

        if ($replyTo = $parameters->getParameter('configEmail/body/replyTo')->getValue()) {
            $this->addReplyTo($replyTo);
        }

        if ($from = $parameters->getParameter('configEmail/body/from')->getValue()) {
            if ($displayName = $parameters->getParameter('configEmail/body/displayName')->getValue()) {
                $this->setFrom($from, $displayName);
            } else {
                $this->setFrom($from);
            }
        }

        $this->Username = $parameters->getParameter('configEmail/connection/username')->getValue();
        $this->Password = $parameters->getParameter('configEmail/connection/password')->getValue();
        $this->SMTPSecure = $parameters->getParameter('configEmail/connection/smtpSecure')->getValue();
    }

    /**
     * @param string $name
     * @param array $params
     * @return BaseProjectMailer
     */
    public function setCmsTemplate($name, $params = [])
    {
        $cmsBlock = Block::getBlock('Cms_Block');
        $cmsBlock->setName($name);
        $this->setTemplate($cmsBlock, $params);
        return $this;
    }

    /**
     * @param Block $block
     * @param array $params
     * @return BaseProjectMailer
     */
    public function setTemplate(&$block, $params = [])
    {
        $this->isHTML(true);
        $this->Body = $block->getHtml();
        if (count($params) > 0) {
            $this->replaceBodyParams($params);
        }
        return $this;
    }

    /**
     * @param $params
     */
    private function replaceBodyParams($params)
    {
        $body = $this->Body;
        foreach ($params as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }
        $this->Body = $body;
    }
}