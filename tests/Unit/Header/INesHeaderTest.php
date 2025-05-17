<?php

namespace Tourze\NES\Cartridge\Tests\Unit\Header;

use PHPUnit\Framework\TestCase;
use Tourze\NES\Cartridge\Exception\InvalidHeaderException;
use Tourze\NES\Cartridge\Header\INesHeader;

/**
 * 测试iNES头信息处理类
 */
class INesHeaderTest extends TestCase
{
    /**
     * 生成有效的iNES头数据
     *
     * @param int $prgRomSize PRG ROM单元数量
     * @param int $chrRomSize CHR ROM单元数量
     * @param int $flags6 标志位6
     * @param int $flags7 标志位7
     * @return string 16字节的头数据
     */
    private function createValidHeader(
        int $prgRomSize = 2,
        int $chrRomSize = 1,
        int $flags6 = 0,
        int $flags7 = 0
    ): string
    {
        // "NES" + MS-DOS EOF
        $header = "\x4E\x45\x53\x1A";

        // PRG ROM和CHR ROM大小
        $header .= chr($prgRomSize);
        $header .= chr($chrRomSize);

        // 标志位
        $header .= chr($flags6);
        $header .= chr($flags7);

        // 剩余的未使用字节填充为0
        $header .= str_repeat("\x00", 8);

        return $header;
    }

    /**
     * 测试有效的头信息可以被正确解析
     */
    public function testValidHeaderIsParsedCorrectly(): void
    {
        $header = $this->createValidHeader(2, 1, 0, 0);
        $inesHeader = new INesHeader($header);

        $this->assertSame(2, $inesHeader->getPrgRomSize());
        $this->assertSame(1, $inesHeader->getChrRomSize());
        $this->assertSame(0, $inesHeader->getMapperType());
        $this->assertFalse($inesHeader->hasVerticalMirroring());
        $this->assertFalse($inesHeader->hasBatteryBackedRam());
        $this->assertFalse($inesHeader->hasTrainer());
        $this->assertFalse($inesHeader->hasFourScreenVram());
    }

    /**
     * 测试标志位被正确解析
     */
    public function testFlagsAreParsedCorrectly(): void
    {
        // 设置垂直镜像、电池RAM和映射器类型3
        $header = $this->createValidHeader(2, 1, 0x33, 0);
        $inesHeader = new INesHeader($header);

        $this->assertTrue($inesHeader->hasVerticalMirroring());
        $this->assertTrue($inesHeader->hasBatteryBackedRam());
        $this->assertFalse($inesHeader->hasTrainer());
        $this->assertFalse($inesHeader->hasFourScreenVram());
        $this->assertSame(3, $inesHeader->getMapperType());

        // 设置训练器、四屏VRAM和映射器类型3
        $header = $this->createValidHeader(2, 1, 0x3C, 0);
        $inesHeader = new INesHeader($header);

        $this->assertFalse($inesHeader->hasVerticalMirroring());
        $this->assertFalse($inesHeader->hasBatteryBackedRam());
        $this->assertTrue($inesHeader->hasTrainer());
        $this->assertTrue($inesHeader->hasFourScreenVram());
        $this->assertSame(3, $inesHeader->getMapperType());
    }

    /**
     * 测试映射器ID的高位和低位组合正确
     */
    public function testMapperTypeIsCorrectlyCombined(): void
    {
        // 设置映射器类型为1
        $header = $this->createValidHeader(2, 1, 0x10, 0);
        $inesHeader = new INesHeader($header);
        $this->assertSame(1, $inesHeader->getMapperType());

        // 设置映射器类型为4
        $header = $this->createValidHeader(2, 1, 0x40, 0);
        $inesHeader = new INesHeader($header);
        $this->assertSame(4, $inesHeader->getMapperType());

        // 设置映射器类型为128 (高位8)
        $header = $this->createValidHeader(2, 1, 0, 0x80);
        $inesHeader = new INesHeader($header);
        $this->assertSame(128, $inesHeader->getMapperType());

        // 设置映射器类型为119 (高位7 + 低位7)
        $header = $this->createValidHeader(2, 1, 0x70, 0x70);
        $inesHeader = new INesHeader($header);
        $this->assertSame(119, $inesHeader->getMapperType());
    }

    /**
     * 测试字节大小计算正确
     */
    public function testSizeCalculationsAreCorrect(): void
    {
        $header = $this->createValidHeader(2, 1, 0, 0);
        $inesHeader = new INesHeader($header);

        // 2个16KB单元
        $this->assertSame(32 * 1024, $inesHeader->getPrgRomSizeInBytes());

        // 1个8KB单元
        $this->assertSame(8 * 1024, $inesHeader->getChrRomSizeInBytes());

        // 没有训练器
        $this->assertSame(0, $inesHeader->getTrainerSizeInBytes());

        // 添加训练器
        $header = $this->createValidHeader(2, 1, 0x04, 0);
        $inesHeader = new INesHeader($header);

        // 有训练器
        $this->assertSame(512, $inesHeader->getTrainerSizeInBytes());
    }

    /**
     * 测试头信息长度不足时抛出异常
     */
    public function testExceptionIsThrownWhenHeaderIsTooShort(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage('iNES头信息长度不足16字节');

        new INesHeader("\x4E\x45\x53\x1A\x02\x01\x00");
    }

    /**
     * 测试无效的头标识符时抛出异常
     */
    public function testExceptionIsThrownWhenSignatureIsInvalid(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage('无效的iNES头标识符');

        $invalidHeader = "INES" . str_repeat("\x00", 12);
        new INesHeader($invalidHeader);
    }

    /**
     * 测试PRG ROM大小为0时抛出异常
     */
    public function testExceptionIsThrownWhenPrgRomSizeIsZero(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage('PRG ROM大小不能为0');

        $header = $this->createValidHeader(0, 1, 0, 0);
        new INesHeader($header);
    }
}
