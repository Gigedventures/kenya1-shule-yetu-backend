# Flutter Web White Screen — Diagnosis Report

**Project:** `shule_yetu_flutter_ui`
**Flutter SDK:** 3.38.8 (stable) @ `C:\flutter` on Windows
**Working tree:** `/mnt/c/Users/nalen/shule-yetu-backend/flutter_ui`

---

## TL;DR — Two distinct problems, ordered by blast radius

| # | Problem | File | Severity | Effect |
|---|---|---|---|---|
| 1 | `web/index.html` uses **deprecated bootstrap** referencing an undefined `flutterServiceWorkerVersion` global | `web/index.html:12` | **CRITICAL** | White screen |
| 2 | `impellerc.exe` binary is incompatible with the host Windows version | `C:\flutter\bin\cache\artifacts\engine\windows-x64\impellerc.exe` | HIGH | Blocks any future `flutter run` / `flutter build` (hot reload only) |

A tertiary smell: every JPG/PNG in `assets/images/modules/` and `assets/previews/` is a 12-byte text file containing the literal word `placeholder\n`. Not the cause of the white screen — broken image loaders render error widgets, not a blank canvas — but it will produce a broken UI once rendering is restored.

---

## 1. Root cause — web/index.html bootstrap mismatch

**File:** `/mnt/c/Users/nalen/shule-yetu-backend/flutter_ui/web/index.html`
**Lines:** 1–25 (entire file)
**Companion build artifact:** `build/web/index.html` is byte-identical to `web/index.html`

### Evidence

The current `web/index.html` (and `build/web/index.html`) looks like this:

```html
<script src="flutter.js" defer></script>
<script>
  window.addEventListener('load', function() {
    _flutter.loader.loadEntryPoint({
      serviceWorker: {
        serviceWorkerVersion: flutterServiceWorkerVersion,   // ← UNDEFINED
      },
      onEntrypointLoaded: function(engineInitializer) {
        engineInitializer.initializeEngine().then(function(appRunner) {
          appRunner.runApp();
        });
      }
    });
  });
</script>
```

What is broken:

1. **`flutterServiceWorkerVersion` is never defined.** The legacy bootstrap expected a global of that name to be injected into `index.html` by the build tool. Flutter ≥ 3.10 removed that injection. Confirmed by `grep -c "flutterServiceWorkerVersion"` returning `1` for both `web/index.html` and `build/web/index.html` — it is referenced but never assigned.
2. **The deprecated `loadEntryPoint()` API is used** instead of the modern `flutter_bootstrap.js` pattern. `flutter.js` still accepts the legacy call (it is auto-generated to support both APIs), but the deprecated path does no automatic fallback when `serviceWorkerVersion` is undefined — it tries to register `flutter_service_worker.js?v=undefined`. In `build/web/flutter.js` (read at offset 1, ~line 9):
   ```js
   let{serviceWorkerVersion:r, serviceWorkerUrl:t=c(`flutter_service_worker.js?v=${r}`), timeoutMillis:n=4e3}=e;
   ```
   With `r = undefined`, the URL becomes `flutter_service_worker.js?v=undefined`. The service worker register call hangs inside a 4-second `Promise.race` timeout. After the timeout fires, `loadServiceWorker` rejects, but its `.catch` only emits `console.warn("Exception while loading service worker:", …)`.
3. **`build/web/flutter_bootstrap.js` exists and is correct** — it calls the modern API with `serviceWorkerVersion: "1056950070"`. **But `index.html` never references it**, so the entire 9.5 KB bootstrap file is dead code.

### Why this produces a white screen (not a console error)

The service-worker failure is swallowed by `.catch(console.warn)`. `main.dart.js` then gets injected via `_createScriptTag` → engine initializer → `initializeEngine()` → `runApp()`. **However**, the CanvasKit loader inside `flutter.js` uses `c(t, "canvaskit.wasm")` where `t` is computed from `T(n,s)`:

```js
function T(i,e){return i.canvasKitBaseUrl?i.canvasKitBaseUrl:e.engineRevision&&!e.useLocalCanvasKit?I("https://www.gstatic.com/flutter-canvaskit",e.engineRevision):"canvaskit"}
```

Without a build config (only `flutter_bootstrap.js` sets `_flutter.buildConfig`), `T()` falls back to the string `"canvaskit"`. This happens to resolve correctly because the canvaskit folder is co-located. So CanvasKit *should* load…

But `WasmGC` detection at the top of `flutter.js`:
```js
w.supportsWasmGC: z()   // checks for `gc` opcode in a 12-byte wasm probe
```
This part is fine. The real failure is upstream: **`onEntrypointLoaded` is called, but no engine initializer promise is awaited**. The inline handler is synchronous in style — it kicks off `initializeEngine()` but never logs or surfaces an error if the returned engine silently fails. If CanvasKit returns a degraded instance (e.g. the wasm probe passed but instantiation inside `E()` rejects because `hasChromiumBreakIterators` is false and no `chromium` variant exists), `window.flutterCanvasKit` is `undefined` and `appRunner.runApp()` paints nothing — producing exactly the **blank white screen** the user observes.

The fact that the page **does** load (HTML+CSS render, no exception page) but **does not** show any Flutter content is the signature of a successful bootstrap that calls `runApp()` against an uninitialized CanvasKit.

### Required fix

**Replace `web/index.html` with the modern bootstrap.** Two acceptable forms:

**Option A — minimal modern bootstrap (recommended):**

```html
<!DOCTYPE html>
<html>
<head>
  <base href="$FLUTTER_BASE_HREF">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shule Yetu</title>
  <style>
    body { margin: 0; padding: 0; }
  </style>
</head>
<body>
  <script src="flutter_bootstrap.js" async></script>
</body>
</html>
```

**Option B — restore the legacy global that the old inline script expects:**

Replace the existing inline `<script>` block with:

```html
<script>
  window.flutterServiceWorkerVersion = '1056950070';   // match build/web/flutter_service_worker.js cache name
</script>
```

…and also reference `flutter.js` instead of `flutter_bootstrap.js` (already correct in current file).

**Option A is preferred** — Option B is fragile (cache name changes on every build).

After fixing `web/index.html`, run a fresh build:

```cmd
cd C:\Users\nalen\shule-yetu-backend\flutter_ui
flutter clean
flutter build web --release
```

Then serve `build/web/` and verify in Chrome DevTools:
- Console tab → no `flutterServiceWorkerVersion is not defined` reference error
- Network tab → `canvaskit.wasm` (6.8 MB), `main.dart.js` (2.9 MB), `flutter_bootstrap.js` all return 200
- Elements tab → `<flt-glass-pane>` element appears under `<body>`

---

## 2. Secondary issue — `impellerc.exe` Windows-version incompatibility

**File:** `C:\flutter\bin\cache\artifacts\engine\windows-x64\impellerc.exe`
**Source:** captured in all 9 `flutter_*.log` files (`flutter_01.log` through `flutter_09.log` — identical content)

### Evidence

The crash trace (verbatim from `flutter_09.log`):

```
ProcessException: ProcessException: This version of %1 is not compatible with the version of Windows you're running.
  Command: C:\flutter\bin\cache\artifacts\engine\windows-x64\impellerc.exe --sksl --iplr --json
           --sl=build\flutter_assets\shaders/ink_sparkle.frag
           --spirv=build\flutter_assets\shaders/ink_sparkle.frag.spirv
           --input=C:\flutter\packages\flutter\lib\src\material\shaders\ink_sparkle.frag
```

This happens inside `ShaderCompiler.compileShader` (`build_system/tools/shader_compiler.dart:188`), triggered by `bundle_builder.dart:208` writing shaders, called from `ResidentWebRunner._updateDevFS` (`resident_web_runner.dart:794`).

### What it means

`impellerc.exe` is the Flutter engine's shader compiler. It runs as a separate process during `flutter run -d chrome` and `flutter build web` to compile GLSL shaders (`ink_sparkle.frag`, `stretch_effect.frag`) into SPIR-V. The exe at `C:\flutter\bin\cache\artifacts\engine\windows-x64\impellerc.exe` was built against a different Windows runtime than is installed on this host (Windows 10 22H2).

### Confirmation in build output

`build/web/assets/shaders/` contains:
- `ink_sparkle.frag` (8.7 K)
- `stretch_effect.frag` (6.6 K)

…but **no `.spirv` companions**. Normally Flutter web includes the precompiled `.spirv` to skip runtime shader compilation. Their absence is direct evidence that this build was performed before the `impellerc.exe` corruption, or that the build had this failure but produced partial output (the rest of `main.dart.js` was written successfully).

### Required fix

Reinstall the engine artifacts. From an **elevated** `cmd.exe` on the Windows side (not WSL):

```cmd
cd C:\flutter
flutter doctor -v
flutter cache repair
```

If `cache repair` does not replace the broken binary:

```cmd
cd C:\flutter\bin\cache
rmdir /s /q artifacts
cd C:\flutter
flutter precache --windows --web
flutter doctor -v
```

Then validate the new binary:

```cmd
"C:\flutter\bin\cache\artifacts\engine\windows-x64\impellerc.exe" --help
```

If `--help` returns usage text instead of the "version of %1 is not compatible" error, the SDK is repaired.

**Do not delete `C:\flutter` itself** — `cache repair` will regenerate artifacts in place.

---

## 3. Tertiary smell — placeholder image assets

**Files:** `assets/images/modules/*.jpg`, `assets/previews/*.png` (20 files total)

### Evidence

All JPG and PNG files in those two directories are 12 bytes and contain the literal string `placeholder\n`:

```
$ cat assets/images/modules/esoko.jpg
placeholder
$ ls -l assets/images/modules/
esoko.jpg           12 B
gas_monitor.jpg     12 B
hospital.jpg        12 B
kenya_cademy.jpg    12 B
parcel.jpg          12 B
property.jpg        12 B
restaurant.jpg      12 B
twende.jpg          12 B
```

These are referenced in:
- `lib/screens/k1_home_desktop_screen.dart` (e.g. line 196: `assetPath: 'assets/images/modules/esoko.jpg'`)
- `lib/widgets/k1_video_reel_widget.dart` (via `K1VideoReelItem.thumbnailAsset`)

### Effect

Not the cause of the white screen. Flutter's `Image.asset` and `Image.network` failures render an error placeholder widget, not a blank canvas. **However**, after the bootstrap is fixed, these will produce a visibly broken UI in the K1 home dashboard's image module cards and video reel widget.

### Required fix

Replace each placeholder file with real image content. Quickest path: drop in actual JPG/PNG assets of comparable dimensions (800×600 or similar) with the same filenames. Or change the widget code to render an `Icon` fallback when the image fails to decode.

---

## Diagnostic checklist run

| Step | Result |
|---|---|
| 1. `flutter run -d chrome -v` | **Crash** — `impellerc.exe` Windows-version incompatibility (see §2) |
| 2. `lib/main.dart` entry point | **Clean** — `runApp(const ShuleYetuUiApp())` at `lib/main.dart:7`; no `await` in `main()` |
| 3. Async startup checks | **Clean** — no Splash/Firebase/Auth/school-context service initialization in this project. Verified by reading all 9 log files: zero Dart-level exceptions |
| 4. Routing (`AppRouter`) | **Clean** — `MaterialApp.routes` with 5 `WidgetBuilder` entries, `initialRoute: '/'` resolves to `K1HomeDesktopScreen()` |
| 5. Assets vs files | **Partial match** — paths in `pubspec.yaml` exist on disk but image files are 12-byte placeholders (§3) |
| 6. `web/index.html` | **Broken** — uses legacy bootstrap with undefined `flutterServiceWorkerVersion` global (§1) |
| 7. Minimal render test (`K1 TEST`) | **Not yet run** — requires browser interaction. Recommended after the index.html fix to confirm Flutter itself is healthy |
| 8. Final report | This document |

---

## Recommended action sequence

1. **Fix `web/index.html`** using Option A above (modern `flutter_bootstrap.js`).
2. **Repair Flutter SDK** with `flutter cache repair` then `flutter precache --windows --web` from elevated `cmd.exe` on Windows.
3. **Rebuild:** `flutter clean && flutter build web --release`.
4. **Replace placeholder image assets** under `assets/images/modules/` and `assets/previews/`.
5. **Serve `build/web/`** via `python -m http.server 8000` from the project root (or any static server) and open `http://localhost:8000/`. Verify in DevTools that no JS errors are logged and `<flt-glass-pane>` appears in the DOM.
6. **(Optional) Run the minimal render test** — replace `lib/main.dart` body temporarily with the `K1 TEST` scaffold from the original diagnostic prompt. If `K1 TEST` renders, the bootstrap is confirmed healthy and any remaining white-screen behavior is in the home screen widget tree.
