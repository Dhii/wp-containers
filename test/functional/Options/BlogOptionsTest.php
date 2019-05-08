<?php

namespace Dhii\Wp\Containers\FuncTest\Options;

use Dhii\Data\Container\Exception\NotFoundExceptionInterface;
use Dhii\Wp\Containers\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Dhii\Wp\Containers\Options\BlogOptions as TestSubject;
use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;
use Dhii\Data\Container\Exception\ContainerExceptionInterface;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

class BlogOptionsTest extends TestCase
{

    use ComponentMockeryTrait;

    protected function setUp(): void
    {
        parent::setUp();
        setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        tearDown();
    }

    /**
     * Creates a new instance of the test subject.
     *
     * @param array $dependencies A list of constructor args.
     * @param array|null $methods The names of methods to mock in the subject.
     * @return MockObject|TestSubject The new instance.
     * @throws Exception If problem creating.
     */
    protected function createSubject(array $dependencies, ?array $methods = null)
    {
        return $this->createMockBuilder(TestSubject::class, $methods, $dependencies)
            ->getMock();
    }

    /**
     * Tests whether the container correctly determines having an item.
     *
     * @throws Exception If problem testing.
     */
    public function testHasTrue()
    {
        {
            $blogId = rand(1, 99);
            $optionName = uniqid('option-name');
            $optionValue = uniqid('option-value');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$blogId, $default],
                null
            );
            $fnGetBlogOption = Functions\expect('get_blog_option')
                ->times(1)
                ->with($blogId, $optionName, $default)
                ->andReturn($optionValue);
        }

        {
            $result = $subject->has($optionName);
        }

        {
            $this->assertTrue($result, 'Incorrectly determined not having');
        }
    }

    /**
     * Tests whether the container correctly determines not having an item.
     *
     * @throws Exception If problem testing.
     */
    public function testHasFalse()
    {
        {
            $blogId = rand(1, 99);
            $optionName = uniqid('option-name');
            $default = uniqid('default-value');
            $subject = $this->createSubject(
                [$blogId, $default],
                null
            );
            $fnGetBlogOption = Functions\expect('get_blog_option')
                ->times(1)
                ->with($blogId, $optionName, $default)
                ->andReturn($default);
        }

        {
            $result = $subject->has($optionName);
        }

        {
            $this->assertFalse($result, 'Incorrectly determined having');
        }
    }

    public function optionValuesProvider()
    {
        $array = $this->createArray(rand(1, 9), function (int $index) {
            return uniqid(sprintf('element%1$d', $index));
        });
        $object = (object) $this->createArray(
            rand(1, 9),
            function (int $index) {
                return uniqid(sprintf('element-%1$d-', $index));
            },
            function (int $index) {
                return uniqid(sprintf('key-%1$d-', $index));
            }
        );
        return [
            [uniqid('option-value')],
            [rand(0, 99)],
            [$array],
            [$object]
        ];
    }

    /**
     * Tests that the subject will correctly return the existing values.
     *
     * @dataProvider optionValuesProvider
     *
     * @param mixed $value The value to test for.
     *
     * @throws Exception If problem testing.
     */
    public function testGet($optionValue)
    {
        {
            $blogId = rand(1, 99);
            $optionName = uniqid('option-name');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$blogId, $default],
                null
            );
            $fnGetBlogOption = Functions\expect('get_blog_option')
                ->times(1)
                ->with($blogId, $optionName, $default)
                ->andReturn($optionValue);
        }

        {
            $result = $subject->get($optionName);
        }

        {
            $this->assertEquals($optionValue, $result, 'Incorrectly retrieved result');
        }
    }

    /**
     * Tests that the subject throws correctly when trying to get a non-existing key.
     *
     * @throws Exception If problem testing.
     */
    public function testGetNotFound()
    {
        {
            $blogId = rand(1, 99);
            $optionName = uniqid('option-name');
            $optionValue = uniqid('option-value');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$blogId, $default],
                null
            );
            $fnGetBlogOption = Functions\expect('get_blog_option')
                ->times(1)
                ->with($blogId, $optionName, $default)
                ->andReturn($default);
            $this->expectException(NotFoundExceptionInterface::class);
        }

        {
            $result = $subject->get($optionName);
        }

        {
            $this->assertEquals($optionValue, $result, 'Incorrectly retrieved result');
        }
    }

    /**
     * Tests that values can be set correctly.
     *
     * @dataProvider optionValuesProvider
     * @doesNotPerformAssertions
     *
     * @param mixed $optionValue The option value to test.
     *
     * @throws Exception If problem testing.
     */
    public function testSet($optionValue)
    {
        {
            $blogId = rand(1, 99);
            $optionName = uniqid('option-name');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$blogId, $default],
                null
            );
            $fnUpdateBlogOption = Functions\expect('update_blog_option')
                ->times(1)
                ->with($blogId, $optionName, $optionValue)
                ->andReturn(true);
        }

        {
            $subject->set($optionName, $optionValue);
        }

        {
            // No exception means success
        }
    }

    /**
     * Tests that values can be set correctly when the set value is the same as already existing.
     *
     * @dataProvider optionValuesProvider
     * @doesNotPerformAssertions
     *
     * @param mixed $optionValue The option value to test.
     *
     * @throws Exception If problem testing.
     */
    public function testSetSame($optionValue)
    {
        {
            $blogId = rand(1, 99);
            $optionName = uniqid('option-name');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$blogId, $default],
                null
            );
            $fnUpdateBlogOption = Functions\expect('update_blog_option')
                ->times(1)
                ->with($blogId, $optionName, $optionValue)
                ->andReturn(false);
            $fnGetBlogOption = Functions\expect('get_blog_option')
                ->times(1)
                ->with($blogId, $optionName, $default)
                ->andReturn($optionValue);
        }

        {
            $subject->set($optionName, $optionValue);
        }

        {
            // No exception means success
        }
    }

    /**
     * Tests that the correct exception is thrown when a value cannot be set.
     *
     * @throws Exception If problem testing.
     */
    public function testSetFailure()
    {
        {
            $blogId = rand(1, 99);
            $optionName = uniqid('option-name');
            $optionValue = uniqid('option-value');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$blogId, $default],
                null
            );
            $fnUpdateBlogOption = Functions\expect('update_blog_option')
                ->times(1)
                ->with($blogId, $optionName, $optionValue)
                ->andReturn(false);
            $fnGetBlogOption = Functions\expect('get_blog_option')
                ->times(1)
                ->with($blogId, $optionName, $default)
                ->andReturn(uniqid('different-value'));
            $this->expectException(ContainerExceptionInterface::class);
        }

        {
            $subject->set($optionName, $optionValue);
        }

        {
            // No exception means failure
        }
    }
}
