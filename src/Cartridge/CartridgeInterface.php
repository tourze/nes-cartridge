<?php

namespace Tourze\NES\Cartridge\Cartridge;

/**
 * 卡带接口
 * 定义卡带的基本操作和与映射器交互的方法
 */
interface CartridgeInterface
{
    /**
     * CPU读取操作
     *
     * @param int $address 地址
     * @return int 读取的数据
     */
    public function cpuRead(int $address): int;

    /**
     * CPU写入操作
     *
     * @param int $address 地址
     * @param int $data 要写入的数据
     */
    public function cpuWrite(int $address, int $data): void;

    /**
     * PPU读取操作
     *
     * @param int $address 地址
     * @return int 读取的数据
     */
    public function ppuRead(int $address): int;

    /**
     * PPU写入操作
     *
     * @param int $address 地址
     * @param int $data 要写入的数据
     */
    public function ppuWrite(int $address, int $data): void;

    /**
     * 重置卡带状态
     */
    public function reset(): void;

    /**
     * 获取映射器类型ID
     *
     * @return int 映射器ID
     */
    public function getMapperType(): int;

    /**
     * 获取PRG-ROM数据
     *
     * @return string PRG-ROM的二进制数据
     */
    public function getPrgRomData(): string;

    /**
     * 获取CHR-ROM数据
     *
     * @return string CHR-ROM的二进制数据
     */
    public function getChrRomData(): string;
}
