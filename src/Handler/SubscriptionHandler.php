<?php
namespace App\Handler;

use App\Entity\Subscription;
use App\Form\SubscriptionType;
use DateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionHandler
{
    use HandlerTrait;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Process the request.
     *
     * @param Request $request The request.
     *
     * @return bool Whether the request where to process and did so successfully.
     */
    public function processRequest(Request $request): bool
    {
        $subscription = new Subscription();
        $subscription->setCreatedAt(new DateTime());

        $this->form = $this->createForm(SubscriptionType::class, $subscription);

        $this->form->handleRequest($request);
        if ($this->form->isSubmitted()) {
            if ($this->form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $subscriptionRepo = $em->getRepository(Subscription::class);
                $existingSubscription = $subscriptionRepo->findOneBy([
                    'email' => $subscription->getEmail(),
                ]);
                if (!$existingSubscription) {
                    $em->persist($subscription);

                    $em->flush();
                }

                $this->addFlash('success', 'You are now subscribed ! Thank you !');

                return true;
            }
        }

        return false;
    }

    public function getViewParameters(): array
    {
        return [
            'formSubscription' => ($this->form ? $this->form->createView() : null),
        ];
    }
}
