# K1 AI Engine — Product Roadmap

## Current Codebase State

| Domain | Backend | Flutter | Status |
|-------|---------|---------|--------|
| Student Data | `ShuleStudent` model + `StudentController` | `student.dart` | ✅ Complete |
| CBC Data | `CbcSetupController` + `ShuleClass/Stream/Subject` | `cbc_models.dart`, `cbc_service.dart` | ✅ Complete |
| Attendance | `AttendanceController` (stub: 32B) | — | ❌ Stub |
| Finance | `FeeService`, `PaymentController`, `FeeStructureController`, `ReportController` | `fee_models.dart`, `fee_service.dart` | ✅ Complete |
| Transcript | `TranscriptService`, `TranscriptController` | `transcript_models.dart`, `transcript_service.dart` | ✅ Complete |
| Teacher Assessments | `ShuleExamScore` model | — | ⚠️ Model exists |
| Behaviour Records | — | — | ❌ Not built |
| Parent Communication | `MessageThread` model + `ChatController` | `chat_service.dart` | ⚠️ Partial |

## AI Engine — Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    K1 AI Engine (App\K1\Ai)                     │
├─────────────────────────────────────────────────────────────────┤
│  Phase 1: Core ML                                                  │
│  ┌──────────────────────────────────────────────────────────────┐ │
│  │ Engine        │ Models         │ Pipelines      │ Services    │ │
│  │───────────────│────────────────│───────────────│─────────────│ │
│  │ Predictor     │ Student        │ Train          │ Planner     │ │
│  │ Detector      │ Performance    │ Evaluate       │ Recommender │ │
│  │ Analyzer      │ Risk           │ Score          │ Reporter    │ │
│  │ Classifier     │ Behaviour     │ Cluster        │ Notifier    │ │
│  └──────────────────────────────────────────────────────────────┘ │
│  Phase 2: Teacher AI + Lesson Plan Auto-generator                    │
│  Phase 3: School AI + Institutional Analytics                        │
└─────────────────────────────────────────────────────────────────┘

## Implementation Priority

### Must Have (Phase 1 — Q1)
1. **Student Performance Predictor** — `App\K1\Ai\Services\StudentPerformancePredictor`
   - Input: exam scores, attendance, homework completion
   - Output: predicted GPA, risk level, subject weakness/strength
   - Uses: `ShuleExamScore`, `ShuleTermResult`, `ShuleAttendance`

2. **At-Risk Student Detector** — `App\K1\Ai\Services\AtRiskDetector`
   - Input: attendance trends, fee status, behaviour records
   - Output: risk score (0-100), intervention level (low/medium/high)
   - Uses: `StudentBill`, `ShuleAttendance`, `ShuleBehaviourRecord`

3. **Personalized Learning Plan Generator** — `App\K1\Ai\Services\LearningPlanGenerator`
   - Input: subject weaknesses + strengths from exam scores
   - Output: 14-day revision plan, resource recommendations, practice schedule

4. **Competency Gap Analyzer** — `App\K1\Ai\Services\CompetencyGapAnalyzer`
   - Input: CBC strand scores vs. expected targets
   - Output: gap report, recommended interventions

### Should Have (Phase 2 — Q2/Q3)
5. **Auto Lesson Plan Generator** — LLM integration for teacher tools
6. **Auto Scheme of Work** — Term-level curriculum mapping
7. **Auto Rubric/Assignment/Exam Generator** — Assessment creation
8. **Auto Marking Assistant** — Grading automation
9. **Enrollment & Revenue Forecaster** — Institutional planning

### Could Have (Phase 3 — Q4+)
10. **Behaviour & Attendance Risk Engine** — Real-time alerts
11. **Teacher Workload & Capacity Analyzer** — Resource optimization
12. **School Performance Ranking** — Multi-institution analytics
13. **Department Analytics** — Subject-level trend analysis
14. **Resource Utilization** — Classroom/lab/inventory tracking

## Data Sources (already exist)

| Source | Table/Model | Status |
|--------|-------------|--------|
| Exam scores | `ShuleExamScore` | ✅ Exists |
| Term results | `ShuleTermResult` | ✅ Exists |
| Student bills | `ShuleStudentBill` | ✅ Exists |
| Payments | `ShulePayment` | ✅ Exists |
| Fee structures | `ShuleFeeStructure` | ✅ Exists |
| Classes | `ShuleClass` | ✅ Exists |
| Streams | `ShuleStream` | ✅ Exists |
| Subjects | `ShuleSubject` | ✅ Exists |
| Students | `ShuleStudent` | ✅ Exists |
| Attendance | `ShuleAttendance` (?) | ⚠️ Need check |
| Behaviour | `ShuleBehaviour` (?) | ⚠️ Need check |
| Messages | `ShuleMessage`/`ShuleThread` | ✅ Exists |
| Announcements | `ShuleAnnouncement` | ✅ Exists |

## Flutter AI Engine — Client Layer

```
flutter_ui/lib/services/k1_ai_service.dart
  └── K1AiService {
      └── predictStudentPerformance(studentId)
      └── detectAtRisk(studentId)
      └── generateLearningPlan(studentId)
      └── analyzeCompetencyGaps(studentId)
      └── getTeacherRecommendations(studentId)
      └── getParentActions(studentId)
  }
```

## Routes (backend API)

```php
// Existing routes/api.php — add AI prefix
Route::prefix('/v1/shule-yetu/ai')->group(function () {
    Route::post('/students/{student}/predict', [AiController::class, 'predict']);
    Route::post('/students/{student}/at-risk', [AiController::class, 'atRisk']);
    Route::post('/students/{student}/learning-plan', [AiController::class, 'learningPlan']);
    Route::get('/students/{student}/competency-gaps', [AiController::class, 'competencyGaps']);
    Route::post('/teachers/{teacher}/lesson-plan', [AiController::class, 'lessonPlan']);
    Route::post('/schools/{school}/forecast', [AiController::class, 'forecast']);
});
```

## Estimated Effort

| Phase | Features | Estimated |
|-------|---------|-----------|
| Phase 1 (Core ML) | 4 services + 8 models | 4-6 weeks |
| Phase 2 (Teacher AI) | 8 generators + 6 services | 8-12 weeks |
| Phase 3 (School AI) | 6 analytics + 5 reports | 6-8 weeks |
| **Total** | **18+ services** | **18-26 weeks** |