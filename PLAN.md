# NES卡带模块开发计划

## 1. 目录结构

```shell
nes-cartridge/
├── src/
│   ├── Cartridge/                # 卡带核心类
│   │   ├── Cartridge.php         # 卡带基础类
│   │   ├── CartridgeFactory.php  # 卡带工厂类
│   │   └── CartridgeInterface.php # 卡带接口 (已完成)
│   ├── Header/                   # 卡带头信息处理
│   │   ├── INesHeader.php        # iNES格式头信息处理 (已完成)
│   │   └── NesHeader.php         # NES2.0格式头信息处理
│   ├── Memory/                   # 内存管理
│   │   ├── PrgRom.php            # 程序ROM (已完成)
│   │   ├── ChrRom.php            # 图形ROM (已完成)
│   │   └── SaveRam.php           # 存档RAM (已完成)
│   ├── Exception/                # 异常处理
│   │   ├── CartridgeException.php    # 卡带异常基类 (已完成)
│   │   ├── InvalidHeaderException.php # 无效头信息异常 (已完成)
│   │   └── UnsupportedMapperException.php # 不支持的映射器异常 (已完成)
│   └── Loader/                   # 文件加载器
│       ├── FileLoader.php        # 文件加载器
│       └── CartridgeLoader.php   # 卡带加载器
├── tests/                        # 测试目录
│   ├── Unit/                     # 单元测试
│   │   ├── Cartridge/            # 卡带测试 (已完成接口测试)
│   │   ├── Header/               # 头信息测试 (已完成)
│   │   └── Memory/               # 内存测试 (已完成)
│   └── Fixture/                  # 测试固件
│       └── roms/                 # 测试ROM文件
└── README.md                     # 文档 (已完成)
```

## 2. 模块分层设计

1. **接口层**
   - `CartridgeInterface` - 定义卡带对外接口，包括与映射器互操作的接口 (已完成)

2. **核心层**
   - `Cartridge` - 卡带核心实现

3. **功能层**
   - 头信息处理 - 解析和验证ROM文件头信息 (已完成)
   - 内存管理 - PRG-ROM, CHR-ROM, SRAM管理 (已完成)

4. **工厂层**
   - `CartridgeFactory` - 卡带实例化工厂

5. **加载层**
   - `FileLoader` - 处理文件IO
   - `CartridgeLoader` - 加载ROM文件到卡带对象

## 3. 类级别设计

### Cartridge 模块

- **CartridgeInterface**：定义卡带的公共接口 (已完成)
  - 方法：`cpuRead`, `cpuWrite`, `ppuRead`, `ppuWrite`, `reset`
  - 方法：`getMapperType`, `getPrgRomData`, `getChrRomData` - 用于与映射器模块交互

- **Cartridge**：卡带基础实现
  - 属性：程序ROM、图形ROM、存档RAM、头信息、映射器类型ID
  - 功能：CPU/PPU总线读写转发、ROM数据管理、与映射器模块集成

- **CartridgeFactory**：创建卡带实例
  - 方法：`create`、`createFromFile`、`createFromData`

### Header 模块

- **INesHeader**：iNES格式（.NES文件）头信息处理 (已完成)
  - 功能：解析16字节标准iNES头

- **NesHeader**：NES2.0格式头信息处理
  - 功能：解析扩展的NES2.0格式头信息

### Memory 模块

- **PrgRom**：程序ROM管理 (已完成)
  - 功能：管理程序代码存储和访问

- **ChrRom**：图形ROM管理 (已完成)
  - 功能：管理图形数据存储和访问

- **SaveRam**：存档RAM管理 (已完成)
  - 功能：管理游戏存档RAM，支持持久化

### Loader 模块

- **FileLoader**：文件加载实用工具
  - 功能：读取文件内容

- **CartridgeLoader**：卡带加载器
  - 功能：解析ROM文件并创建适当的卡带实例

### Exception 模块

- **CartridgeException**：卡带异常基类 (已完成)
  - 功能：所有卡带相关异常的基类

- **InvalidHeaderException**：头信息异常 (已完成)
  - 功能：处理无效或不支持的ROM头信息

- **UnsupportedMapperException**：不支持的映射器异常 (已完成)
  - 功能：处理不支持的映射器类型

## 4. 完成进度规划

| 模块 | 类 | 状态 | 优先级 | 依赖项 |
|------|-----|------|--------|--------|
| Cartridge | CartridgeInterface | 已完成 | 高 | 无 |
| Cartridge | Cartridge | 未开始 | 高 | CartridgeInterface |
| Cartridge | CartridgeFactory | 未开始 | 中 | Cartridge |
| Header | INesHeader | 已完成 | 高 | 无 |
| Header | NesHeader | 未开始 | 低 | INesHeader |
| Memory | PrgRom | 已完成 | 高 | 无 |
| Memory | ChrRom | 已完成 | 高 | 无 |
| Memory | SaveRam | 已完成 | 中 | 无 |
| Exception | CartridgeException | 已完成 | 中 | 无 |
| Exception | InvalidHeaderException | 已完成 | 中 | CartridgeException |
| Exception | UnsupportedMapperException | 已完成 | 中 | CartridgeException |
| Loader | FileLoader | 未开始 | 中 | 无 |
| Loader | CartridgeLoader | 未开始 | 中 | FileLoader, CartridgeFactory |

## 实施步骤

1. **阶段1：基础结构** (已完成)
   - 实现卡带接口定义
   - 实现头信息解析
   - 实现基本异常处理

2. **阶段2：核心功能** (已完成)
   - 实现基本卡带类
   - 实现PrgRom和ChrRom

3. **阶段3：扩展功能** (待开始)
   - 实现CartridgeFactory和加载器
   - 实现SaveRam和持久化

4. **阶段4：与映射器集成** (待开始)
   - 实现与`nes-mappers`包的集成接口
   - 处理卡带数据到映射器的传递

5. **阶段5：完善和测试** (进行中)
   - 添加完整测试
   - 性能优化

## 与映射器模块的交互

卡带模块(`nes-cartridge`)与映射器模块(`nes-mappers`)的交互设计：

1. **依赖关系**：
   - 卡带模块依赖映射器模块的接口
   - 映射器模块依赖卡带提供的数据(PRG ROM, CHR ROM等)

2. **数据流向**：
   - 卡带模块负责加载ROM文件和头信息解析
   - 卡带模块将原始ROM数据和映射器类型传递给映射器模块
   - 映射器模块处理内存映射并返回正确的数据

3. **接口设计**：
   - 卡带模块提供数据访问接口
   - 映射器模块提供内存映射接口
   - CPU/PPU读写请求通过卡带模块转发给映射器模块
