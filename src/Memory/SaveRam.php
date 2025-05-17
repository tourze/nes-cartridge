<?php

namespace Tourze\NES\Cartridge\Memory;

/**
 * 存档RAM管理
 * 用于存储和访问游戏存档数据
 */
class SaveRam
{
    /**
     * SRAM数据
     */
    private string $data;

    /**
     * SRAM大小（字节）
     */
    private int $size;

    /**
     * 是否启用电池供电（保存数据）
     */
    private bool $batteryBacked;

    /**
     * 创建SRAM实例
     *
     * @param int $size SRAM大小（字节）
     * @param bool $batteryBacked 是否启用电池供电（保存数据）
     * @param string|null $initialData 初始数据，如果为null则初始化为全0
     */
    public function __construct(int $size = 8192, bool $batteryBacked = false, ?string $initialData = null)
    {
        $this->size = $size;
        $this->batteryBacked = $batteryBacked;

        // 初始化数据
        if ($initialData !== null && strlen($initialData) === $size) {
            $this->data = $initialData;
        } else {
            $this->data = str_repeat("\x00", $size);
        }
    }

    /**
     * 从指定地址读取一个字节
     *
     * @param int $address 相对于SRAM起始的地址
     * @return int 读取的字节数据
     */
    public function read(int $address): int
    {
        if ($address < 0 || $address >= $this->size) {
            // 地址超出范围时镜像
            $address = $address % max(1, $this->size);
        }

        return ord($this->data[$address]);
    }

    /**
     * 写入一个字节到指定地址
     *
     * @param int $address 相对于SRAM起始的地址
     * @param int $value 要写入的字节数据
     */
    public function write(int $address, int $value): void
    {
        if ($address < 0 || $address >= $this->size) {
            // 地址超出范围时镜像
            $address = $address % max(1, $this->size);
        }

        // 需要把string转为可修改的数组
        $dataArray = str_split($this->data);
        $dataArray[$address] = chr($value & 0xFF);
        $this->data = implode('', $dataArray);
    }

    /**
     * 获取SRAM大小（字节）
     *
     * @return int SRAM大小
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * 获取SRAM原始数据
     *
     * @return string SRAM二进制数据
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * 检查是否启用电池供电（保存数据）
     *
     * @return bool 是否启用电池供电
     */
    public function isBatteryBacked(): bool
    {
        return $this->batteryBacked;
    }

    /**
     * 加载存档数据
     *
     * @param string $data 要加载的数据
     */
    public function loadData(string $data): void
    {
        if (strlen($data) === $this->size) {
            $this->data = $data;
        } elseif (strlen($data) > $this->size) {
            // 截断过长的数据
            $this->data = substr($data, 0, $this->size);
        } else {
            // 填充过短的数据
            $this->data = str_pad($data, $this->size, "\x00");
        }
    }
}
