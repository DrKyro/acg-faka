# ACG Faka AI 实战速记

## 项目结构速览
- **app/**：业务核心，Controller 负责路由入口，Service 聚合业务逻辑，Model 直连数据库，Pay 负责渠道对接；所有命名遵循 PSR-4，入口参见 `composer.json`。
- **kernel/**：框架启动、中间件与补丁脚本（如 `app/Controller/Admin/Patch.php`）。
- **config/**、**database/**、**assets/**、**app/View/** 分别存放配置、脚本、静态资源和模板。
- **runtime/**、**cache/**、**logs/** 用于运行时数据，默认不提交。

## API 发货存储与协议差异
| 协议类型 | type 值 | 凭证字段 | 主要实现 | 说明 |
| --- | --- | --- | --- | --- |
| 内置共享接口 | 0 | `app_id/app_key` | `Shared::post()` | 走旧版 `/shared/...` 接口。 |
| MCY V4 | 1 | `app_id/app_key` | `Shared::mcyRequest()` | 商户号 + 签名，支持 SKU/库存。 |
| ACG API 发卡 | 2 | `cookie` + `app_id`(Base64) + `app_key` | `Shared::acgRequestRaw()` | `cookie` 保存明文授权，`app_id` 仅做兼容影子值。`app_key` 通常用作 X-CSRF/CAPTCHA。 |

`app/Controller/Admin/Api/Store.php` 保存店铺时会优先读取表单中的 `cookie` 字段，若为空再回退解码 `app_id`。`app/Service/Bind/Shared.php` 的 `resolveAcgCookie()` 在所有 ACG 请求里复用，确保数据库旧值也能正常发货。

## 共享店铺补丁实践
1. **扩容凭证字段**：`/admin/patch/updateSharedCredentialFields` 把 `shared.app_id` 扩到 `varchar(1024)`、`app_key` 扩到 `varchar(255)`，防止长 Cookie 被截断。
2. **新增 Cookie 列并迁移**：`/admin/patch/updateSharedCookieField` 如果不存在 `shared.cookie` 就新增，并把 type=2 的旧数据从 `app_id` 复制过去。
3. **前端提交流程**：`assets/admin/controller/shared/store.js` 在 type=2 时只把编码后的 Cookie 存入 `app_id`，明文写入 `cookie`。若编码失败会直接提示。

> 建议每次拉到新版本后，先执行上述补丁 URL，再观察数据库结构，避免因为表结构旧导致授权值截断。

## 常见排障经验
- **“操作被阻断”**：只是前端弹窗标题。真实原因以 `msg` 字段为准，常见为 `连接失败#API`，多半是容器无法解析上游域名。进入容器 `nslookup`/`curl` 核实，再配置 `dns` 或 `/etc/hosts`。
- **无法访问 8088**：确认 `./start.sh start` / `docker compose up -d` 已启动，并用 `docker compose logs -f nginx|app` 查看实时日志。
- **抓日志**：容器模式直接 `docker compose logs -f app`；若是本机 PHP-FPM，请查看 `php.ini` 的 `error_log` 指向。

## 推荐工作流
1. 在本地复现问题 → 先看前端请求体（Network 面板或 `curl -i`），确认 `cookie/app_id` 是否正确提交。
2. 运行补丁确保数据库结构符合新逻辑。
3. 如需调试上游，直接在容器中执行 `php -r 'require "vendor/autoload.php"; (new \App\Service\Bind\Shared())->connect(...)'`，可马上看到具体异常。

## ACG API 发卡对接现状
- **已完成**：`connect/items/item/inventory/getItemStock/trade` 等接口均在 `app/Service/Bind/Shared.php` 内根据 `type=2` 走 `acgRequest()`，新增店铺时前后端也已使用 `cookie`/`resolveAcgCookie()` 确保鉴权字段写入 `shared.cookie`。
- **未覆盖**：`draftCard()/getDraft()` 仍直接抛出 “API发卡协议暂不支持预选卡密”，导致后台、前台的预选卡密流程不可用；若要支持预选，需要补齐上游筛卡/锁卡接口。
- **差异行为**：`connect()` 只探测 `/user/api/index/data` 并返回推测的店铺名、余额恒为 0；如需展示真实余额或其他概况，需要在上游寻找对应接口并扩展返回值。
- **潜在改进**：`inventoryState()` 目前仅依据 `commodityDetail` 的库存数做本地校验，没有类似旧协议那样携带 `card_id/race/sku` 给上游做锁定验证；高并发场景可能需要补充上游的库存锁定接口。

通过以上流程，可以快速定位共享店铺相关问题并保持环境与代码一致。EOF
