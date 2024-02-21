<?php

namespace Starfruit\EcommerceBundle\Event\Model;

use Pimcore\Event\Traits\ArgumentsAwareTrait;
use Pimcore\Model\DataObject\AbstractObject;
use Symfony\Contracts\EventDispatcher\Event;
use Pimcore\Event\Model\ElementEventInterface;
use Symfony\Component\HttpFoundation\Request;

class CartEvent extends Event implements ElementEventInterface
{
    use ArgumentsAwareTrait;

    /**
     * Request.
     * 
     * @var Request
     */
    protected $request;

    public function __construct($request = null)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return AbstractObject
     */
    public function getElement(): \Pimcore\Model\Element\ElementInterface
    {
        return null;
    }
}
