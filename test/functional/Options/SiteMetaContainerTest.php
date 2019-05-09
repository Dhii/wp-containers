<?php

namespace Dhii\Wp\Containers\FuncTest\Options;

use Dhii\Data\Container\Exception\NotFoundExceptionInterface;
use Dhii\Wp\Containers\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Dhii\Wp\Containers\Options\SiteMetaContainer as TestSubject;
use PHPUnit\Framework\TestCase;

class SiteMetaContainerTest extends TestCase
{

    use ComponentMockeryTrait;

    /**
     * Creates a new instance of the test subject.
     *
     * @param array $dependencies A list of constructor args.
     * @param array|null $methods The names of methods to mock in the subject.
     *
     * @return MockObject|TestSubject The new instance.
     *
     * @throws Exception If problem creating.
     */
    protected function createSubject(array $dependencies, array $methods = null)
    {
        return $this->createMockBuilder(TestSubject::class, $methods, $dependencies)
            ->getMock();
    }

    /**
     * Creates a new WP Site mock.
     *
     * @param int $id The ID for the new site.
     *
     * @return MockObject|WP_Site The new site.
     *
     * @throws Exception If problem creating.
     */
    protected function createWpSite(int $id)
    {
        $mock = $this->createMockBuilder('WP_Site')
            ->getMock();
        $mock->blog_id = $id;

        return $mock;
    }

    /**
     * Tests that the subject can correctly determine having a key.
     *
     * @throws Exception If problem testing.
     */
    public function testHasTrue()
    {
        {
            $siteId = rand(1, 99);
            $site = $this->createWpSite($siteId);
            $factory = $this->createCallable(function () {});
            $sitesContainer = $this->createContainer([
                $siteId         => $site,
            ]);
            $subject = $this->createSubject([$factory, $sitesContainer]);
        }

        {
            $result = $subject->has($siteId);
        }

        {
            $this->assertTrue($result, 'Wrongly determined having');
        }
    }

    /**
     * Tests that the subject can correctly determine not having a key.
     *
     * @throws Exception If problem testing.
     */
    public function testHasFalse()
    {
        {
            $factory = $this->createCallable(function () {});
            $sitesContainer = $this->createContainer([]);
            $subject = $this->createSubject([$factory, $sitesContainer]);
        }

        {
            $result = $subject->has(uniqid('site-id'));
        }

        {
            $this->assertFalse($result, 'Wrongly determined not having');
        }
    }

    /**
     * Test that retrieving containers from the subject works as expected.
     *
     * @throws Exception If problem testing.
     */
    public function testGet()
    {
        {
            $siteId = rand(1, 99);
            $site = $this->createWpSite($siteId);
            $newContainer = $this->createWritableContainer([]);
            $factory = $this->createCallable(function () use ($newContainer) {
                return $newContainer;
            });
            $sitesContainer = $this->createContainer([
                $siteId         => $site,
            ]);
            $subject = $this->createSubject([$factory, $sitesContainer]);

            $factory->expects($this->exactly(1))
                ->method('__invoke')
                ->with($siteId)
                ->will($this->returnValue($newContainer));
        }

        {
            $result = $subject->get($siteId);
        }

        {
            $this->assertSame($newContainer, $result, 'Wrong container retrieved');
        }
    }

    /**
     * Test that retrieving a non-existing value from the subject throws as expected.
     *
     * @throws Exception If problem testing.
     */
    public function testGetNotFound()
    {
        {
            $factory = $this->createCallable(function () {});
            $sitesContainer = $this->createContainer([]);
            $subject = $this->createSubject([$factory, $sitesContainer]);
        }

        {
            $this->expectException(NotFoundExceptionInterface::class);
            $subject->get(rand(1, 99));
        }

        {
            // No exception means fail
        }
    }
}
