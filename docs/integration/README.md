# FleetOS — Integration Info (معلومات الربط)

عقود الـAPIs النهائيّة لتطبيقي **Fleet Ride** (الراكب) و**DriverX** (السائق) ولوحة **Fleet Office**، بطلباتها وردودها وسيناريوهاتها. كل عقد هنا مبنيّ على طبقة خدمات **منجزة ومختبَرة** (`tests/Feature/Fleet` — 53 اختباراً · 154 تأكيداً).

> الحالة: طبقة الخدمة مختبَرة تكامليّاً، **وطبقة HTTP الرقيقة (`api/v1`) مبنيّة ومربوطة** بهذه الخدمات (21 مساراً). الوثائق هي العقد الذي تلتزم به التطبيقات.

## الملفّات
| # | المجال | الخدمة الأساس |
|---|---|---|
| [00](00-conventions.md) | الاصطلاحات (مصادقة · رؤوس · مال · idempotency · أخطاء) | — |
| [01](01-wallet-payments.md) | المحفظة والمدفوعات و webhooks والاسترجاع | `PaymentService` · `FleetWalletService` |
| [02](02-subscriptions-commission.md) | خطط الاشتراك وعمولة المكاتب | `OfficeSubscriptionService` · `CommissionResolver` |
| [03](03-dispatch.md) | الحضور · إنشاء وظيفة · المرشّحون · العروض · القبول الذرّيّ | `DispatchService` |
| [04](04-realtime-notifications.md) | القنوات الحيّة · أنواع الأحداث · الأجهزة · الإشعارات · البوّابة المؤمَّنة | `OutboxRelay` · `NotificationService` |
| [05](05-isolation-currency.md) | العزل DB-لكل-دولة · `ShardManager` · `X-Country` · تعدّد العملات | `ShardManager` |
| [06](06-marketplace-modules.md) | المفضّلة · المحادثة الحيّة · التقارير · التدقيق | `FavoriteOfficeService` · `ChatService` · `ReportService` · `AuditLogService` |
| [07](07-panel-admin.md) | لوحة Office/Admin: اشتراكات · تقارير · محادثة المكتب (حراسة session) | الخدمات نفسها |
| [08](08-subscription-billing.md) | فوترة حسب الدولة · تجربة مجانية · تسجيل ذاتي · Stripe متكرّر · webhooks | `RegionBilling` · `SubscriptionBillingService` · `SubscriptionWebhookService` |

## المبادئ العابرة
- **المال** بوحدات صغرى صحيحة (`*_minor`) + `currency_code` دائماً — لا أرقام عشريّة عائمة.
- **idempotency_key** إلزاميّ على كل عمليّة تغيّر مالاً/إسناداً — التكرار آمن (لا ازدواج).
- **عالميّ مقابل شارد:** المستخدمون/الخطط/القوالب عالميّة؛ المكاتب/السائقون/الرحلات/المحافظ لكل دولة (يُحدَّد بالرأس `X-Country`).
- **القناة = المتلقّي:** `user.{id}` · `driver.{id}` · `office.{id}` · `booking.{id}`.
