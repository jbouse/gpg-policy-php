<?php

namespace App;

use Bref\SymfonyBridge\BrefKernel as BaseKernel;
// use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
