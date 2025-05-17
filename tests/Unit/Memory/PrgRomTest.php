<?php

namespace Tourze\NES\Cartridge\Tests\Unit\Memory;

use PHPUnit\Framework\TestCase;
use Tourze\NES\Cartridge\Memory\PrgRom;

/**
 * 测试程序ROM管理类
 */
class PrgRomTest extends TestCase
{
    /**
     * 测试可以正确创建PrgRom实例
     */
    public function testCanCreatePrgRomInstance(): void
    {
        $data = str_repeat("\xAA", 32 * 1024); // 32KB的数据
        $prgRom = new PrgRom($data);

        $this->assertInstanceOf(PrgRom::class, $prgRom);
        $this->assertSame(32 * 1024, $prgRom->getSize());
        $this->assertSame($data, $prgRom->getData());
    }

    /**
     * 测试可以正确读取数据
     */
    public function testCanReadDataCorrectly(): void
    {
        $data = "ABCDEFGH"; // 简单的测试数据
        $prgRom = new PrgRom($data);

        $this->assertSame(ord('A'), $prgRom->read(0));
        $this->assertSame(ord('B'), $prgRom->read(1));
        $this->assertSame(ord('C'), $prgRom->read(2));
        $this->assertSame(ord('H'), $prgRom->read(7));
    }

    /**
     * 测试超出范围的地址会被镜像
     */
    public function testAddressOutOfRangeIsMirrored(): void
    {
        $data = "ABCD"; // 4字节数据
        $prgRom = new PrgRom($data);

        // 超出范围的地址会被镜像
        $this->assertSame(ord('A'), $prgRom->read(4)); // 4 % 4 = 0
        $this->assertSame(ord('B'), $prgRom->read(5)); // 5 % 4 = 1
        $this->assertSame(ord('C'), $prgRom->read(6)); // 6 % 4 = 2
        $this->assertSame(ord('D'), $prgRom->read(7)); // 7 % 4 = 3

        // 更大的地址也会被镜像
        $this->assertSame(ord('A'), $prgRom->read(8));  // 8 % 4 = 0
        $this->assertSame(ord('A'), $prgRom->read(12)); // 12 % 4 = 0
        $this->assertSame(ord('C'), $prgRom->read(10)); // 10 % 4 = 2

        // 负地址也会被镜像
        $this->assertSame(ord('A'), $prgRom->read(-4)); // -4 % 4 = 0
        $this->assertSame(ord('C'), $prgRom->read(-2)); // -2 % 4 = 2
    }

    /**
     * 测试可以处理空数据
     */
    public function testCanHandleEmptyData(): void
    {
        $prgRom = new PrgRom('');

        $this->assertSame(0, $prgRom->getSize());

        // 空数据时读取应返回0
        $this->assertSame(0, $prgRom->read(0));
        $this->assertSame(0, $prgRom->read(100));
    }
}
