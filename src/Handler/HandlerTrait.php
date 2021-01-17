<?php
namespace App\Handler;

use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use function get_class;

/**
 * Trait HandlerTrait
 */
trait HandlerTrait
{
    /** @var ContainerInterface */
    private $container;

    /** @var Form */
    private $form;

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type The type.
     * @param array|null $data The data.
     * @param array $options The options.
     *
     * @return FormInterface
     */
    protected function createForm(string $type, $data = null, array $options = []): FormInterface
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @throws LogicException If DoctrineBundle is not available
     */
    protected function getDoctrine(): ManagerRegistry
    {
        if (!$this->container->has('doctrine')) {
            throw new LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
        }

        return $this->container->get('doctrine');
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied subject.
     *
     * @param string|array $attributes The attribute(s).
     * @param mixed        $subject    The subject.
     *
     * @return bool
     * @throws LogicException
     */
    protected function isGranted($attributes, $subject = null): bool
    {
        if (!$this->container->has('security.authorization_checker')) {
            throw new LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }

        return $this->container->get('security.authorization_checker')->isGranted($attributes, $subject);
    }

    /**
     * Adds a flash message to the current session for type.
     *
     * @param string $type    The type.
     * @param string $message The message.
     *
     * @throws LogicException
     */
    protected function addFlash(string $type, string $message): void
    {
        if (!$this->container->has('session')) {
            throw new LogicException('You can not use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".');
        }

        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * Gets a container parameter by its name.
     *
     * @param string $name The name.
     *
     * @return mixed
     * @throws ServiceNotFoundException
     */
    protected function getParameter(string $name)
    {
        if (!$this->container->has('parameter_bag')) {
            throw new ServiceNotFoundException('parameter_bag', null, null, [], sprintf('The "%s::getParameter()" method is missing a parameter bag to work properly. Did you forget to register your controller as a service subscriber? This can be fixed either by using autoconfiguration or by manually wiring a "parameter_bag" in the service locator passed to the controller.', get_class($this)));
        }

        return $this->container->get('parameter_bag')->get($name);
    }

    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }
}
