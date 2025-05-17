<?php

namespace Tourze\NES\Cartridge\Memory;

/**
 * 程序ROM管理
 * 用于存储和访问游戏程序代码
 */
class PrgRom
{
    /**
     * PRG ROM数据
     */
    private string $data;

    /**
     * PRG ROM大小（字节）
     */
    private int $size;

    /**
     * 创建PRG ROM实例
     *
     * @param string $data PRG ROM二进制数据
     */
    public function __construct(string $data)
    {
        $this->data = $data;
        $this->size = strlen($data);
    }

    /**
     * 从指定地址读取一个字节
     *
     * @param int $address 相对于PRG ROM起始的地址
     * @return int 读取的字节数据
     */
    public function read(int $address): int
    {
        // 处理空数据的情况
        if ($this->size === 0) {
            return 0;
        }

        if ($address < 0 || $address >= $this->size) {
            // 地址超出范围时镜像
            // 使用正确的取模操作，避免除以零错误
            $address = ($address % $this->size + $this->size) % $this->size;
        }

        return ord($this->data[$address]);
    }

    /**
     * 获取PRG ROM大小（字节）
     *
     * @return int PRG ROM大小
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * 获取PRG ROM原始数据
     *
     * @return string PRG ROM二进制数据
     */
    public function getData(): string
    {
        return $this->data;
    }
}
