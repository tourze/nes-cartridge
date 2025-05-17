<?php

namespace Tourze\NES\Cartridge\Header;

use Tourze\NES\Cartridge\Exception\InvalidHeaderException;

/**
 * iNES格式头信息处理
 * 用于解析和验证标准的16字节iNES ROM头信息
 *
 * @see https://wiki.nesdev.org/w/index.php/INES
 */
class INesHeader
{
    /**
     * iNES头标识符
     */
    private const HEADER_SIGNATURE = "\x4E\x45\x53\x1A"; // "NES" + MS-DOS EOF

    /**
     * PRG ROM的16KB单元数量
     */
    private int $prgRomSize;

    /**
     * CHR ROM的8KB单元数量
     */
    private int $chrRomSize;

    /**
     * 映射器类型ID
     */
    private int $mapperType;

    /**
     * 是否使用垂直镜像
     */
    private bool $verticalMirroring;

    /**
     * 是否包含电池记忆的RAM
     */
    private bool $hasBatteryBackedRam;

    /**
     * 是否包含训练器
     */
    private bool $hasTrainer;

    /**
     * 是否使用四屏VRAM
     */
    private bool $hasFourScreenVram;

    /**
     * 解析iNES头信息
     *
     * @param string $headerData 16字节的头信息数据
     * @throws InvalidHeaderException 如果头信息无效
     */
    public function __construct(string $headerData)
    {
        if (strlen($headerData) < 16) {
            throw new InvalidHeaderException('iNES头信息长度不足16字节');
        }

        // 验证头标识
        if (substr($headerData, 0, 4) !== self::HEADER_SIGNATURE) {
            throw new InvalidHeaderException('无效的iNES头标识符');
        }

        // 解析头信息
        $this->prgRomSize = ord($headerData[4]);
        $this->chrRomSize = ord($headerData[5]);

        // 解析标志位 Flag6
        $flag6 = ord($headerData[6]);
        $this->verticalMirroring = ($flag6 & 0x01) !== 0;
        $this->hasBatteryBackedRam = ($flag6 & 0x02) !== 0;
        $this->hasTrainer = ($flag6 & 0x04) !== 0;
        $this->hasFourScreenVram = ($flag6 & 0x08) !== 0;
        $lowerMapperNibble = ($flag6 >> 4) & 0x0F;

        // 解析标志位 Flag7
        $flag7 = ord($headerData[7]);
        $upperMapperNibble = ($flag7 & 0xF0) >> 4; // 修正这里，需要右移4位

        // 组合得到完整的映射器ID
        $this->mapperType = ($upperMapperNibble << 4) | $lowerMapperNibble;

        // 验证PRG ROM大小
        if ($this->prgRomSize === 0) {
            throw new InvalidHeaderException('PRG ROM大小不能为0');
        }
    }

    /**
     * 获取PRG ROM的16KB单元数量
     */
    public function getPrgRomSize(): int
    {
        return $this->prgRomSize;
    }

    /**
     * 获取CHR ROM的8KB单元数量
     */
    public function getChrRomSize(): int
    {
        return $this->chrRomSize;
    }

    /**
     * 获取映射器类型ID
     */
    public function getMapperType(): int
    {
        return $this->mapperType;
    }

    /**
     * 是否使用垂直镜像
     */
    public function hasVerticalMirroring(): bool
    {
        return $this->verticalMirroring;
    }

    /**
     * 是否包含电池记忆的RAM
     */
    public function hasBatteryBackedRam(): bool
    {
        return $this->hasBatteryBackedRam;
    }

    /**
     * 是否包含训练器
     */
    public function hasTrainer(): bool
    {
        return $this->hasTrainer;
    }

    /**
     * 是否使用四屏VRAM
     */
    public function hasFourScreenVram(): bool
    {
        return $this->hasFourScreenVram;
    }

    /**
     * 计算PRG ROM的字节大小
     */
    public function getPrgRomSizeInBytes(): int
    {
        return $this->prgRomSize * 16 * 1024; // 16KB单元
    }

    /**
     * 计算CHR ROM的字节大小
     */
    public function getChrRomSizeInBytes(): int
    {
        return $this->chrRomSize * 8 * 1024; // 8KB单元
    }

    /**
     * 计算训练器数据的大小
     */
    public function getTrainerSizeInBytes(): int
    {
        return $this->hasTrainer ? 512 : 0;
    }
}
