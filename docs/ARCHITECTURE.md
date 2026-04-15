# GOGO-Admin 架构文档

> **生成时间:** 2026-04-15  
> **技术栈:** ThinkPHP 5.0 / PHP 7.2  
> **服务端口:** 8002  
> **访问地址:** https://boss.gogo198.cn  
> **数据来源:** 服务器真实代码扫描  

---

## 📊 项目概览

| 属性 | 值 |
|------|-----|
| **项目名称** | GOGO-Admin（GOGO总后台管理平台） |
| **技术框架** | ThinkPHP 5.0 |
| **PHP版本** | 7.2 |
| **数据库** | MySQL |
| **部署方式** | Git直接部署 |
| **CI/CD** | GitHub Actions（智能6阶段流程） |
| **路由总数** | 410 条路由规则 |

---

## 📁 目录结构

```
boss.gogo198.cn/
│
├── 📂 application/                    # ThinkPHP应用核心 ⭐
│   ├── 📂 index/
│   │   ├── 📂 controller/             # 前台控制器（11个）
│   │   │   ├── Customer.php           # 客户管理
│   │   │   ├── Gather.php             # 数据采集管理
│   │   │   ├── Index.php              # 首页/信息展示
│   │   │   ├── Loggin.php             # 日志管理
│   │   │   ├── Main.php               # 主控制器/决策网
│   │   │   ├── Memberc.php            # 企业会员管理
│   │   │   ├── Member.php             # 会员管理
│   │   │   ├── Members.php            # 会员列表
│   │   │   ├── Merchant.php           # 商户管理（首页路由）
│   │   │   ├── Monitor.php            # 系统监控面板 ⭐
│   │   │   └── Shop.php               # 店铺管理
│   │   └── 📂 model/
│   │       └── Parceltask.php         # 包裹任务模型
│   ├── 📂 api/
│   │   └── 📂 controller/
│   │       ├── Account.php            # 账户API接口
│   │       └── Chatgpt.php            # ChatGPT集成API
│   ├── config.php                     # 应用配置
│   ├── common.php                     # 公共函数库
│   ├── route.php                      # 路由配置（410条）
│   ├── tags.php                       # 钩子配置
│   └── database.php                   # 数据库配置
│
├── 📂 api/                            # 独立API目录
├── 📂 framework/                      # 框架组件
├── 📂 TPFrameWork/                    # TP框架扩展
├── 📂 addons/                         # 插件目录
├── 📂 payment/                        # 支付模块
├── 📂 web/                            # Web前端资源
├── 📂 store/app/themes/default/       # 前端主题模板
├── 📂 crontab/                        # 定时任务脚本
├── 📂 sendMsg/                        # 消息推送模块
├── 📂 collect_website/                # 网站数据采集
├── 📂 python_code/                    # Python辅助脚本
├── 📂 Public/                         # 公共静态资源
├── 📂 attachment/                     # 文件附件
├── 📂 uploads/                        # 用户上传文件
├── 📂 data/                           # 数据目录
├── 📂 pcsite/                         # PC端站点
├── 📂 foll/                           # 关注/粉丝功能
├── 📂 vendor/                         # Composer依赖包
│
├── 📄 index.php                       # 主入口文件
├── 📄 api.php                         # API入口文件
├── 📄 batch_push.py                   # Python批量推送
├── 📄 sendWechatMessage.php           # 微信消息发送
├── 📄 testApi.go                      # Go语言API测试
├── 📄 transfer.php                    # 转账功能脚本
├── 📄 Dockerfile                      # Docker构建配置
├── 📄 docker-compose.yml              # Docker编排配置
└── 📄 composer.json                   # PHP依赖配置
```

---

## 🎮 控制器详解

### 前台控制器 (`application/index/controller/`)

| 控制器 | 功能说明 | 主要方法 |
|--------|----------|----------|
| **Merchant** | 商户管理（网站首页入口 `/`） | index, list, detail |
| **Main** | 主控制器 / 决策网模块 | guide_page, disease_detail, production_list, medical_detail, search_info, staff_reg |
| **Index** | 首页/信息展示/客户背景调查 | index, detail, enterprise_news, customers, background_email, background_site, background_company, searchengine, domainsearch, findcustomers |
| **Member** | 个人会员管理 | index, list, detail, edit |
| **Members** | 会员列表查询 | index, search, export |
| **Memberc** | 企业会员管理 | index, list, detail |
| **Customer** | 客户管理 | index, list, detail |
| **Shop** | 店铺管理 | index, list, detail |
| **Gather** | 数据采集管理 | index, list, run |
| **Loggin** | 系统日志管理 | index, list, detail |
| **Monitor** | 系统监控面板 ⭐ | containers, docker_cmd, system_info, crontab, security |

### API控制器 (`application/api/controller/`)

| 控制器 | 功能说明 |
|--------|----------|
| **Account** | 账户注册/登录/认证 |
| **Chatgpt** | ChatGPT AI对话集成 |

---

## 🛤️ API路由文档（真实路由节选）

> **路由文件:** `application/route.php`  
> **总路由数:** 410 条

### 首页路由
| 方法 | 路由 | 控制器 | 说明 |
|------|------|--------|------|
| GET | `/` | `index/merchant/index` | 网站首页（商户入口） |

### 决策网模块（Main控制器）
| 方法 | 路由 | 说明 |
|------|------|------|
| GET | `main/guide_page` | 列表页/代购商城页 |
| GET | `main/disease_detail` | 疾病详情 |
| GET | `main/production_list` | 当前国家生产商列表 |
| GET | `main/medical_detail` | 药品详情 |
| POST | `main/search_info` | 首页查询跳转 |
| ANY | `main/staff_reg` | 人员验证 |

### 首页信息模块（Index控制器）
| 方法 | 路由 | 说明 |
|------|------|------|
| ANY | `index/enterprise_news` | 购购动态 |
| ANY | `index/cross_news` | 跨境新闻 |
| ANY | `index/chooseMarket` | 选市场 |
| ANY | `index/customers` | 找客户 |
| ANY | `index/background_email` | 全球客户背景调查-邮箱 |
| ANY | `index/background_site` | 全球客户背景调查-网站 |
| ANY | `index/background_company` | 全球客户背景调查-企业 |
| ANY | `index/background_searchworld` | 全球客户背景调查-查注册信息 |
| ANY | `index/background_overseasreport` | 全球客户背景调查-信用报告 |
| ANY | `index/searchengine` | 搜索引擎获客 |
| ANY | `index/domainsearch` | 域名获客 |
| ANY | `index/findcustomers` | 海关数据 |
| ANY | `index/enterprise` | 社交媒体获客 |

---

## 📦 数据模型

| 模型 | 文件 | 对应数据表 | 说明 |
|------|------|-----------|------|
| **Parceltask** | `application/index/model/Parceltask.php` | `parcels_tasks` | 包裹任务管理 |

---

## 🔧 核心功能模块

### 1. 系统监控面板 (Monitor.php) ⭐
访问地址：`https://boss.gogo198.cn/?s=monitor`  
登录：`admin / Gogo@198`

- Docker容器实时监控
- 系统资源（CPU/内存/磁盘）
- 安全事件告警
- 端口详情与网络连接
- 定时任务管理
- 阿里云事件通知

### 2. 决策网模块
- 全球药品/疾病数据查询
- 生产商/厂商信息展示
- 医药领域信息检索

### 3. 全球客户背景调查
- 邮箱/网站/企业信息查询
- KYB合规报告
- 海关数据查询
- 搜索引擎/社交媒体获客

### 4. 商户与会员管理
- 多类型会员（个人/企业）
- 商户入驻与管理
- 店铺运营管理

### 5. 消息推送与集成
- 微信消息发送
- Python批量推送
- ChatGPT AI集成

---

## 🛠️ 部署信息

| 项目 | 值 |
|------|-----|
| **服务器** | 阿里云 ECS 39.108.11.214 (CentOS 7) |
| **部署路径** | `/www/wwwroot/boss.gogo198.cn/` |
| **访问地址** | https://boss.gogo198.cn |
| **服务端口** | 8002 |
| **运行用户** | www |
| **备份目录** | `/opt/backups/gogo-admin/` |
| **版本记录** | `.deployed_version` / `.deployment_history` |

---

## 🔐 安全与第三方集成

| 集成 | 文件 | 说明 |
|------|------|------|
| 微信公众平台 | `MP_verify_*.txt` | 域名验证 |
| Google OAuth | `client_secret_*.json` | Google登录 |

---

## 📈 CI/CD 流程状态

| 阶段 | 状态 | 说明 |
|------|------|------|
| 代码审核 | ✅ 已配置 | SonarQube扫描 |
| 架构文档生成 | ✅ 已配置 | 自动生成docs/ARCHITECTURE.md |
| 修复建议 | ✅ 已配置 | GitHub Issue自动创建 |
| 自动修复 | ✅ 已配置 | 创建修复PR |
| 部署 | ✅ 已配置 | SSH直接部署 |
| 邮件通知 | ✅ 已配置 | 发送至198@gogo198.net |

---

*由 GOGO CI/CD 基于服务器真实代码扫描生成 · 2026-04-15*
