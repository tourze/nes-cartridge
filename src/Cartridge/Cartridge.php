<?php

namespace Tourze\NES\Cartridge\Cartridge;

use Tourze\NES\Cartridge\Exception\InvalidHeaderException;
use Tourze\NES\Cartridge\Header\INesHeader;
use Tourze\NES\Cartridge\Memory\ChrRom;
use Tourze\NES\Cartridge\Memory\PrgRom;
use Tourze\NES\Cartridge\Memory\SaveRam;

/**
 * 卡带实现
 * 
 * 提供NES卡带的核心功能，管理PRG ROM、CHR ROM和存档RAM数据，
 * 与映射器模块配合实现内存映射功能。
 */
class Cartridge implements CartridgeInterface
{
    /**
     * 卡带头信息
     */
    private INesHeader $header;

    /**
     * 程序ROM
     */
    private PrgRom $prgRom;

    /**
     * 图形ROM
     */
    private ChrRom $chrRom;

    /**
     * 存档RAM
     */
    private SaveRam $saveRam;
    
    /**
     * 创建卡带实例
     * 
     * @param INesHeader $header 卡带头信息
     * @param PrgRom $prgRom 程序ROM
     * @param ChrRom $chrRom 图形ROM
     * @param SaveRam|null $saveRam 存档RAM，如果为null则根据头信息自动创建
     * @throws InvalidHeaderException 如果头信息无效
     */
    public function __construct(
        INesHeader $header,
        PrgRom $prgRom,
        ChrRom $chrRom,
        ?SaveRam $saveRam = null
    ) {
        $this->header = $header;
        $this->prgRom = $prgRom;
        $this->chrRom = $chrRom;
        
        // 如果未提供SaveRam，则根据头信息创建
        if ($saveRam === null) {
            $this->saveRam = new SaveRam(
                8192, // 标准8KB SRAM
                $header->hasBatteryBackedRam()
            );
        } else {
            $this->saveRam = $saveRam;
        }
        
        // 验证ROM大小与头信息一致
        if ($prgRom->getSize() !== $header->getPrgRomSizeInBytes()) {
            throw new InvalidHeaderException(
                "PRG ROM大小不匹配：头信息标记为{$header->getPrgRomSizeInBytes()}字节，但实际为{$prgRom->getSize()}字节"
            );
        }
        
        // CHR ROM可以为0（使用CHR RAM）
        if ($header->getChrRomSize() > 0 && $chrRom->getSize() !== $header->getChrRomSizeInBytes()) {
            throw new InvalidHeaderException(
                "CHR ROM大小不匹配：头信息标记为{$header->getChrRomSizeInBytes()}字节，但实际为{$chrRom->getSize()}字节"
            );
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function cpuRead(int $address): int
    {
        // 这个方法在实际使用中应该将请求转发给映射器
        // 由于我们已将映射器分离到独立模块，此处仅提供一个基本实现
        // 仅处理SRAM范围的地址，其他地址应由映射器处理
        
        // SRAM通常映射在0x6000-0x7FFF
        if ($address >= 0x6000 && $address <= 0x7FFF) {
            return $this->saveRam->read($address - 0x6000);
        }
        
        // 简单实现：直接从PRG ROM读取，实际应由映射器处理
        if ($address >= 0x8000) {
            // 简单镜像到PRG ROM空间，实际映射应由映射器实现
            return $this->prgRom->read($address - 0x8000);
        }
        
        return 0; // 不在已知区域时返回0
    }
    
    /**
     * {@inheritdoc}
     */
    public function cpuWrite(int $address, int $data): void
    {
        // 这个方法在实际使用中应该将请求转发给映射器
        // 由于我们已将映射器分离到独立模块，此处仅提供一个基本实现
        
        // SRAM通常映射在0x6000-0x7FFF
        if ($address >= 0x6000 && $address <= 0x7FFF) {
            $this->saveRam->write($address - 0x6000, $data);
        }
        
        // 其他写入操作应由映射器处理
    }
    
    /**
     * {@inheritdoc}
     */
    public function ppuRead(int $address): int
    {
        // 这个方法在实际使用中应该将请求转发给映射器
        // 由于我们已将映射器分离到独立模块，此处仅提供一个基本实现
        
        // 图案表（Pattern Tables）通常映射在0x0000-0x1FFF
        if ($address <= 0x1FFF) {
            return $this->chrRom->read($address);
        }
        
        return 0; // 不在已知区域时返回0
    }
    
    /**
     * {@inheritdoc}
     */
    public function ppuWrite(int $address, int $data): void
    {
        // 这个方法在实际使用中应该将请求转发给映射器
        // 由于我们已将映射器分离到独立模块，此处仅提供一个基本实现
        
        // 如果是CHR RAM，可以写入图案表（Pattern Tables）
        if ($address <= 0x1FFF && $this->header->getChrRomSize() === 0) {
            $this->chrRom->write($address, $data);
        }
        
        // 其他写入操作应由映射器处理
    }
    
    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        // 重置卡带状态
        // 在实际使用中，映射器通常也需要重置
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMapperType(): int
    {
        return $this->header->getMapperType();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPrgRomData(): string
    {
        return $this->prgRom->getData();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getChrRomData(): string
    {
        return $this->chrRom->getData();
    }
    
    /**
     * 获取卡带头信息
     * 
     * @return INesHeader 卡带头信息
     */
    public function getHeader(): INesHeader
    {
        return $this->header;
    }
    
    /**
     * 获取程序ROM对象
     * 
     * @return PrgRom 程序ROM对象
     */
    public function getPrgRom(): PrgRom
    {
        return $this->prgRom;
    }
    
    /**
     * 获取图形ROM对象
     * 
     * @return ChrRom 图形ROM对象
     */
    public function getChrRom(): ChrRom
    {
        return $this->chrRom;
    }
    
    /**
     * 获取存档RAM对象
     * 
     * @return SaveRam 存档RAM对象
     */
    public function getSaveRam(): SaveRam
    {
        return $this->saveRam;
    }
    
    /**
     * 载入存档数据
     * 
     * @param string $data 存档数据
     */
    public function loadSaveData(string $data): void
    {
        $this->saveRam->loadData($data);
    }
    
    /**
     * 获取存档数据
     * 
     * @return string 存档数据
     */
    public function getSaveData(): string
    {
        return $this->saveRam->getData();
    }
} 