# 币种管理系统

一个基于PHP的现代化币种管理系统，支持管理后台和用户端功能。

## 功能特性

### 管理后台
- 🪙 币种管理：添加、编辑、删除币种
- 🖼️ 图标上传：支持SVG/PNG格式
- 📝 JSON模板：支持占位符变量
- ⚙️ 状态控制：启用/禁用开关
- 📊 数据监控：查看用户提交数据

### 用户系统
- 👤 用户注册/登录：图形验证码验证
- 🔒 密码策略：大小写字母、数字、特殊字符
- 📋 参数配置：动态表单生成
- 🎯 模板替换：自动处理占位符

## 技术栈

- **后端**: PHP 7.4+
- **数据库**: MySQL 5.7+
- **前端**: Bootstrap 5, Font Awesome
- **服务器**: Apache/Nginx

## 安装部署

### 环境要求
- PHP 7.4 或更高版本
- MySQL 5.7 或更高版本
- Apache/Nginx Web服务器
- 支持宝塔面板/小皮面板

### 安装步骤

1. **上传文件**
   ```bash
   # 将项目文件上传到网站根目录
   ```

2. **配置数据库**
   - 创建数据库 `coin_management`
   - 修改 `config/database.php` 中的数据库连接信息

3. **设置权限**
   ```bash
   chmod 755 uploads/
   chmod 644 .htaccess
   ```

4. **访问系统**
   - 前台：`http://your-domain.com/`
   - 后台：`http://your-domain.com/index.php?page=admin`
   - 默认管理员：`admin` / `admin123`

## 使用说明

### 管理员操作

1. **登录后台**
   - 使用默认账户登录管理后台

2. **添加币种**
   - 点击"添加币种"按钮
   - 填写币种名称、上传图标
   - 设置JSON模板（如：`{"wallet": "{{WALLET}}", "amount": "{{AMOUNT}}"}`）
   - 选择启用状态

3. **管理用户数据**
   - 在"用户数据"页面查看所有提交记录
   - 按币种筛选查看数据

### 用户操作

1. **注册账户**
   - 填写用户名、邮箱、密码
   - 密码需符合安全要求

2. **配置币种**
   - 选择要配置的币种
   - 填写相关参数
   - 提交后查看处理结果

## 文件结构

```
├── index.php              # 主入口文件
├── config/
│   └── database.php       # 数据库配置
├── includes/
│   └── functions.php      # 通用函数
├── admin/
│   └── dashboard.php      # 管理后台
├── pages/
│   ├── home.php          # 首页
│   ├── login.php         # 登录页
│   ├── register.php      # 注册页
│   ├── coin_form.php     # 币种配置表单
│   ├── submit_coin.php   # 提交处理
│   └── success.php       # 成功页面
├── uploads/              # 上传文件目录
├── .htaccess            # Apache配置
└── README.md            # 说明文档
```

## 安全说明

- 密码使用 `password_hash()` 加密存储
- 支持CSRF令牌验证
- 文件上传类型限制
- SQL注入防护
- XSS攻击防护

## 注意事项

1. 首次使用请修改默认管理员密码
2. 定期备份数据库
3. 确保 `uploads/` 目录可写
4. 建议配置SSL证书

## 技术支持

如有问题请联系技术支持。

---

© 2024 币种管理系统. 保留所有权利.
