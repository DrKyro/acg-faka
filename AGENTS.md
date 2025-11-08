# Repository Guidelines

## 项目结构与模块组织
- 核心业务位于 `app/`，其中 `Controller/` 承载路由入口、`Service/` 聚合业务规则、`Model/` 访问数据库、`Pay/` 集成渠道，所有命名遵循 PSR-4 映射（见 `composer.json`）。
- 框架启动与通用中间件置于 `kernel/`，配置文件集中在 `config/`，静态资源与前端模板分别进入 `assets/` 与 `app/View/`，而数据库脚本、示例数据位于 `database/`。
- 运行状态、缓存与日志写入 `runtime/`、`cache/`、`logs/`，请勿手动提交；`spec/` 保存 API、流程与前端交互说明，更新协议时同步维护。

## 构建、测试与开发命令
- `composer install --no-dev --optimize-autoloader`：初始化或更新 Composer 依赖，保持生产镜像体积可控。
- `./start.sh init` / `./start.sh start`：在本机检查 Docker 依赖、准备可写目录并以 `docker-compose` 启动 PHP-FPM、Nginx、MySQL、Redis、phpMyAdmin，默认 Web 暴露 `http://localhost:8088`。
- `./start.sh stop`、`./start.sh logs`、`./start.sh build`：分别停止服务、跟随容器日志以及重建镜像；排障时可附加 `docker-compose ps` 观察健康状态。

## 编码风格与命名规范
- PHP 文件统一 `declare(strict_types=1);`，使用四空格缩进、PSR-12 花括号折行；控制器用 `PascalCase`，方法、变量使用 `camelCase`，配置键与数据库字段保持 `snake_case`。
- 复用常量放入 `app/Consts`，复合查询写入 Repository/Model 中，避免在控制器内直接拼接 SQL；模板中尽量调用 View 层方法而非原始全局函数。
- 变更需要伴随类型提示与 `@throws` 注释，便于内置注解（如 `Kernel\Annotation`）做静态检查。

## 测试指引
- 现阶段未内置自动化测试框架，新增功能需至少补充 `spec/api` 或 `spec/frontend` 中的设计用例，确保接口参数、响应示例与实际实现同步。
- 建议为 Service/Pay 层引入 PHPUnit：在容器内执行 `docker-compose exec app ./vendor/bin/phpunit --testsuite unit`（可在 `composer.json` 的 scripts 节点新增别名），并对支付、订单、库存等关键场景保持>80% 覆盖率。
- 端到端场景可借助 Postman Collection 或 `spec/phases` 的时序文档，提交前附带重放步骤与必要的 `curl` 示例。

## 提交与 Pull Request 规范
- Git 提交仿照历史记录使用 emoji + 简短中文标题，例如 `📚 文档整合和项目结构优化`；正文列出动机、主要变更点与潜在风险。
- Pull Request 需包含：变更概要、测试结果（命令输出或截图）、关联 Issue/需求单链接、必要的 UI 截图或 API 响应示例；若涉及配置或脚本，请说明兼容性和回滚方式。
- 在触及支付、通知、插件扩展时，添加 `Security Impact` 小节，说明密钥、回调 URL、权限的默认值，避免误将敏感信息提交到版本库。

## 安全与配置加固
- 所有敏感配置写入 `.env` 或 `config/*.php` 中的占位符，使用 `docker-compose.override.yml` 为本地注入真实凭据；不要提交生产密钥、证书或数据库快照。
- 新增上传目录时务必同步更新 `config/nginx/default.conf` 以限制可执行文件扩展名，并在 `start.sh` 中追加权限初始化；运行中产生的 `upload/`、`runtime.log` 可在 PR 描述里提醒评审者如何复现。
- 如果引入第三方支付或短信通道，遵循最小权限原则，更新 `kernel/Interceptor` 以增加 session 校验，并在文档写明可审计的回调事件。
