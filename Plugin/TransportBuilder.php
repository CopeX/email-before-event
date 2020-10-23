<?php

namespace CopeX\EmailBeforeEvent\Plugin;

use Magento\Framework\DataObject\Factory;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\Mail\Template\TransportBuilder as TransportBuilderClass;

class TransportBuilder
{
    protected $templateVars;
    protected $templateIdentifier;
    protected $templateOptions;

    /**
     * @var EventManager
     */
    private $eventManager;
    /**
     * @var Factory
     */
    private $dataObject;

    public function __construct(EventManager $eventManager, Factory $dataObject)
    {
        $this->eventManager = $eventManager;
        $this->dataObject = $dataObject;
    }

    public function beforeSetTemplateIdentifier(
        TransportBuilderClass $subject,
        $templateIdentifier
    ) {
        $this->templateIdentifier = $templateIdentifier;
    }

    public function beforeSetTemplateVars(
        TransportBuilderClass $subject,
        $templateVars
    ) {
        $this->templateVars = $templateVars;
    }

    public function beforeSetTemplateOptions(
        TransportBuilderClass $subject,
        $templateOptions
    ) {
        $this->templateOptions = $templateOptions;
    }

    /**
     * @param TransportBuilderClass $subject
     */
    public function beforeGetTransport(TransportBuilderClass $subject)
    {
        $templateOptions = $this->dataObject->create($this->templateOptions);
        $templateVars = $this->dataObject->create($this->templateVars);
        $identifier = $this->dataObject->create(["identifier" => $this->templateIdentifier]);
        $params = [
                   "builder"             => $subject,
                   "template_options"    => $templateOptions,
                   "template_identifier" => $identifier,
                   "template_vars"       => $templateVars,
        ];
        $this->eventManager->dispatch('email_get_transport_before', $params);
        $this->eventManager->dispatch('email_get_transport_before_' . $identifier->getIdentifier(), $params);
        $subject->setTemplateOptions($templateOptions->getData());
        $subject->setTemplateIdentifier($identifier->getIdentifier());
        $subject->setTemplateVars($templateVars->getData());
    }

}
