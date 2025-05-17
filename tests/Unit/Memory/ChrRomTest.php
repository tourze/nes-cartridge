<?php

namespace Tourze\NES\Cartridge\Tests\Unit\Memory;

use PHPUnit\Framework\TestCase;
use Tourze\NES\Cartridge\Memory\ChrRom;

/**
 * 测试图形ROM管理类
 */
class ChrRomTest extends TestCase
{
    /**
     * 测试可以正确创建ChrRom实例
     */
    public function testCanCreateChrRomInstance(): void
    {
        $data = str_repeat("\xBB", 8 * 1024); // 8KB的数据
        $chrRom = new ChrRom($data);

        $this->assertInstanceOf(ChrRom::class, $chrRom);
        $this->assertSame(8 * 1024, $chrRom->getSize());
        $this->assertSame($data, $chrRom->getData());
    }

    /**
     * 测试可以正确读取数据
     */
    public function testCanReadDataCorrectly(): void
    {
        $data = "PQRSTUV"; // 简单的测试数据
        $chrRom = new ChrRom($data);

        $this->assertSame(ord('P'), $chrRom->read(0));
        $this->assertSame(ord('Q'), $chrRom->read(1));
        $this->assertSame(ord('R'), $chrRom->read(2));
        $this->assertSame(ord('V'), $chrRom->read(6));
    }

    /**
     * 测试可以正确写入数据
     */
    public function testCanWriteDataCorrectly(): void
    {
        $data = "ABCDEFG"; // 简单的测试数据
        $chrRom = new ChrRom($data);

        // 写入一些数据
        $chrRom->write(0, ord('X'));
        $chrRom->write(3, ord('Y'));
        $chrRom->write(6, ord('Z'));

        // 验证写入后的数据
        $this->assertSame(ord('X'), $chrRom->read(0));
        $this->assertSame(ord('B'), $chrRom->read(1)); // 未修改
        $this->assertSame(ord('C'), $chrRom->read(2)); // 未修改
        $this->assertSame(ord('Y'), $chrRom->read(3));
        $this->assertSame(ord('E'), $chrRom->read(4)); // 未修改
        $this->assertSame(ord('F'), $chrRom->read(5)); // 未修改
        $this->assertSame(ord('Z'), $chrRom->read(6));

        // 检查原始数据已修改
        $this->assertSame("XBCYEFZ", $chrRom->getData());
    }

    /**
     * 测试超出范围的地址会被镜像
     */
    public function testAddressOutOfRangeIsMirrored(): void
    {
        $data = "IJKL"; // 4字节数据
        $chrRom = new ChrRom($data);

        // 超出范围的地址会被镜像
        $this->assertSame(ord('I'), $chrRom->read(4)); // 4 % 4 = 0
        $this->assertSame(ord('J'), $chrRom->read(5)); // 5 % 4 = 1
        $this->assertSame(ord('K'), $chrRom->read(6)); // 6 % 4 = 2
        $this->assertSame(ord('L'), $chrRom->read(7)); // 7 % 4 = 3

        // 写入超出范围的地址
        $chrRom->write(8, ord('X')); // 8 % 4 = 0
        $this->assertSame(ord('X'), $chrRom->read(0));
        $this->assertSame(ord('X'), $chrRom->read(4));
        $this->assertSame("XJKL", $chrRom->getData());
    }

    /**
     * 测试可以处理空数据
     */
    public function testCanHandleEmptyData(): void
    {
        $chrRom = new ChrRom('');

        $this->assertSame(0, $chrRom->getSize());

        // 读取空数据应该返回0，而不是抛出错误
        $this->assertSame(0, $chrRom->read(0));
        $this->assertSame(0, $chrRom->read(100));

        // 写入空数据应该不会抛出错误，但也不会有任何效果
        $chrRom->write(0, 0xFF);
        $this->assertSame('', $chrRom->getData());
    }
}
