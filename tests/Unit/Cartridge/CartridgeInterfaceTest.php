<?php

namespace Tourze\NES\Cartridge\Tests\Unit\Cartridge;

use PHPUnit\Framework\TestCase;
use Tourze\NES\Cartridge\Cartridge\CartridgeInterface;

/**
 * 测试卡带接口
 */
class CartridgeInterfaceTest extends TestCase
{
    /**
     * 测试接口定义了所需的所有方法
     */
    public function testInterfaceDefinesAllRequiredMethods(): void
    {
        $methods = [
            'cpuRead',
            'cpuWrite',
            'ppuRead',
            'ppuWrite',
            'reset',
            'getMapperType',
            'getPrgRomData',
            'getChrRomData',
        ];

        $reflection = new \ReflectionClass(CartridgeInterface::class);

        foreach ($methods as $method) {
            $this->assertTrue(
                $reflection->hasMethod($method),
                "接口应该定义{$method}方法"
            );
        }
    }

    /**
     * 测试方法签名正确
     */
    public function testMethodSignaturesAreCorrect(): void
    {
        $reflection = new \ReflectionClass(CartridgeInterface::class);

        // 测试CPU读取方法
        $method = $reflection->getMethod('cpuRead');
        $this->assertSame(1, $method->getNumberOfParameters());
        $this->assertSame('address', $method->getParameters()[0]->getName());
        $this->assertSame('int', $method->getReturnType()->getName());

        // 测试CPU写入方法
        $method = $reflection->getMethod('cpuWrite');
        $this->assertSame(2, $method->getNumberOfParameters());
        $this->assertSame('address', $method->getParameters()[0]->getName());
        $this->assertSame('data', $method->getParameters()[1]->getName());
        $this->assertSame('void', $method->getReturnType()->getName());

        // 测试PPU读取方法
        $method = $reflection->getMethod('ppuRead');
        $this->assertSame(1, $method->getNumberOfParameters());
        $this->assertSame('address', $method->getParameters()[0]->getName());
        $this->assertSame('int', $method->getReturnType()->getName());

        // 测试PPU写入方法
        $method = $reflection->getMethod('ppuWrite');
        $this->assertSame(2, $method->getNumberOfParameters());
        $this->assertSame('address', $method->getParameters()[0]->getName());
        $this->assertSame('data', $method->getParameters()[1]->getName());
        $this->assertSame('void', $method->getReturnType()->getName());

        // 测试reset方法
        $method = $reflection->getMethod('reset');
        $this->assertSame(0, $method->getNumberOfParameters());
        $this->assertSame('void', $method->getReturnType()->getName());

        // 测试getMapperType方法
        $method = $reflection->getMethod('getMapperType');
        $this->assertSame(0, $method->getNumberOfParameters());
        $this->assertSame('int', $method->getReturnType()->getName());

        // 测试getPrgRomData方法
        $method = $reflection->getMethod('getPrgRomData');
        $this->assertSame(0, $method->getNumberOfParameters());
        $this->assertSame('string', $method->getReturnType()->getName());

        // 测试getChrRomData方法
        $method = $reflection->getMethod('getChrRomData');
        $this->assertSame(0, $method->getNumberOfParameters());
        $this->assertSame('string', $method->getReturnType()->getName());
    }
}
