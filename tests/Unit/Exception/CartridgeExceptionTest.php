<?php

namespace Tourze\NES\Cartridge\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\NES\Cartridge\Exception\CartridgeException;

/**
 * 测试卡带异常基类
 */
class CartridgeExceptionTest extends TestCase
{
    /**
     * 测试异常可以正确创建并继承自Exception
     */
    public function testCartridgeExceptionInheritsFromException(): void
    {
        $exception = new CartridgeException('测试异常');

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame('测试异常', $exception->getMessage());
    }

    /**
     * 测试异常可以设置代码和上一个异常
     */
    public function testCartridgeExceptionCanSetCodeAndPrevious(): void
    {
        $previousException = new \RuntimeException('上一个异常');
        $exception = new CartridgeException('测试异常', 123, $previousException);

        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
