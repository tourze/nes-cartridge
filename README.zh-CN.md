# NES Cartridge

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/nes/cartridge.svg?style=flat-square)](https://packagist.org/packages/nes/cartridge)
[![Total Downloads](https://img.shields.io/packagist/dt/nes/cartridge.svg?style=flat-square)](https://packagist.org/packages/nes/cartridge)

这个包提供了NES模拟器中卡带（Cartridge）相关的功能实现，负责处理游戏ROM数据的加载和内存映射。

## 功能特性

- 支持标准iNES和NES2.0格式ROM文件解析
- 提供卡带内存数据结构管理
- 负责ROM数据加载和处理
- 支持与映射器模块(nes-mappers)集成
- 支持带电池的存档RAM（Battery-backed SRAM）管理

## 安装

通过Composer安装:

```bash
composer require nes/cartridge
```

## 基本用法

```php
// 从文件加载ROM
$cartridge = CartridgeFactory::createFromFile('/path/to/game.nes');

// 获取映射器类型
$mapperType = $cartridge->getMapperType();

// 获取PRG-ROM数据
$prgRomData = $cartridge->getPrgRomData();

// 获取CHR-ROM数据
$chrRomData = $cartridge->getChrRomData();

// 重置卡带状态
$cartridge->reset();
```

## 与映射器模块集成

此包设计为与`nes-mappers`包协同工作:

```php
// 卡带和映射器集成示例
$cartridge = CartridgeFactory::createFromFile('/path/to/game.nes');
$mapper = MapperFactory::create($cartridge->getMapperType(), $cartridge);

// CPU读取(经过映射器)
$data = $mapper->cpuRead(0x8000);

// PPU读取(经过映射器)
$patternData = $mapper->ppuRead(0x0000);
```

## 架构设计

该模块采用分层设计:

1. **接口层** - 定义与其他模块交互的标准接口
2. **核心层** - 提供卡带核心实现
3. **功能层** - 实现ROM头解析、内存管理等功能
4. **工厂层** - 提供创建卡带实例的工厂类
5. **加载层** - 处理文件IO和ROM数据加载

## 贡献

欢迎贡献代码。请确保提交PR前添加适当的测试。

## 授权

MIT License 