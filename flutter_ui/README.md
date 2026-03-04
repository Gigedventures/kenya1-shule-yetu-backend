# Kenya 1 / Shule Yetu Flutter UI

## Windows Run Steps
1. Open PowerShell in this folder: `cd flutter_ui`
2. Verify SDK/tooling: `flutter doctor`
3. Install packages: `flutter pub get`
4. List available targets: `flutter devices`
5. Run on a specific device/emulator:
   - Android emulator already running: `flutter run`
   - Specific target: `flutter run -d <device-id>`
   - Web (Chrome): `flutter run -d chrome`

## Device / Emulator Selection
- Android Studio:
  1. Open Device Manager
  2. Start an emulator
  3. Use `flutter devices` to confirm it appears
- VS Code:
  1. `Ctrl+Shift+P` -> `Flutter: Select Device`
  2. Choose your emulator/phone

## Routes
- `/` Kenya 1 Home
- `/shule-yetu-selector` Shule Yetu selector (Juniors vs Seniors)
- `/juniors` Shule Yetu Parent Dashboard (Juniors)
- `/seniors` Shule Yetu Student Dashboard (Seniors)

The `/` route now renders the new desktop-first K1 Global Home Page with responsive 4/3/2/1-column behavior.

## Notes
- Mock data is in `lib/data/mock_data.dart`.
- `TODO(backend)` marks integration points for API wiring.
