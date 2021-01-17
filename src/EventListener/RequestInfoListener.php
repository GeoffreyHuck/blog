<?php
namespace App\EventListener;

use App\Entity\RequestInfoInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestInfoListener
{
    /** @var Request */
    private $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getMasterRequest();
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        /** @var RequestInfoInterface $entity */
        $entity = $args->getEntity();

        if (!$entity instanceof RequestInfoInterface) {
            return;
        }

        $entity->setIp($this->request->getClientIp());
        $entity->setUserAgent($this->request->headers->get('User-Agent'));
        $entity->setReferer($this->request->headers->get('Referer'));
        $entity->setAcceptLanguage($this->request->headers->get('Accept-Language'));
    }
}
