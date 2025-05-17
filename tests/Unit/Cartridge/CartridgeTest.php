<?php

namespace Tourze\NES\Cartridge\Tests\Unit\Cartridge;

use PHPUnit\Framework\TestCase;
use Tourze\NES\Cartridge\Cartridge\Cartridge;
use Tourze\NES\Cartridge\Exception\InvalidHeaderException;
use Tourze\NES\Cartridge\Header\INesHeader;
use Tourze\NES\Cartridge\Memory\ChrRom;
use Tourze\NES\Cartridge\Memory\PrgRom;
use Tourze\NES\Cartridge\Memory\SaveRam;

/**
 * 测试卡带类
 */
class CartridgeTest extends TestCase
{
    /**
     * 生成有效的头信息Mock对象
     */
    private function createHeaderMock(
        int $prgRomSize = 2,
        int $chrRomSize = 1,
        bool $hasBattery = false
    ): INesHeader {
        $header = $this->createMock(INesHeader::class);
        
        $header->method('getPrgRomSize')->willReturn($prgRomSize);
        $header->method('getChrRomSize')->willReturn($chrRomSize);
        $header->method('getMapperType')->willReturn(0);
        $header->method('hasBatteryBackedRam')->willReturn($hasBattery);
        $header->method('getPrgRomSizeInBytes')->willReturn($prgRomSize * 16 * 1024);
        $header->method('getChrRomSizeInBytes')->willReturn($chrRomSize * 8 * 1024);
        
        return $header;
    }
    
    /**
     * 测试可以正确创建卡带实例
     */
    public function testCanCreateCartridgeInstance(): void
    {
        $header = $this->createHeaderMock(2, 1);
        $prgRom = new PrgRom(str_repeat("\xAA", 2 * 16 * 1024));
        $chrRom = new ChrRom(str_repeat("\xBB", 1 * 8 * 1024));
        
        $cartridge = new Cartridge($header, $prgRom, $chrRom);
        
        $this->assertInstanceOf(Cartridge::class, $cartridge);
        $this->assertSame($header, $cartridge->getHeader());
        $this->assertSame($prgRom, $cartridge->getPrgRom());
        $this->assertSame($chrRom, $cartridge->getChrRom());
        $this->assertInstanceOf(SaveRam::class, $cartridge->getSaveRam());
    }
    
    /**
     * 测试创建卡带时提供自定义的SaveRam
     */
    public function testCanCreateCartridgeWithCustomSaveRam(): void
    {
        $header = $this->createHeaderMock(2, 1);
        $prgRom = new PrgRom(str_repeat("\xAA", 2 * 16 * 1024));
        $chrRom = new ChrRom(str_repeat("\xBB", 1 * 8 * 1024));
        $saveRam = new SaveRam(4096, true);
        
        $cartridge = new Cartridge($header, $prgRom, $chrRom, $saveRam);
        
        $this->assertSame($saveRam, $cartridge->getSaveRam());
        $this->assertSame(4096, $cartridge->getSaveRam()->getSize());
        $this->assertTrue($cartridge->getSaveRam()->isBatteryBacked());
    }
    
    /**
     * 测试当PRG ROM大小与头信息不匹配时抛出异常
     */
    public function testThrowsExceptionWhenPrgRomSizeDoesntMatch(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage('PRG ROM大小不匹配');
        
        $header = $this->createHeaderMock(2, 1); // 2个16KB PRG ROM单元
        $prgRom = new PrgRom(str_repeat("\xAA", 1 * 16 * 1024)); // 只有1个16KB
        $chrRom = new ChrRom(str_repeat("\xBB", 1 * 8 * 1024));
        
        new Cartridge($header, $prgRom, $chrRom);
    }
    
    /**
     * 测试当CHR ROM大小与头信息不匹配时抛出异常
     */
    public function testThrowsExceptionWhenChrRomSizeDoesntMatch(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage('CHR ROM大小不匹配');
        
        $header = $this->createHeaderMock(2, 1); // 1个8KB CHR ROM单元
        $prgRom = new PrgRom(str_repeat("\xAA", 2 * 16 * 1024));
        $chrRom = new ChrRom(str_repeat("\xBB", 2 * 8 * 1024)); // 2个8KB
        
        new Cartridge($header, $prgRom, $chrRom);
    }
    
    /**
     * 测试当CHR ROM大小为0时，可以使用任意大小的CHR RAM
     */
    public function testCanUseAnySizeChrRamWhenChrRomSizeIsZero(): void
    {
        $header = $this->createHeaderMock(2, 0); // 0个CHR ROM单元，使用CHR RAM
        $prgRom = new PrgRom(str_repeat("\xAA", 2 * 16 * 1024));
        $chrRom = new ChrRom(str_repeat("\xBB", 8 * 1024)); // 8KB CHR RAM
        
        $cartridge = new Cartridge($header, $prgRom, $chrRom);
        
        $this->assertInstanceOf(Cartridge::class, $cartridge);
    }
    
    /**
     * 测试卡带的CPU读写功能
     */
    public function testCartridgeCpuReadWrite(): void
    {
        $header = $this->createHeaderMock(2, 1);
        $prgRom = new PrgRom(str_repeat("\xAA", 2 * 16 * 1024));
        $chrRom = new ChrRom(str_repeat("\xBB", 1 * 8 * 1024));
        
        $cartridge = new Cartridge($header, $prgRom, $chrRom);
        
        // 测试SRAM区域的读写
        $cartridge->cpuWrite(0x6000, 0x42);
        $this->assertSame(0x42, $cartridge->cpuRead(0x6000));
        
        $cartridge->cpuWrite(0x7FFF, 0x43);
        $this->assertSame(0x43, $cartridge->cpuRead(0x7FFF));
        
        // 测试PRG ROM区域的读取
        $this->assertSame(0xAA, $cartridge->cpuRead(0x8000));
        $this->assertSame(0xAA, $cartridge->cpuRead(0xFFFF));
    }
    
    /**
     * 测试卡带的PPU读写功能
     */
    public function testCartridgePpuReadWrite(): void
    {
        // 测试CHR ROM（只读）
        $header = $this->createHeaderMock(2, 1);
        $prgRom = new PrgRom(str_repeat("\xAA", 2 * 16 * 1024));
        $chrRom = new ChrRom(str_repeat("\xBB", 1 * 8 * 1024));
        
        $cartridge = new Cartridge($header, $prgRom, $chrRom);
        
        // 测试模式表（pattern table）区域的读取
        $this->assertSame(0xBB, $cartridge->ppuRead(0x0000));
        $this->assertSame(0xBB, $cartridge->ppuRead(0x1FFF));
        
        // 尝试写入CHR ROM（应该无效）
        $cartridge->ppuWrite(0x0000, 0x42);
        $this->assertSame(0xBB, $cartridge->ppuRead(0x0000));
        
        // 测试CHR RAM（可读写）
        $header = $this->createHeaderMock(2, 0); // 0个CHR ROM单元，使用CHR RAM
        $prgRom = new PrgRom(str_repeat("\xAA", 2 * 16 * 1024));
        $chrRom = new ChrRom(str_repeat("\xCC", 8 * 1024));
        
        $cartridge = new Cartridge($header, $prgRom, $chrRom);
        
        // 测试模式表区域的读写
        $this->assertSame(0xCC, $cartridge->ppuRead(0x0000));
        
        $cartridge->ppuWrite(0x0000, 0x42);
        $this->assertSame(0x42, $cartridge->ppuRead(0x0000));
    }
    
    /**
     * 测试获取映射器类型
     */
    public function testGetMapperType(): void
    {
        $header = $this->createMock(INesHeader::class);
        $header->method('getPrgRomSize')->willReturn(2);
        $header->method('getChrRomSize')->willReturn(1);
        $header->method('getMapperType')->willReturn(1); // 映射器类型1
        $header->method('getPrgRomSizeInBytes')->willReturn(2 * 16 * 1024);
        $header->method('getChrRomSizeInBytes')->willReturn(1 * 8 * 1024);
        
        $prgRom = new PrgRom(str_repeat("\xAA", 2 * 16 * 1024));
        $chrRom = new ChrRom(str_repeat("\xBB", 1 * 8 * 1024));
        
        $cartridge = new Cartridge($header, $prgRom, $chrRom);
        
        $this->assertSame(1, $cartridge->getMapperType());
    }
    
    /**
     * 测试获取PRG和CHR数据
     */
    public function testGetRomData(): void
    {
        $header = $this->createHeaderMock(2, 1);
        $prgData = str_repeat("\xAA", 2 * 16 * 1024);
        $chrData = str_repeat("\xBB", 1 * 8 * 1024);
        
        $prgRom = new PrgRom($prgData);
        $chrRom = new ChrRom($chrData);
        
        $cartridge = new Cartridge($header, $prgRom, $chrRom);
        
        $this->assertSame($prgData, $cartridge->getPrgRomData());
        $this->assertSame($chrData, $cartridge->getChrRomData());
    }
    
    /**
     * 测试存档数据的加载和获取
     */
    public function testLoadAndGetSaveData(): void
    {
        $header = $this->createHeaderMock(2, 1, true);
        $prgRom = new PrgRom(str_repeat("\xAA", 2 * 16 * 1024));
        $chrRom = new ChrRom(str_repeat("\xBB", 1 * 8 * 1024));
        
        $cartridge = new Cartridge($header, $prgRom, $chrRom);
        
        // 加载存档数据
        $saveData = str_repeat("\x42", 8192);
        $cartridge->loadSaveData($saveData);
        
        // 验证存档数据已加载
        $this->assertSame($saveData, $cartridge->getSaveData());
        
        // 验证可以通过CPU总线读取到存档数据
        $this->assertSame(0x42, $cartridge->cpuRead(0x6000));
        $this->assertSame(0x42, $cartridge->cpuRead(0x7FFF));
    }
} 