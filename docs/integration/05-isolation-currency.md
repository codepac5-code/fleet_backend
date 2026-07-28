# 05 · Isolation & Currency (العزل وتعدّد العملات)

نموذج العزل: **قاعدة بيانات لكل دولة** (DB-per-country). البيانات العالميّة مشتركة؛ بيانات الدولة معزولة في شاردها.

## التصنيف
| عالميّ (`global`) | مُشارد (`dynamic` لكل دولة) |
|---|---|
| المستخدمون/الزبائن · `infrastructure_nodes` · العملات · الإعدادات · خطط الاشتراك · قوالب الإشعارات | المكاتب · السائقون · الحجوزات · المحافظ/الدفتر · الإسناد · الأحداث · الإشعارات · المفضّلة · المحادثة · التدقيق |

- العالميّ: الموديل يثبّت `protected $connection = 'global'`.
- المُشارد: الموديل يستخدم `App\Traits\ResolvesTenantConnection` → يوجَّه لـ`dynamic` عند تفعيل شارد، وإلّا للافتراضيّ.

## مصدر الحقيقة الوحيد: `ShardManager`
`App\Http\Core\GeoServices\ShardManager`:
- `resolveFromRequest($req)`: ترتيب الحلّ — رأس `X-Country` (iso2) → `session('active_shard_id')` → أوّل دولة نشطة. آمن ضدّ الاستثناءات (DB غير متاح → null → اتصال افتراضيّ، لا 500).
- `activate($node)`: يهيّئ اتصال `dynamic` من `InfrastructureNode` (host/port/db/user/pass) + يضبط `region` و`shard_currency`.
- `currency()`: عملة الشارد النشط (من `infrastructure_nodes.currency_code`)، fallback `USD`.

الوسيط `tenant-shard` (`ResolveTenantShard`) يطبّق ذلك على مسارات `api/v1`. كل طلب موبايل مُشارد يرسل `X-Country`.

> الوسطاء القديمون المُصلّبون (`SetCountryDatabase` بكلمات مرور في الكود، `MultipleDatabases`، `SetDatabaseByDialCode`) **متجاوَزون** — لا تبنِ عليهم.

## تعدّد العملات
كل عقدة دولة تحمل `currency_code` (+`currency_symbol`). الدفتر يخزّن `currency_code` لكل قيد، فالمحفظة/التسوية متعدّدة العملات أصلاً. الـAPI يستخدم `ShardManager::currency()` عملةً افتراضيّة حين لا تُرسَل.

> العقد مُثبَت في `tests/Feature/Fleet/IsolationRoutingTest.php` و`ShardResolutionTest.php`.

## أمان: لا ربط ضمنيّ بدولة خاطئة
`resolveFromRequest` لا يرجع إلى «أوّل دولة» عند غياب `X-Country` (كان تسريباً محتملاً عبر الدول بعد الفصل الفعليّ). بلا رأس ولا جلسة → **لا تفعيل شارد** (يبقى على الافتراضيّ/العالميّ). لذا تطبيقات الموبايل **يجب** أن ترسل `X-Country` للموارد المُشاردة.

## تهيئة شارد دولة (أمر تشغيليّ)
```
php artisan fleet:shard-provision qa        # بالرمز ISO2
php artisan fleet:shard-provision --id=5     # بمعرّف العقدة
php artisan fleet:shard-provision --all      # كل الدول النشطة
```
يهيّئ اتصال الشارد ويُشغّل الترحيلات على قاعدته المخصّصة (idempotent — Laravel يتتبّع المُطبَّق لكل شارد). الجداول العالميّة تبقى في `global`.

## قائمة الفصل الفعليّ (عند فصل قواعد البيانات لكل دولة)
الآن كل الشاردات تشير إلى قاعدة `fleet` نفسها (التوجيه صحيح، الفصل الفيزيائيّ لم يتمّ بعد). للفصل:
1. أنشئ عقدة الدولة ثمّ `php artisan fleet:shard-provision <iso2>` (يُشغّل الترحيلات على قاعدتها).
2. حدّث صفوف `infrastructure_nodes` بـ`db_host/db_name/db_user/db_pass` المميّزة + `currency_code`.
3. تأكّد أنّ تطبيقات الموبايل ترسل `X-Country` — مسارات `api/v1` **و** مسارات الموبايل القديمة (`user.php`/`driver.php`) كلّها الآن تحلّ الشارد عبر `tenant-shard`.
4. صنّف النماذج القديمة غير المصنّفة قبل الفصل: `WalletTransaction`/`UserWallet`/`Payment`/`DriverDocument` (تبدو مُشاردة) · `Service`/`SubService`/`PaymentMethod`/`Coupon` (قرار: كتالوج عالميّ أم لكل دولة). حاليّاً كلّها على الافتراضيّ (`global`).
5. **لا JOIN عبر القواعد**: الحجوزات (شارد) تشير إلى `userId` (عالميّ) — استخدم علاقات Eloquent (استعلامات منفصلة) لا `join()` SQL.

> المستخدمون عامّون: `User` لا يستخدم سمة الشارد (معطّلة عمداً) والافتراضيّ `global` → جدول مستخدمين مشترك واحد عبر كل الدول.
