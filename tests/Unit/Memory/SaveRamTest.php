<?php

namespace Tourze\NES\Cartridge\Tests\Unit\Memory;

use PHPUnit\Framework\TestCase;
use Tourze\NES\Cartridge\Memory\SaveRam;

/**
 * 测试存档RAM管理类
 */
class SaveRamTest extends TestCase
{
    /**
     * 测试可以正确创建SaveRam实例
     */
    public function testCanCreateSaveRamInstance(): void
    {
        // 默认大小和电池状态
        $saveRam = new SaveRam();

        $this->assertInstanceOf(SaveRam::class, $saveRam);
        $this->assertSame(8192, $saveRam->getSize());
        $this->assertFalse($saveRam->isBatteryBacked());
        $this->assertSame(str_repeat("\x00", 8192), $saveRam->getData());

        // 自定义大小和电池状态
        $saveRam = new SaveRam(4096, true);

        $this->assertSame(4096, $saveRam->getSize());
        $this->assertTrue($saveRam->isBatteryBacked());
        $this->assertSame(str_repeat("\x00", 4096), $saveRam->getData());

        // 带初始数据
        $initialData = str_repeat("\xFF", 2048);
        $saveRam = new SaveRam(2048, false, $initialData);

        $this->assertSame(2048, $saveRam->getSize());
        $this->assertFalse($saveRam->isBatteryBacked());
        $this->assertSame($initialData, $saveRam->getData());
    }

    /**
     * 测试可以正确读写数据
     */
    public function testCanReadAndWriteDataCorrectly(): void
    {
        $saveRam = new SaveRam(16); // 16字节的SRAM

        // 初始状态应该是全0
        for ($i = 0; $i < 16; $i++) {
            $this->assertSame(0, $saveRam->read($i));
        }

        // 写入一些数据
        $saveRam->write(0, 0x12);
        $saveRam->write(5, 0x34);
        $saveRam->write(10, 0x56);
        $saveRam->write(15, 0x78);

        // 读取并验证
        $this->assertSame(0x12, $saveRam->read(0));
        $this->assertSame(0x34, $saveRam->read(5));
        $this->assertSame(0x56, $saveRam->read(10));
        $this->assertSame(0x78, $saveRam->read(15));

        // 未写入的位置应该仍然是0
        $this->assertSame(0, $saveRam->read(1));
        $this->assertSame(0, $saveRam->read(6));
        $this->assertSame(0, $saveRam->read(11));
    }

    /**
     * 测试超出范围的地址会被镜像
     */
    public function testAddressOutOfRangeIsMirrored(): void
    {
        $saveRam = new SaveRam(4); // 4字节的SRAM

        // 写入数据
        $saveRam->write(0, 0xAA);
        $saveRam->write(1, 0xBB);
        $saveRam->write(2, 0xCC);
        $saveRam->write(3, 0xDD);

        // 超出范围的读取会被镜像
        $this->assertSame(0xAA, $saveRam->read(4)); // 4 % 4 = 0
        $this->assertSame(0xBB, $saveRam->read(5)); // 5 % 4 = 1
        $this->assertSame(0xCC, $saveRam->read(6)); // 6 % 4 = 2
        $this->assertSame(0xDD, $saveRam->read(7)); // 7 % 4 = 3

        // 超出范围的写入会被镜像
        $saveRam->write(8, 0xEE); // 8 % 4 = 0
        $this->assertSame(0xEE, $saveRam->read(0));
        $this->assertSame(0xEE, $saveRam->read(4));
        $this->assertSame(0xEE, $saveRam->read(8));
    }

    /**
     * 测试加载数据功能
     */
    public function testLoadDataFunctionality(): void
    {
        $saveRam = new SaveRam(8);

        // 初始状态是全0
        $this->assertSame(str_repeat("\x00", 8), $saveRam->getData());

        // 加载完全匹配大小的数据
        $saveRam->loadData("\x01\x02\x03\x04\x05\x06\x07\x08");
        $this->assertSame("\x01\x02\x03\x04\x05\x06\x07\x08", $saveRam->getData());

        // 加载过短的数据（应该填充0）
        $saveRam->loadData("\xAA\xBB\xCC");
        $this->assertSame("\xAA\xBB\xCC\x00\x00\x00\x00\x00", $saveRam->getData());

        // 加载过长的数据（应该截断）
        $saveRam->loadData("\x11\x22\x33\x44\x55\x66\x77\x88\x99\xAA");
        $this->assertSame("\x11\x22\x33\x44\x55\x66\x77\x88", $saveRam->getData());
    }
}
