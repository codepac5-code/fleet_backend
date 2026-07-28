# 04 · Realtime & Notifications (الأحداث الحيّة والإشعارات)

يربط `OutboxRelay` (بثّ مرّة واحدة) + `NotificationService` (إشعار DB + FCM + قوالب). كل حدث يُكتب في **نفس معاملة** تغيير الحالة (transactional outbox) → لا يُفقد ولا يُكرّر.

## القنوات (channel = المتلقّي)
```
user.{id}     driver.{id}     office.{id}     booking.{id}
```
التطبيق يشترك بقناته (مثلاً DriverX على `driver.9`)؛ الراكب على `user.7` و`booking.5001`.

## الاشتراك المؤمَّن (handshake)
يتّصل التطبيق ببوّابة الـrealtime ومعه توكن Passport، ثمّ يطلب الاشتراك بقناة. البوّابة **تتحقّق من ملكيّة القناة** عبر Laravel قبل الانضمام — لا اشتراك مفتوحاً.
```js
const socket = io(GATEWAY_URL, { auth: { token: '<passport-access-token>' } });
socket.emit('subscribe', 'driver.9', (res) => {
  // res.authorized === true  → انضممت؛ false → رُفض
});
socket.on('driver.9:dispatch.offer_created', (data) => { /* ... */ });
```
نقطة التفويض (خادميّة، تستدعيها البوّابة لا التطبيق):
```
POST /realtime/authorize        Authorization: Bearer <passport-token>
{ "channel": "driver.9" }   →   200 { "authorized": true, "identity": { "type":"driver", "id":9 } }
```
القواعد: `user.{id}`/`driver.{id}` تُقبَل فقط لمالكها؛ `booking.{id}` لطرفَي الحجز (الراكب/السائق المُسنَد)؛ `office.{id}` وغيرها تُرفض لتوكنات الموبايل.

## أنواع الأحداث (event types)
| type | يُبثّ على | متى |
|---|---|---|
| `dispatch.offer_created` | `driver.{id}` | عرض رحلة لسائق |
| `dispatch.ride_assigned` | `booking · driver · office` | إسناد رحلة ذرّيّاً |
| `dispatch.offer_expired` | `driver.{id}` | انتهاء عرض |
| `presence.changed` | `office.{id}` | تغيّر حالة سائق |
| `wallet.credited` | `user.{id}` | شحن محفظة |
| `payment.succeeded` | `user.{id}` | نجاح دفع |
| `ride.released` | `booking · driver · office` | تحرير ماليّ ثلاثيّ |

شكل الرسالة المبثوثة:
```json
{ "channel": "driver.9", "type": "dispatch.ride_assigned",
  "payload": { "booking_id": 5001, "driver_id": 9, "office_id": 3 } }
```

## تسجيل جهاز (FCM)
```
POST /api/v1/devices
```
```json
{ "owner_type": "driver", "owner_id": 9, "token": "fcm-...", "platform": "android" }
```

## الإشعارات (in-app)
```
GET  /api/v1/notifications?cursor=..&limit=20      → قائمة إشعارات المستخدم
POST /api/v1/notifications/{id}/read               → تعليم كمقروء
```
عنصر إشعار:
```json
{ "id": 12, "type": "dispatch.ride_assigned", "locale": "ar",
  "title": "تم إسناد رحلة جديدة", "body": "تم إسنادك للحجز رقم #5001. توجّه لنقطة الالتقاط.",
  "data": { "booking_id": 5001 }, "read_at": null }
```

## القوالب (إدارة — لوحة المكتب/المشرف)
متعدّدة اللغات (`en`/`ar`)، مخزّنة عالميّاً وتتجاوز الكتالوج الافتراضيّ.
```
GET/PUT /api/v1/notification-templates/{key}
```
```json
{ "key": "wallet_credited", "channels": ["inapp","push"],
  "subject_i18n": { "en": "Wallet topped up", "ar": "تم شحن المحفظة" },
  "body_i18n": { "en": "Your wallet was credited with {amount}.", "ar": "تم إضافة {amount} إلى محفظتك." } }
```

## سيناريوهات
**أ) إسناد رحلة:** قبول السائق → حدث `dispatch.ride_assigned` في outbox ذرّيّاً → relay يبثّه على 3 قنوات **و** يُنشئ إشعار DB + FCM للسائق. إعادة التشغيل لا تكرّر. ✅

**ب) فشل النقل:** سقوط الـsocket لحظتها → الحدث يبقى pending ويُعاد بثّه لاحقاً (retry/backoff) ثم failed بعد الحدّ الأقصى — لا فقدان صامت.

**ج) rollback:** فشل العمل التجاريّ → لا حدث وهميّ يُبثّ (الحدث في نفس المعاملة). ✅

**د) تعدّد اللغات:** `Accept-Language: ar` → الإشعار يُرسَل بالعربيّة من القالب.

> أُثبتت في `tests/Feature/Fleet/EventOutboxTest.php` و`NotificationTest.php`.

## النواقل الفعليّة (للتشغيل)
| الطبقة | الناقل | الملاحظة |
|---|---|---|
| الأحداث الحيّة | `RedisEventPublisher` → Redis (`pubsub`, قناة `rt:{channel}`) → بوّابة `realtime-gateway/` → Socket.io | الرسالة `{event, data, socket:false}`؛ المفاتيح الداخليّة (`_*`) تُجرّد |
| الدفع | `FcmPushSender` → FCM HTTP v1 | يعيد استخدام مسار اعتماد `getAccessToken()` + `project_id` من `OTHER_SETTING`؛ الفشل لا يكسر الحدث |
| البريد | `LaravelMailSender` (قناة `email`) | يُرسَل حين تتضمّن القناةُ `email` ويتوفّر `_email` في الحمولة |

البوّابة المخصّصة `realtime-gateway/server.js` (منفصلة عن `server.js` القديم): تشترك بنمط `rt:*` فقط، تستقبل من الخادم فحسب (لا بثّ من العميل)، وتفرض التفويض على كل اشتراك. تُشغَّل تحت supervisor/pm2.

دلالة التسليم: **socket = أفضل-جهد** (Redis pub/sub fire-and-forget — يُسقَط إن لم يكن العميل متّصلاً)؛ **DB-notification + FCM = مضمون**. لا تعتمد على الـsocket وحده لشيء حرج.

تصريف الـoutbox لكل شارد (يلزم supervisor):
```
php artisan fleet:events-relay                 # تمريرة واحدة (مناسب للمجدول)
php artisan fleet:events-relay --daemon --sleep=2
```
> الربط في `FleetTransportServiceProvider`؛ شكل الرسالة مُثبَت في `tests/Feature/Fleet/TransportTest.php`؛ منطق التفويض في `ChannelAuthorizerTest.php`.
