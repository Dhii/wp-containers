<?php declare(strict_types = 1);

namespace Dhii\Wp\Containers\FuncTest;

use Brain\Monkey\Functions;
use Dhii\Wp\Containers\Sites as TestSubject;
use Dhii\Wp\Containers\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

class SitesTest extends TestCase
{

    use ComponentMockeryTrait;

    protected function setUp()
    {
        parent::setUp();
        setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        tearDown();
    }

    /**
     * @param array $dependencies
     * @return MockObject|TestSubject
     * @throws Exception
     */
    protected function createSubject(array $dependencies, $methods = null)
    {
        return $this->createMockBuilder(TestSubject::class, $methods, $dependencies)
            ->getMock();
    }

    /**
     * Tests that entries are correctly returned if they exist.
     *
     * @throws Exception If problem testing.
     */
    public function testGet()
    {
        {
            $siteId = rand(1, 9);
            $site = $this->getMockBuilder('WP_Site')
                ->getMock();
            $site->siteId = $siteId;
            $subject = $this->createSubject([]);
            $fnGetSites = Functions\expect('get_site');
            $fnGetSites->times(1)
                ->with($siteId)
                ->andReturn($site);
        }

        {
            $result = $subject->get($siteId);
        }

        {
            $this->assertSame($site, $result, 'Wrong site retrieved');
        }
    }

    /**
     * Tests that the correct exception is thrown if entry doesn't exist.
     *
     * @throws Exception If problem testing.
     */
    public function testGetNotFound()
    {
        {
            $siteId = rand(1, 9);
            $subject = $this->createSubject([]);
            $fnGetSites = Functions\expect('get_site');
            $fnGetSites->times(1)
                ->with($siteId)
                ->andReturn(false);
        }

        {
            $this->expectException('Psr\Container\NotFoundExceptionInterface');
            $subject->get($siteId);
        }
    }

    /**
     * Tests that absence is correctly determined.
     *
     * @throws Exception If problem testing.
     */
    public function testHasFalse()
    {
        {
            $siteId = rand(1, 9);
            $subject = $this->createSubject([], ['get']);

            $subject->expects($this->exactly(1))
                ->method('get')
                ->with($siteId)
                ->willThrowException(
                    new class (sprintf('Could not find site #%1$d', $siteId))
                        extends Exception
                        implements NotFoundExceptionInterface {

});
        }

        {
            $result = $subject->has($siteId);
        }

        {
            $this->assertFalse($result, 'Wrongly determined having');
        }
    }

    /**
     * Tests that presence is correctly determined.
     *
     * @throws Exception If problem testing.
     */
    public function testHasTrue()
    {
        {
            $siteId = rand(1, 9);
            $site = $this->getMockBuilder('WP_Site')
                ->getMock();
            $site->siteId = $siteId;
            $subject = $this->createSubject([], ['get']);

            $subject->expects($this->exactly(1))
                ->method('get')
                ->with($siteId)
                ->willReturn($site);
        }

        {
            $result = $subject->has($siteId);
        }

        {
            $this->assertTrue($result, 'Wrongly determined not having');
        }
    }

}
