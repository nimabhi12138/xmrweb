# 币种管理系统 - 部署说明

## 系统概述
基于PHP开发的币种管理系统，包含管理后台和用户端，具有科技感的界面设计和流畅的交互体验。

## 系统要求
- PHP 7.4 或更高版本
- MySQL 5.7 或更高版本
- 支持 PDO 扩展
- 支持 GD 库（用于验证码生成）
- 支持文件上传

## 部署步骤

### 1. 宝塔面板部署

1. **创建站点**
   - 登录宝塔面板
   - 点击"网站" -> "添加站点"
   - 填写域名和根目录
   - 选择PHP版本（建议7.4或更高）
   - 创建MySQL数据库

2. **上传文件**
   - 将整个 `crypto-management-system` 文件夹上传到网站根目录
   - 确保 `assets/uploads` 目录有写入权限（755或777）

3. **导入数据库**
   - 在宝塔面板中找到创建的数据库
   - 点击"管理"进入phpMyAdmin
   - 导入 `database/database.sql` 文件

4. **配置数据库连接**
   - 编辑 `includes/config.php` 文件
   - 修改以下配置：
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', '你的数据库名');
   define('DB_USER', '数据库用户名');
   define('DB_PASS', '数据库密码');
   ```

5. **配置站点URL**
   - 编辑 `includes/config.php` 文件
   - 修改 `SITE_URL` 为你的实际域名：
   ```php
   define('SITE_URL', 'http://你的域名/');
   ```

### 2. 小皮面板（PHPStudy）部署

1. **创建站点**
   - 打开小皮面板
   - 点击"创建网站"
   - 设置域名和根目录
   - 选择PHP版本（7.4或更高）

2. **创建数据库**
   - 在小皮面板中点击"数据库"
   - 创建新数据库
   - 记录数据库名、用户名和密码

3. **上传文件**
   - 将 `crypto-management-system` 文件夹复制到站点根目录
   - 确保 `assets/uploads` 目录有写入权限

4. **导入数据库**
   - 打开phpMyAdmin（通常是 http://localhost/phpmyadmin）
   - 选择创建的数据库
   - 导入 `database/database.sql` 文件

5. **配置文件**
   - 同宝塔面板步骤4和5

### 3. 手动部署（XAMPP/WAMP等）

1. **环境准备**
   - 确保Apache和MySQL服务已启动
   - PHP版本7.4或更高

2. **部署文件**
   - 将 `crypto-management-system` 文件夹复制到 `htdocs` 或 `www` 目录

3. **创建数据库**
   - 访问 phpMyAdmin
   - 创建名为 `crypto_management` 的数据库
   - 导入 `database/database.sql` 文件

4. **配置文件**
   - 编辑 `includes/config.php` 配置数据库连接

## 默认账户

### 管理员账户
- 用户名：admin
- 密码：Admin@123456

### 测试步骤
1. 访问系统首页（会自动跳转到登录页）
2. 使用管理员账户登录
3. 进入管理后台，添加币种和配置字段
4. 注册普通用户账户
5. 使用普通用户登录，配置币种参数

## 目录结构说明

```
crypto-management-system/
├── admin/              # 管理后台
│   ├── index.php      # 管理后台首页
│   ├── currencies.php # 币种管理
│   └── user-configs.php # 用户配置监控
├── user/              # 用户端
│   ├── login.php      # 登录页面
│   ├── register.php   # 注册页面
│   ├── dashboard.php  # 用户仪表盘
│   └── configure.php  # 币种配置
├── assets/            # 静态资源
│   ├── css/          # 样式文件
│   ├── uploads/      # 上传文件目录（需要写权限）
│   └── js/           # JavaScript文件
├── includes/          # 核心文件
│   ├── config.php    # 配置文件
│   ├── database.php  # 数据库类
│   ├── auth.php      # 认证类
│   └── captcha.php   # 验证码类
├── api/              # API接口
└── database/         # 数据库文件
    └── database.sql  # 数据库结构
```

## 常见问题

### 1. 验证码不显示
- 检查PHP GD库是否安装
- 检查 session 是否正常工作

### 2. 文件上传失败
- 检查 `assets/uploads` 目录权限
- 检查PHP配置中的 `upload_max_filesize` 和 `post_max_size`

### 3. 数据库连接失败
- 确认数据库配置信息正确
- 确认MySQL服务已启动
- 检查数据库用户权限

### 4. 页面样式异常
- 清除浏览器缓存
- 检查CSS文件路径是否正确

## 安全建议

1. **生产环境部署时**：
   - 修改 `includes/config.php` 中的错误报告设置：
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

2. **修改默认管理员密码**：
   - 首次登录后立即修改管理员密码

3. **设置合适的文件权限**：
   - 配置文件：644
   - 上传目录：755
   - 其他PHP文件：644

4. **使用HTTPS**：
   - 建议配置SSL证书，使用HTTPS访问

## 技术支持

如有问题，请检查：
1. PHP版本是否满足要求
2. 必要的PHP扩展是否已启用
3. 数据库连接信息是否正确
4. 文件权限是否设置正确

## 系统特性

- ✅ 科技感界面设计
- ✅ 响应式布局
- ✅ 图形验证码
- ✅ 密码强度验证
- ✅ 币种图标上传（支持PNG/SVG）
- ✅ JSON模板配置
- ✅ 自定义字段管理
- ✅ 用户数据监控
- ✅ 实时状态切换
- ✅ 流畅的交互动画