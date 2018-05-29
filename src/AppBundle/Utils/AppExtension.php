<?php

namespace AppBundle\Utils;

use Twig\Extension\AbstractExtension;
use Twig_Extension_GlobalsInterface;

class AppExtension extends AbstractExtension implements Twig_Extension_GlobalsInterface
{
    /**
     * @var string
     */
    private $repository;

    /**
     * @var string
     */
    private $version;

    /**
     * AppExtension constructor.
     *
     * @param string $repository
     * @param string $version
     */
    public function __construct(string $repository, string $version)
    {
        $this->version = $version;
        $this->repository = $repository;
    }

    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals()
    {
        return [
            'app_repository_link' => $this->repository,
            'app_version' => $this->version,
            'app_version_link' => sprintf('%s/tree/%s', $this->repository, $this->version),
        ];
    }
}
