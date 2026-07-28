# Background Operation Setup

How the rider app keeps working while backgrounded, and what's left to finish. Three layers:

| # | Capability | Status |
|---|---|---|
| 1 | **Resume cleanly on reopen** — drop socket when idle, reconnect + resync on resume | ✅ **Done** |
| 2 | **Push notifications** when backgrounded/closed (FCM + APNs) | ✅ **Wired** (Firebase project `fleet-bfb36`); iOS needs 3 Xcode/portal steps |
| 3 | **Live trip updates** during an active ride (Android foreground service / iOS location) | 🟡 Native config in place; needs plugin + service |

> **Package id:** the app was renamed to **`com.codepac.fleetapp`** (Android `applicationId` + iOS
> bundle id) to match the Firebase config. The Apple Sign-In App ID must be re-registered for this id.

---

## 1. Lifecycle — done ✅

- `FleetRideApp` is a `WidgetsBindingObserver` (`lib/app.dart`). On `resumed` it calls
  `TripRepository.onAppResumed()` (reconnect + the socket re-subscribes; UI re-snapshots via REST);
  on `paused`/`hidden`/`detached` it calls `onAppBackgrounded()`.
- `onAppBackgrounded()` **drops the socket when idle** (battery) but **keeps it during an active ride**
  (`hasActiveTrip`), so live driver location/status keep flowing (backed by layer 3 below).
- Covered by tests in `test/trip_repository_test.dart` (group `background lifecycle`).

No further work needed.

---

## 2. Push notifications (FCM + APNs) — wired ✅

**In the repo now:**
- Plugins: `firebase_core`, `firebase_messaging`, `flutter_local_notifications` (`pubspec.yaml`).
- `PushService` interface + `MockPushService` (offline default) + `FcmPushService`
  (`lib/core/data/push_service.dart`, `fcm_push_service.dart`), selected by `pushServiceProvider`
  (mock while `useMock`, real FCM on live builds).
- `main()` calls `Firebase.initializeApp()` + registers the background handler **on live builds only**.
- The notification-priming screen calls `pushService.init()` → registers the token via `POST /devices`;
  logout calls `deleteToken()` + `unregisterDevice`.
- Android: Google-Services Gradle plugin applied (`settings.gradle.kts`, `app/build.gradle.kts`),
  `google-services.json` in `android/app/`, `POST_NOTIFICATIONS` permission. **Android is complete.**
- iOS: `GoogleService-Info.plist` copied to `ios/Runner/`, `UIBackgroundModes: remote-notification` set.

**iOS — remaining manual steps (need your Apple account / Xcode):**
1. **Add `GoogleService-Info.plist` to the Runner target** in Xcode (drag it into the Runner group,
   tick "Runner" target). Without this it isn't bundled and `Firebase.initializeApp()` fails at runtime.
2. Enable the **Push Notifications** capability on the Runner target, and upload the **APNs auth key**
   to Firebase (Project settings → Cloud Messaging).
3. `cd ios && pod install` (pulls the Firebase pods).

**Backend:** send FCM/APNs for the same events as the socket (`booking.status_changed`,
`dispatch.ride_assigned`, `notification.created`, …), sharing the notification `id` so the foreground
socket event and the push **dedupe** (see `docs/REALTIME_APP_REQUIREMENTS.md` §3.6).

> Security note: `google-services.json` / `GoogleService-Info.plist` now live in their native
> locations. The copies you added under `docs/` can be deleted — they carry API keys and shouldn't
> be shared.

---

## 3. Live trip in the background 🟡

Keep receiving `driver.location` / `booking.status_changed` while the app is backgrounded **during a
ride** (not after — the socket is dropped when idle by layer 1).

**Native config already applied:**
- Android: `FOREGROUND_SERVICE`, `FOREGROUND_SERVICE_LOCATION`, `ACCESS_FINE_LOCATION`,
  `ACCESS_BACKGROUND_LOCATION` (`AndroidManifest.xml`).
- iOS: `UIBackgroundModes: location` + location usage strings (`Info.plist`).

**Remaining:**
1. Add a foreground-service plugin (e.g. `flutter_foreground_task`).
2. **Start** the service when a ride goes active (`TripRepository.hasActiveTrip` flips true /
   `requestRide`), showing a persistent "Trip in progress" notification; **stop** it on a terminal
   status. The service holds the socket connection so `onAppBackgrounded()` keeps it alive.
3. iOS: the `location` background mode keeps the app running during the trip; start/stop location
   updates alongside the ride so the OS sustains the process. Request "Always" location only when a
   ride needs it.

> Battery note: only run the service **during an active ride**. Layer 1 already guarantees the socket
> is dropped when idle, so there's no always-on drain.

---

## Summary

Layer 1 works today and is tested. Layers 2 & 3 have their **native config wired**; completing them
needs a Firebase project + two plugins (push) and one plugin + a small service (live trip). Both
follow the app's mock-first pattern — add the interface, keep a mock default, drop in the real
implementation behind the same provider.
