# CBC Data + Senior Data Integration Plan

> **Goal:** Complete CBC (Curriculum/Competency/Learning Area/Assessment) and Senior data integration — replace static mock data with real API-connected backend and Flutter screens.

**Architecture:** Reuse existing backend infrastructure (`CbcSetupController`, `ExamService`, `TranscriptService`, `StudentController`, `ChatService`, `FeeService`). Create dedicated Flutter services and models. Wire existing dashboards to real API calls.

## RECOVERY AUDIT

### Already completed (do not redo):
- **Transcript integration** ✅ — `TranscriptService`, `TranscriptController`, `TranscriptResource`, `transcript_models.dart`, `transcript_service.dart`, route
- **CBC Setup** ✅ — `CbcSetupController::setup()` creates classes/streams/subjects
- **Senior Dashboard** ✅ — `SeniorStudentDashboardScreen` exists (stub)

### What this plan creates:
1. **CBC Data** — `cbc_models.dart`, `cbc_service.dart`, connect to `CbcSetupController` API + remove `// TODO`
2. **Senior Data** — `senior_models.dart`, `senior_service.dart`, replace `SeniorStudentDashboardScreen` stub + remove `// TODO`

## Task 1: CBC Models + Service (Flutter)

**Files:**
- Create: `flutter_ui/lib/models/cbc_models.dart`
- Create: `flutter_ui/lib/services/cbc_service.dart`
- Modify: `flutter_ui/lib/screens/juniors_parent_dashboard_screen.dart` (connect to real API)

```dart
// cbc_models.dart
class CbcSetupData {
  const CbcSetupData({
    required this.classes,
    required this.streams,
    required this.subjects,
  });

  final List<CbcClass> classes;
  final List<CbcStream> streams;
  final List<CbcSubject> subjects;

  factory CbcSetupData.fromJson(Map<String, dynamic> json) => CbcSetupData(
    classes: (json['classes'] as List? ?? []).map((e) => CbcClass.fromJson(e)).toList(),
    streams: (json['streams'] as List? ?? []).map((e) => CbcStream.fromJson(e)).toList(),
    subjects: (json['subjects'] as List? ?? []).map((e) => CbcSubject.fromJson(e)).toList(),
  );
}

class CbcClass {
  final String id;
  final String name;
  final String level;

  factory CbcClass.fromJson(Map<String, dynamic> json) => ...
}

class CbcStream {
  final String id;
  final String name;
  final String? classId;

  factory CbcStream.fromJson(Map<String, dynamic> json) => ...
}

class CbcSubject {
  final String id;
  final String name;
  final String level;

  factory CbcSubject.fromJson(Map<String, dynamic> json) => ...
}
```

```dart
// cbc_service.dart
class CbcService {
  final String baseUrl;
  final Future<String?> Function() tokenProvider;

  CbcService({required this.baseUrl, required this.tokenProvider});

  /// POST /v1/shule-yetu/setup-cbc-full
  Future<void> setupCbcFull() async {
    ...
  }

  /// GET /v1/shule-yetu/cbc/curriculum
  Future<CbcSetupData> getCbcCurriculum() async {
    ...
  }
}
```

```dart
// juniors_parent_dashboard_screen.dart — replace MockData.juniorLayerData[_selectedLayer]!
// with real CbcService.getCbcCurriculum() call
```

## Task 2: Senior Models + Service (Flutter)

**Files:**
- Create: `flutter_ui/lib/models/senior_models.dart`
- Create: `flutter_ui/lib/services/senior_service.dart`
- Modify: `flutter_ui/lib/screens/student/senior/senior_student_dashboard_screen.dart` (replace stub)

```dart
// senior_models.dart
class SeniorDashboardData {
  final String studentName;
  final String program;
  final String semester;
  final List<SeniorKpi> kpis;
  final List<ScheduleBlock> schedule;
  final List<String> announcements;
  final List<AlertItem> alerts;

  factory SeniorDashboardData.fromJson(Map<String, dynamic> json) => ...
}
```

```dart
// senior_service.dart
class SeniorService {
  Future<SeniorDashboardData> getSeniorDashboard(String studentId) async {
    // Uses TranscriptService, ChatService, ExamService, StudentController
  }
}
```

```dart
// senior_student_dashboard_screen.dart — replace with real data-connected dashboard
```

## Verification

- ✅ `php -l` on all backend files
- ✅ `route:list` includes `cbc` and `senior` routes
- ✅ Flutter imports correct
- ✅ No `// TODO(backend)` for CBC or Senior remain
- ✅ `flutter analyze` passes
- ✅ `juniors_parent_dashboard_screen.dart` uses real data
- ✅ `senior_student_dashboard_screen.dart` no longer stub