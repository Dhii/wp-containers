<?php declare(strict_types = 1);

namespace Dhii\Wp\Containers\Options;

use Dhii\Wp\Containers\Exception\ContainerException;
use Psr\Container\NotFoundExceptionInterface;
use Dhii\Wp\Containers\Util\StringTranslatingTrait;
use Exception;
use Psr\Container\ContainerInterface;
use Throwable;
use WP_Site; // Counting on this being there when in WordPress

/**
 * Creates and returns option containers for sites.
 *
 * @package Dhii\Wp\Containers
 */
class BlogOptionsContainer implements ContainerInterface
{

    use StringTranslatingTrait;

    /**
     * @var callable
     */
    protected $optionsFactory;
    /**
     * @var ContainerInterface
     */
    protected $sitesContainer;

    /**
     * @param callable $optionsFactory A callable with the following signature:
     * `function (int $id): ContainerInterface`
     * Accepts a site ID, and returns a container with options for that site.
     */
    public function __construct(
        callable $optionsFactory,
        ContainerInterface $sitesContainer
    ) {
        $this->optionsFactory = $optionsFactory;
        $this->sitesContainer = $sitesContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $site = $this->_getSite($id);
        $id = (int) $site->blog_id;

        try {
            $options = $this->_createOptions($id);
        } catch (Exception $e) {
            throw new ContainerException(
                $this->__('Could not get options for site #%1$d', [$id]),
                0,
                $e,
                $this
            );
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        try {
            $this->_getSite($id);
        } catch (NotFoundExceptionInterface $e) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve a site instance for the specified ID.
     *
     * @param int|string $id The ID of the site to retrieve.
     * @return WP_Site The site instance.
     * @throws NotFoundExceptionInterface If problem retrieving.
     * @throws Exception If problem retrieving.
     * @throws Throwable If problem running.
     */
    protected function _getSite($id): WP_Site
    {
        $site = $this->sitesContainer->get($id);

        return $site;
    }

    /**
     * Creates a container that represents options for a specific site.
     *
     * @param int $siteId The ID of the site to get the options for.
     * @return ContainerInterface The options.
     * @throws ContainerException If problem
     */
    protected function _createOptions(int $siteId): ContainerInterface
    {
        $factory = $this->optionsFactory;

        if (!is_callable($factory)) {
            throw new Exception(
                $this->__('Could not invoke options factory'),
                null,
                null
            );
        }

        $options = $factory($siteId);

        return $options;
    }

}