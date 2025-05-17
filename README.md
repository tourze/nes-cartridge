# NES Cartridge

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/nes/cartridge.svg?style=flat-square)](https://packagist.org/packages/nes/cartridge)
[![Total Downloads](https://img.shields.io/packagist/dt/nes/cartridge.svg?style=flat-square)](https://packagist.org/packages/nes/cartridge)

This package provides NES cartridge-related functionality, handling game ROM data loading and memory mapping for a NES emulator.

## Features

- Support for standard iNES and NES2.0 format ROM file parsing
- Memory structure management for cartridge data
- ROM data loading and processing
- Integration with the mapper module (nes-mappers)
- Support for battery-backed SRAM management

## Installation

Install via Composer:

```bash
composer require nes/cartridge
```

## Basic Usage

```php
// Load ROM from file
$cartridge = CartridgeFactory::createFromFile('/path/to/game.nes');

// Get mapper type
$mapperType = $cartridge->getMapperType();

// Get PRG-ROM data
$prgRomData = $cartridge->getPrgRomData();

// Get CHR-ROM data
$chrRomData = $cartridge->getChrRomData();

// Reset cartridge state
$cartridge->reset();
```

## Integration with Mapper Module

This package is designed to work with the `nes-mappers` package:

```php
// Cartridge and mapper integration example
$cartridge = CartridgeFactory::createFromFile('/path/to/game.nes');
$mapper = MapperFactory::create($cartridge->getMapperType(), $cartridge);

// CPU read (through mapper)
$data = $mapper->cpuRead(0x8000);

// PPU read (through mapper)
$patternData = $mapper->ppuRead(0x0000);
```

## Architecture

The module uses a layered design:

1. **Interface Layer** - Defines standard interfaces for interaction with other modules
2. **Core Layer** - Provides core cartridge implementation
3. **Functional Layer** - Implements ROM header parsing, memory management, etc.
4. **Factory Layer** - Provides factory classes for creating cartridge instances
5. **Loader Layer** - Handles file IO and ROM data loading

## Contributing

Contributions are welcome. Please ensure you add appropriate tests before submitting PRs.

## License

MIT License
