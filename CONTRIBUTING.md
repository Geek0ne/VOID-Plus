# 贡献指南

感谢你愿意为 VOID-Plus 出力。本文件说明本地开发方式、代码风格与提交流程。

## 环境要求

| 组件 | 版本 | 用途 |
|---|---|---|
| Node.js | 16 及以上 | 构建前端资源（gulp 4） |
| npm | 8 及以上 | 依赖管理 |
| PHP | 7.0 – 8.4 | 本地语法检查与调试 |

主题本身是纯 PHP + 前端静态资源，**运行站点不依赖 Node**，Node 只在改样式或脚本时用得上。

## 起步

```bash
git clone https://github.com/Geek0ne/VOID-Plus.git
cd VOID-Plus
npm install
```

## 目录结构

```
VOID-Plus/
├── index.php / post.php / page.php / archive.php / Archives.php   页面模板
├── includes/          各页面片段（head、header、footer、banner、404、main…）
├── libs/Utils.php     主题工具类（esc 转义、备案号、协议、建站时间等）
├── assets/
│   ├── VOID-Plus.scss 主样式源文件
│   ├── parts/         样式分块（_article、_header、_footer…）
│   ├── VOID-Plus.js   前端主脚本
│   ├── fonts/         图标字体
│   └── libs/          第三方库（Prism、MathJax、pjax、OwO…）
├── plugin/VOIDPlus/   配套插件（随发行包一起发布）
├── .github/workflows/ CI 与发版流程
└── .github/scripts/   一致性校验脚本
```

## 构建

```bash
npm run build     # 完整构建，产物输出到 build/
npm run watch     # 开发模式：合并第三方依赖到 assets/
npm run sass      # 监听 SCSS 改动并编译到 assets/
npm run lint      # ESLint 检查 assets/ 下的脚本
```

**样式改动的硬性要求**：SCSS 源码在 `assets/VOID-Plus.scss` 与 `assets/parts/`，而站点真正加载的是编译产物
`assets/VOID-Plus.css`。改样式后必须重新编译并把 `assets/VOID-Plus.css` 一并提交，否则新样式不生效，
CI 的 `consistency` job 也会因此失败。

## 代码风格

**PHP**

- 4 空格缩进，`<?php` 开头，文件末尾不写闭合标签
- 所有来自用户的字段（`$this->fields->xxx`、`$this->title` 之外的输入）输出前必须经 `Utils::esc()` 转义
- 数字字段一律 `(int)` 强转，不要依赖 PHP 的隐式转换
- 新增设置项集中放进 `functions.php` 的 `themeConfig()`

**JavaScript**

- 4 空格缩进、单引号、分号结尾
- 保持 ES5 语法（主题面向较老的浏览器，不使用箭头函数、`let`/`const` 之外的新语法）
- 新增前端依赖优先用仓库内已有的库，不要引 CDN 脚本进主题

**CSS / SCSS**

- 颜色、宽度等一律用 `assets/parts/_var.scss` 里的变量，不要写死色值
- 暗色模式适配用 `.theme-dark &` 的写法

## 提交信息

标题写**一句话**，说清这次修了什么、加了什么、优化了什么即可，不要罗列流水账。

```
fix: 页脚陪伴计时与服务端相差一个时区偏移
feat: 后台新增建站时间设置
```

详细的改动清单写进正文或更新日志（`change-log.md`），不要塞进标题。

## 分支

| 前缀 | 用途 |
|---|---|
| `feature/` | 新功能 |
| `fix/` | 缺陷修复 |
| `docs/` | 文档 |
| `chore/` | 工程维护 |

改完提 PR 到 `master`，CI 会自动跑四项检查。

## 本地自查

提交前建议先跑一遍仓库自带的一致性校验：

```bash
python3 .github/scripts/check_consistency.py
```

它会检查三件事：

1. `functions.php`、`index.php`、`package.json` 三处版本号是否一致
2. `README.md` 与 `change-log.md` 的更新日志是否逐字一致
3. `assets/VOID-Plus.scss` 里的选择器是否都出现在 `assets/VOID-Plus.css` 中

另外请确保 PHP 文件能通过语法检查：

```bash
find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
```

## 发版

发版由维护者执行，流程固定为：改版本号三处 → 定稿更新日志（去掉「开发中」并填日期）→
新建 `RELEASE-NOTES-vX.Y.Z.md` → 打 `vX.Y.Z` 标签 → CI 自动打包并创建 Release → 下载回验压缩包。

版本号遵循语义化版本，从 3.5.7 起严格执行：

| 档 | 形如 | 判据 | 用户升级动作 |
|---|---|---|---|
| MAJOR | 4.0.0 | 有破坏性变更 | 改目录名 / 改数据库 / 重填设置 |
| MINOR | 3.6.0 | 只加新功能，向后兼容 | 覆盖目录即可，配置全保留 |
| PATCH | 3.5.8 | 只修 bug | 覆盖目录即可 |

## 致谢

本主题基于 [VOID](https://github.com/AlanDecode/Typecho-Theme-VOID)（作者 熊猫小A）二次开发，
主题设计归原作者所有，详见 [README](README.md) 的鸣谢部分。
