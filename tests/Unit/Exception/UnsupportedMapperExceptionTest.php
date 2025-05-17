<?php

namespace Tourze\NES\Cartridge\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\NES\Cartridge\Exception\CartridgeException;
use Tourze\NES\Cartridge\Exception\UnsupportedMapperException;

/**
 * 测试不支持的映射器异常类
 */
class UnsupportedMapperExceptionTest extends TestCase
{
    /**
     * 测试异常可以正确创建并继承自CartridgeException
     */
    public function testUnsupportedMapperExceptionInheritsFromCartridgeException(): void
    {
        $exception = new UnsupportedMapperException('不支持的映射器类型: 255');

        $this->assertInstanceOf(CartridgeException::class, $exception);
        $this->assertSame('不支持的映射器类型: 255', $exception->getMessage());
    }
}
