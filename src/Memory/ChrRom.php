<?php

namespace Tourze\NES\Cartridge\Memory;

/**
 * 图形ROM管理
 * 用于存储和访问游戏图形数据
 */
class ChrRom
{
    /**
     * CHR ROM数据
     */
    private string $data;

    /**
     * CHR ROM大小（字节）
     */
    private int $size;

    /**
     * 创建CHR ROM实例
     *
     * @param string $data CHR ROM二进制数据
     */
    public function __construct(string $data)
    {
        $this->data = $data;
        $this->size = strlen($data);
    }

    /**
     * 从指定地址读取一个字节
     *
     * @param int $address 相对于CHR ROM起始的地址
     * @return int 读取的字节数据
     */
    public function read(int $address): int
    {
        if ($address < 0 || $address >= $this->size) {
            // 地址超出范围时镜像
            $address = $address % max(1, $this->size);
        }

        // 处理空数据的情况
        if ($this->size === 0) {
            return 0;
        }

        return ord($this->data[$address]);
    }

    /**
     * 写入一个字节到指定地址
     * 注意：大多数卡带的CHR ROM是只读的，但有些卡带使用CHR RAM，可以写入
     *
     * @param int $address 相对于CHR ROM/RAM起始的地址
     * @param int $value 要写入的字节数据
     */
    public function write(int $address, int $value): void
    {
        // 需要把string转为可修改的数组
        $dataArray = str_split($this->data);

        if ($address < 0 || $address >= $this->size) {
            // 地址超出范围时镜像
            $address = $address % max(1, $this->size);
        }

        // 写入数据
        if ($this->size > 0) {
            $dataArray[$address] = chr($value & 0xFF);
            $this->data = implode('', $dataArray);
        }
    }

    /**
     * 获取CHR ROM大小（字节）
     *
     * @return int CHR ROM大小
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * 获取CHR ROM原始数据
     *
     * @return string CHR ROM二进制数据
     */
    public function getData(): string
    {
        return $this->data;
    }
}
