<?php declare(strict_types=1);

namespace SUP;

class Factory
{
    protected $container;

    public function __construct(\Slim\Container $container)
    {
        $this->container = $container;
    }

    public function userFromID(int $id)
    {
        $user = new User($this->container);
        return $user->createFromDB($id);
    }
}
