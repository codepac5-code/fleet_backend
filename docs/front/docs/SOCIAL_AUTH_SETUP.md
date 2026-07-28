# Social Sign-In Setup (Google & Apple)

How to take the scaffolded Google/Apple sign-in from the **offline mock** to a **live** flow.

## Architecture (already wired)

```
LoginScreen ─▶ AuthController.signInWithProvider(provider)
                 │  ├─ SocialAuthService.signIn(provider)  → SocialCredential {idToken, code?, email?, name?}
                 │  │     • MockSocialAuthService   (useMock=true — offline, no native config)
                 │  │     • RealSocialAuthService   (useMock=false — google_sign_in + sign_in_with_apple)
                 │  └─ AuthRepository.socialSignIn(...)     → POST /auth/social → AuthSession
                 └─ SessionController.setTokens(...)        (persists the session)
```

- **Selection is automatic:** `socialAuthServiceProvider` returns the mock while `apiConfigProvider.useMock`
  is true, and `RealSocialAuthService` on a live build (`--dart-define=LIVE=true`).
- **Packages:** `google_sign_in: ^6.2.1`, `sign_in_with_apple: ^6.1.4` (in `pubspec.yaml`).

## What you must provide

| Item | Where it goes | Notes |
|---|---|---|
| Google **web/server** client id | `--dart-define=GOOGLE_SERVER_CLIENT_ID=…` → `AppEnv.googleServerClientId` → `googleServerClientIdProvider` | Token audience the backend verifies. Required for an `idToken` on Android. |
| Google **iOS** client id (reversed) | `ios/Runner/Info.plist` → `CFBundleURLTypes` (replace `REVERSED_CLIENT_ID`) | From the iOS OAuth client / `GoogleService-Info.plist` (`REVERSED_CLIENT_ID`). |
| Google **Android** SHA-1/SHA-256 | Google Cloud / Firebase console (Android OAuth client) | Register debug + release signing certs. No manifest change needed. |
| Apple **App ID** capability | Apple Developer portal → App ID → enable "Sign in with Apple" | Bundle id `com.codepac.fleetapp`. |
| Apple **Service ID** + key | Apple Developer portal (for the backend token verification / Android web flow) | Backend needs the Apple public keys to verify `identityToken`. |

## iOS (done in the repo)

- ✅ `ios/Runner/Runner.entitlements` created with `com.apple.developer.applesignin = [Default]`.
- ✅ `CODE_SIGN_ENTITLEMENTS = Runner/Runner.entitlements` added to all three Runner build configs
  (Debug/Release/Profile) in `project.pbxproj`.
- ✅ `Info.plist` has a `CFBundleURLTypes` entry for the Google callback.

**Remaining (needs your account, do in Xcode once):**
1. Open `ios/Runner.xcworkspace` → Runner target → **Signing & Capabilities**. Confirm
   **Sign in with Apple** appears (it reads the entitlement). If signing complains, let Xcode
   register the capability on your App ID.
2. Replace `REVERSED_CLIENT_ID` in `Info.plist` with your real reversed iOS client id.
3. `cd ios && pod install` (the plugins add pods).

## Android

- **Google:** no manifest change. Provide `GOOGLE_SERVER_CLIENT_ID` and register the app's SHA-1/-256
  in the Google console. That's enough for `idToken`.
- **Apple on Android** (optional — Apple sign-in is web-based here): `sign_in_with_apple` needs a
  return-URL `intent-filter` and an Apple **Service ID** + redirect configured to bounce back to the
  app. Skip unless you need Apple login on Android; iOS is the primary surface.

## Backend — `POST /auth/social`

The app sends:

```jsonc
{
  "provider": "google" | "apple",
  "idToken": "<provider OIDC id token>",
  "authorizationCode": "<apple only, single-use>",   // optional
  "email": "…",                                        // optional (first Apple auth only)
  "fullName": "…",                                      // optional (first Apple auth only)
  "country": "QA"
}
```

Backend must: **verify `idToken`** against the provider (Google: audience = server client id;
Apple: Apple public keys + your Service/App id), find-or-create the user, upsert `email`/`fullName`
when present, and return the same `AuthSession` shape as `/auth/otp/verify`
(`{ accessToken, refreshToken, user }`). (Not yet in `openapi.v2.yaml` — add it there.)

## Testing the live flow

```bash
flutter run --dart-define=LIVE=true \
  --dart-define=API_BASE_URL=https://<host>/v1 \
  --dart-define=GOOGLE_SERVER_CLIENT_ID=<web-client-id>.apps.googleusercontent.com
```

With `LIVE` unset the app keeps using `MockSocialAuthService`, so the buttons work offline for demos.
