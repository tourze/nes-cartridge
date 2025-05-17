<?php

namespace Tourze\NES\Cartridge\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\NES\Cartridge\Exception\CartridgeException;
use Tourze\NES\Cartridge\Exception\InvalidHeaderException;

/**
 * 测试无效头信息异常类
 */
class InvalidHeaderExceptionTest extends TestCase
{
    /**
     * 测试异常可以正确创建并继承自CartridgeException
     */
    public function testInvalidHeaderExceptionInheritsFromCartridgeException(): void
    {
        $exception = new InvalidHeaderException('无效的头信息');

        $this->assertInstanceOf(CartridgeException::class, $exception);
        $this->assertSame('无效的头信息', $exception->getMessage());
    }
}
