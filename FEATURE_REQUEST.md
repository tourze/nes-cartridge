# NES-Cartridge 功能需求清单

为了使 `nes-cartridge` 模块能够完全替代 `nes-emulator` 中的卡带实现，需要进行以下功能扩展和改进：

## 1. 核心功能补充

### 1.1 工厂类和加载器实现

- 完成 `CartridgeFactory` 类，支持以下功能：
  - `createFromFile($path)`: 从文件路径加载ROM
  - `createFromData($data)`: 从二进制数据创建卡带
  - `create($header, $prgRom, $chrRom, $options)`: 从组件创建卡带

- 实现文件加载器：
  - `FileLoader`: 提供文件读写功能
  - `CartridgeLoader`: 解析ROM文件内容并创建卡带实例

### 1.2 接口扩展

扩展 `CartridgeInterface` 接口，增加以下方法：

```php
// 镜像模式相关
public function getMirroringMode(): int;
public function setMirroringMode(int $mode): void;

// 细粒度存储器访问
public function readPRGROM(int $address): int;
public function readCHRROM(int $address): int;
public function readPRGRAM(int $address): int;
public function writePRGRAM(int $address, int $value): void;
public function readCHRRAM(int $address): int;
public function writeCHRRAM(int $address, int $value): void;

// 更多功能
public function getInfo(): CartridgeInfo;  // 返回完整的卡带信息
public function cpuCycle(): void;  // CPU周期通知（用于映射器时序）
public function saveRam(string $filename): bool;  // 存档保存
public function loadRam(string $filename): bool;  // 存档加载
```

### 1.3 NES 2.0 格式支持

- 实现 `NesHeader` 类，完整支持 NES 2.0 格式扩展
- 支持子映射器（Submapper）的识别和处理

## 2. 与映射器模块集成

### 2.1 完善映射器交互

- 增强与 `nes-mmc` 模块的互操作性
- 提供更灵活的映射器加载机制
- 支持运行时映射器切换（如有需要）
- 确保 `nes-cartridge` 能与 `nes-mmc` 模块中的 `MapperFactory` 和 `MapperInterface` 无缝协作

### 2.2 映射器通知机制

- 实现映射器事件通知系统
- 支持特殊映射器要求的时钟周期通知
- 与 `nes-mmc` 中定义的映射器生命周期函数对接

## 3. 增强的内存管理

### 3.1 更多内存类型支持

- 增加对 Trainer 数据的支持
- 增强存档 RAM 的持久化功能
- 支持四屏幕镜像和其他特殊镜像模式，与 `nes-mmc` 的 `MirroringMode` 枚举对接

### 3.2 内存访问优化

- 优化内存读写性能
- 增加内存缓存机制
- 支持批量内存操作

## 4. 兼容性和测试

### 4.1 兼容测试

- 开发兼容性测试套件
- 测试常见游戏ROM是否能正确加载和识别
- 确保与 `nes-emulator` 的行为一致性
- 测试与 `nes-mmc` 模块的集成是否正常工作

### 4.2 单元测试扩展

- 为新增功能添加完整的单元测试
- 添加边界条件测试
- 添加性能测试
- 添加与 `nes-mmc` 的集成测试

## 5. 文档与示例

### 5.1 接口文档

- 为所有公共接口提供详细文档
- 提供类图和数据流程图
- 详细说明与 `nes-mmc` 模块的交互方式

### 5.2 使用示例

- 添加更多使用示例
- 提供与模拟器集成的示例代码
- 编写与 `nes-mmc` 模块集成的完整示例

## 6. 兼容性计划

为确保平滑过渡，建议制定从 `nes-emulator` 卡带实现迁移到 `nes-cartridge` 的兼容性计划：

1. 实现适配器层，允许 `nes-emulator` 使用 `nes-cartridge` 和 `nes-mmc` 的组合
2. 提供迁移工具，帮助用户从旧接口转换到新接口
3. 制定分阶段迁移策略，避免一次性大规模变更

## 优先级排序

按重要性和实施顺序排列的功能需求：

1. 完成基础工厂类和加载器
2. 扩展接口以支持所有必要功能  
3. 完善与 `nes-mmc` 映射器的集成
4. 增强内存管理功能
5. 添加 NES 2.0 格式支持
6. 开发完整测试套件
7. 完善文档和示例
8. 实现兼容性计划

## 时间估计

基于当前进度和复杂度，预计完成上述功能需要：

- **核心功能补充**: 2-3周
- **映射器集成**: 1-2周
- **内存管理增强**: 1周
- **测试与文档**: 1-2周
- **总计**: 5-8周工作量
