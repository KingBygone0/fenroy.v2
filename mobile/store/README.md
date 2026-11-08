# Store submission kit — Fenroy app

```
mobile/store/
  listing.md              names, descriptions, keywords, privacy and data-safety answers, review notes
  screenshots/
    ios-6.9/              1320 × 2868 — App Store, iPhone 6.9" (required)
    ios-6.5/              1284 × 2778 — App Store, iPhone 6.5"
    android-phone/        1080 × 1920 — Google Play phone screenshots
  android/
    icon-512.png          Google Play app icon
    feature-graphic.png   Google Play feature graphic, 1024 × 500
  raw/                    unframed captures (iPhone 17 Pro Max simulator, Android emulator)
  make_store_images.py    frames raw/ into screenshots/ and android/
```

Upload screenshots in file-name order (01 … 05).

## Regenerating

Capture new raw screens (same file names) from the iPhone 17 Pro Max simulator (`xcrun simctl io booted screenshot`) and an Android emulator (`adb exec-out screencap -p`), then from `mobile/`:

```bash
python3 store/make_store_images.py
```

Captions live in `SHOTS` at the top of the script.

## iPhone (App Store Connect)

The App Store icon is `ios/App/App/Assets.xcassets/AppIcon.appiconset`. The privacy manifest is `ios/App/App/PrivacyInfo.xcprivacy` (keep it in step with `listing.md`).

1. Set the signing team: Xcode → App target → Signing & Capabilities → Team (or `DEVELOPMENT_TEAM` in `project.pbxproj`).
2. Raise `CURRENT_PROJECT_VERSION` for every upload; `MARKETING_VERSION` is the version users see.
3. `npx cap sync ios`, then in Xcode: Product → Archive → Distribute App → App Store Connect.
4. Fill in the listing from `listing.md`, including a demo account in the review notes.

## Android (Google Play Console)

Release signing comes from the `ANDROID_KEYSTORE_*` GitHub secrets (see `.github/workflows/mobile-build.yml`); CI passes `versionCode` from the run number. Upload the signed `.aab` from the workflow's `fenroy-android` artifact, and turn on Play App Signing.

Play rejects apps that offer APK downloads, so the site must hide its APK links inside the app. `App\Support\NativeApp` does this (user agent plus a `fenroy_app` cookie, because the service worker's requests lose the app's user agent on Android); keep any new APK link behind `NativeApp::detected()`.
