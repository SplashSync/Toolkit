<?php

namespace App\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * This file has been generated by the SonataEasyExtendsBundle.
 *
 * @link https://sonata-project.org/easy-extends
 *
 * References:
 * @link http://symfony.com/doc/current/book/bundles.html
 */
class AppUserBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'SonataUserBundle';
    }
}