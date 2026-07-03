# Shule Yetu Flutter UI — Full Audit Report

**Generated:** 2026-07-02  
**Scope:** `flutter_ui/lib/` — all screens, routes, widgets, navigation, data, and backend integration points

---

## Executive Summary

| Category | Count | Notes |
|----------|-------|-------|
| **Total Screens** | 12 | 1 entry (Kenya Home), 1 selector, 1 parent, 4 student dashboards (primary/junior/senior/tertiary), 3 legacy/redirect screens |
| **Routes Defined** | 5 | `/`, `/shule-yetu-selector`, `/juniors`, `/seniors`, `/student-dashboard` |
| **Placeholder Actions (SnackBar/print/TODO/debugPrint)** | 8 TODOs | All are `// TODO(backend):` comments — **zero** `ScaffoldMessenger`, `SnackBar`, `print()`, `debugPrint()`, or "Coming Soon" strings found |
| **Backend-Connected Screens** | 0 | 100% mock data via `MockUsersData` / `MockData` |
| **Architecture Coverage (14 domains)** | 2/14 | Only **Shule Yetu (Academics)** and **Transport (partial)** have UI; 12 domains missing |

---

## 1. Screen-by-Screen Audit

### A. Fully Implemented (Real UI, Working Navigation, Real Widgets)

| Screen | Route | Role | Status |
|--------|-------|------|--------|
| `K1HomeDesktopScreen` | `/` (kenyaHome) | Kenya 1 Super App | ✅ Full responsive dashboard with 4-column grid, 18 widget types, sidebar navigation, module cards, video reel, smart offers |
| `ShuleYetuSelectorScreen` | `/shule-yetu-selector` | Role Selector | ✅ 5 role cards (Parent, Primary/Junior/Senior/Tertiary Student), animated, navigates to dashboards |
| `JuniorsParentDashboardScreen` | `/juniors` | Parent (CBC) | ✅ 7 tabs (Overview, Learning, Homework, Attendance, Transport, Fees, Chat), layer selector (PP1-PP2, Grade 1-6, Grade 7-9), charts, real widget composition |
| `PrimaryStudentDashboardScreen` | (via selector) | Student (Grade 1-5) | ✅ Hero, Mission Board, Schedule, Homework, Reading, Rewards, Transport — all mock but fully built |
| `JuniorStudentDashboardScreen` | (via selector) | Student (Grade 6-8) | ✅ Stats grid, Assignments, Leaderboard, Clubs/Projects, Study Rhythm — all mock but fully built |
| `TertiaryStudentDashboardScreen` | (via selector) | Student (College/TVET/Uni) | ✅ 7 tabs (Dashboard, Courses, Exams, Results, Timetable, AI Tutor, Finance), charts, forms, mock data |

### B. Partially Implemented (Screen Exists, Mock Data, Incomplete Navigation)

| Screen | Route | Gaps |
|--------|-------|------|
| `SeniorsStudentDashboardScreen` | `/seniors` | **Stub only** — returns `StudentDashboardRouterScreen` with tertiary demo student; **SeniorStudentDashboardScreen** is a placeholder (AppBar + Center Text) |
| `StudentDashboardRouterScreen` | `/student-dashboard` | Router works but `SeniorStudentDashboardScreen` is non-functional |
| `SeniorStudentDashboardScreen` | — | **Empty scaffold** — no tabs, no data, no features |
| `KenyaHomeScreen` | — | Thin wrapper → `K1HomeDesktopScreen` (OK) |

### C. Placeholder Actions — **NONE FOUND**

| Pattern | Occurrences | Files |
|---------|-------------|-------|
| `ScaffoldMessenger` | 0 | — |
| `SnackBar` | 0 | — |
| `print(` | 0 | — |
| `debugPrint(` | 0 | — |
| "Coming Soon" / "Feature coming soon" | 0 | — |
| `// TODO(backend):` | **8** | See Section 4 |

> **Finding:** The codebase uses **structured TODO comments** (`// TODO(backend): …`) instead of runtime placeholders. This is a **positive pattern** — no SnackBar toasts, no debug prints, no fake "Feature coming soon" banners.

### D. Missing Features (Exist in Architecture, Not in Flutter)

| Architecture Domain | Backend Exists? | Flutter UI Status |
|---------------------|-----------------|-------------------|
| **Super Admin** | Yes (k1-backend) | ❌ No screen, no route |
| **Headteacher** | Yes | ❌ No screen, no route |
| **Teacher** | Yes | ❌ No screen, no route |
| **Finance** | Yes | ⚠️ Partial (Tertiary Finance tab only, mock) |
| **HR** | Yes | ❌ No screen |
| **Academics** | Yes | ✅ Covered via Parent/Student dashboards (CBC + Senior/Tertiary) |
| **Exams** | Yes | ⚠️ Partial (Tertiary Exams tab + Junior quizzes only) |
| **Attendance** | Yes | ⚠️ Partial (Parent + Junior tabs only) |
| **Inventory** | Yes | ❌ No screen |
| **Transport** | Yes | ⚠️ Partial (Parent bus tracker mini, Primary transport row, Junior tab) |
| **Communication** | Yes | ⚠️ Partial (Parent Chat tab stub, Tertiary AI Tutor chat input) |
| **Reports** | Yes | ❌ No reports screen (only inline charts) |

---

## 2. Route & Navigation Map

```
AppRouter.routes
├── '/'                          → K1HomeDesktopScreen (Kenya 1 Home)
├── '/shule-yetu-selector'       → ShuleYetuSelectorScreen (Role picker)
├── '/juniors'                   → JuniorsParentDashboardScreen (Parent CBC)
├── '/seniors'                   → SeniorsStudentDashboardScreen → StudentDashboardRouterScreen → SeniorStudentDashboardScreen (STUB)
└── '/student-dashboard'         → StudentDashboardRouterScreen (with tertiary demo student)
```

**Navigation Gaps:**
- No named routes for: Teacher, Headteacher, Super Admin, Finance Officer, HR, Inventory Manager, Transport Manager
- No deep links for: specific student, fee invoice, exam timetable, bus route, chat thread
- No route guards / role-based redirects
- `SeniorStudentDashboardScreen` reachable but non-functional

---

## 3. Widget Inventory (Reusable Components)

| Widget | Used In | Purpose |
|--------|---------|---------|
| `K1NavigationSidebar` | Kenya Home (left rail) | Module navigation with preview panel |
| `K1WidgetCard` | Kenya Home (all columns) | Standardized card with title, accent, icon, style variants |
| `K1ImageModuleCards` | Kenya Home (Module Marketplace) | Image-based module cards with CTA |
| `K1VideoReelWidget` | Kenya Home (Short Videos) | Horizontal video carousel |
| `K1SmartOffersWidget` | Kenya Home (Nearby Offers) | Location-aware offer cards |
| `WalletPulseWidget` | Kenya Home (Wallet Pulse) | Balance + quick actions |
| `K1TopBar` | Parent, Tertiary dashboards | Title + subtitle + mail badge |
| `K1BottomNav` | Parent, Tertiary dashboards | 4-item bottom nav (Home, Explore, Wallet, Profile) |
| `K1Column` | Kenya Home | Responsive column layout helper |
| `GlassCard`, `SectionTitle`, `QuickActionCard`, `ProgressStrip`, `ServiceTile` | Various | Base UI primitives |

**All widgets are functional and well-styled.** No broken widgets found.

---

## 4. All `// TODO(backend):` Comments (8 Total)

| File | Line | Comment |
|------|------|---------|
| `data/mock_data.dart` | 117 | `replace with repository calls from Kenya1/ShuleYetu APIs` |
| `data/mock_data.dart` | 181 | `fetch CBC layer-aware dashboard payload from Shule Yetu API` |
| `data/mock_data.dart` | 296 | `return senior dashboard sections from student profile APIs` |
| `screens/juniors_parent_dashboard_screen.dart` | 470 | `connect fee statements and payment endpoints` |
| `screens/juniors_parent_dashboard_screen.dart` | 494 | `plug real parent-teacher chat API` |
| `screens/student/tertiary/tertiary_student_dashboard_screen.dart` | 346 | `route to transcript download endpoint` |
| `screens/student/tertiary/tertiary_student_dashboard_screen.dart` | 416 | `connect AI study plan generator service` |
| `screens/student/tertiary/tertiary_student_dashboard_screen.dart` | 463 | `wire invoices, payments, and receipts API` |

> **Pattern:** All TODOs are **explicit backend integration points** with clear API intent. No vague TODOs.

---

## 5. Gap Analysis by Architecture Domain

| Feature | Current State | Missing Screens | Missing Routes | Missing Widgets | Missing Backend Connection |
|---------|---------------|-----------------|----------------|-----------------|----------------------------|
| **Super Admin** | ❌ None | Admin dashboard, user management, system config, analytics | `/admin/*` | AdminLayout, UserTable, ConfigPanel, MetricsCards | All |
| **Headteacher** | ❌ None | School overview, staff mgmt, academic calendar, discipline | `/headteacher/*` | SchoolHeader, StaffCard, CalendarView, IncidentLog | All |
| **Teacher** | ❌ None | Class roster, lesson planner, gradebook, attendance marking, parent msg | `/teacher/*` | ClassSelector, LessonCard, GradebookGrid, AttendanceSheet, ChatThread | All |
| **Parent (CBC)** | ✅ JuniorsParentDashboardScreen | Fee payment flow, chat thread, report cards, multi-child switch | `/parent/*`, `/parent/fees`, `/parent/chat` | FeePaymentSheet, ChatThread, ReportCardViewer | Fees API, Chat API, Report API |
| **Student (Primary)** | ✅ PrimaryStudentDashboardScreen | Exam view, progress reports, parent messages | `/student/primary/*` | ExamCard, ProgressReport, MessageInbox | Exam API, Report API |
| **Student (Junior)** | ✅ JuniorStudentDashboardScreen | Project collaboration, club details, exam timetable | `/student/junior/*` | ProjectBoard, ClubDetail, ExamTimetable | Project API, Club API, Exam API |
| **Student (Senior)** | ❌ STUB ONLY | **Entire dashboard** — all 7+ tabs needed | `/student/senior/*` | All (Dashboard, Courses, Exams, Results, Timetable, Study, Finance) | All |
| **Student (Tertiary)** | ✅ TertiaryStudentDashboardScreen | Transcript download, AI study plan, finance payments | `/student/tertiary/*` | TranscriptViewer, StudyPlanGenerator, PaymentSheet | Transcript API, AI Plan API, Finance API |
| **Finance** | ⚠️ Tertiary tab only | Fee collection, invoicing, receipts, scholarships, budgets | `/finance/*` | InvoiceCard, ReceiptViewer, BudgetChart, ScholarshipCard | All |
| **HR** | ❌ None | Staff directory, payroll, leave, performance, recruitment | `/hr/*` | StaffCard, LeaveCalendar, PayslipViewer, ReviewForm | All |
| **Academics** | ✅ Partial (via dashboards) | Curriculum builder, lesson library, assessment designer | `/academics/*` | CurriculumTree, LessonEditor, AssessmentBuilder | All |
| **Exams** | ⚠️ Tertiary + Junior only | Exam timetable, seating, results publishing, analytics | `/exams/*` | ExamTimetableGrid, SeatingChart, ResultPublisher, AnalyticsDashboard | All |
| **Attendance** | ⚠️ Parent + Junior tabs | Biometric/RFID check-in, geo-fencing, absence alerts, reports | `/attendance/*` | CheckInButton, GeoFenceMap, AbsenceAlertCard, AttendanceReport | All |
| **Inventory** | ❌ None | Stock levels, purchase orders, asset tracking, issuance logs | `/inventory/*` | StockCard, POForm, AssetTag, IssuanceLog | All |
| **Transport** | ⚠️ Partial widgets | Route planner, driver app, parent tracking, maintenance, alerts | `/transport/*` | RouteMap, DriverDashboard, ParentTracker, MaintenanceLog, AlertBanner | All |
| **Communication** | ⚠️ Chat stubs only | Announcements, SMS/Email/Push, newsletter, surveys | `/comms/*` | AnnouncementCard, BroadcastComposer, SurveyForm, InboxThread | All |
| **Reports** | ❌ None | Custom report builder, scheduled exports, dashboards | `/reports/*` | ReportBuilder, ScheduleConfig, ExportButton, DashboardWidget | All |

---

## 6. Prioritized Implementation Plan

### Phase 1: Eliminate All Placeholder Actions & Non-Functional Menu Items (Week 1–2)

| # | Task | File(s) | Effort |
|---|------|---------|--------|
| 1.1 | **Replace `SeniorStudentDashboardScreen` stub** with full 7-tab implementation (mirror Tertiary structure) | `screens/student/senior/senior_student_dashboard_screen.dart` | High |
| 1.2 | **Wire Parent Fees tab** — replace TODO with real fee statement API + payment sheet | `screens/juniors_parent_dashboard_screen.dart:470` | Medium |
| 1.3 | **Wire Parent Chat tab** — replace TODO with real chat API + thread view | `screens/juniors_parent_dashboard_screen.dart:494` | Medium |
| 1.4 | **Wire Tertiary Transcript button** — add download endpoint + viewer | `screens/student/tertiary/tertiary_student_dashboard_screen.dart:346` | Medium |
| 1.5 | **Wire Tertiary AI Study Plan** — connect generator service + result view | `screens/student/tertiary/tertiary_student_dashboard_screen.dart:416` | High |
| 1.6 | **Wire Tertiary Finance tab** — invoices, payments, receipts API | `screens/student/tertiary/tertiary_student_dashboard_screen.dart:463` | High |
| 1.7 | **Add route guards** — role-based redirect from `/seniors` to functional Senior dashboard | `app_router.dart`, `SeniorsStudentDashboardScreen` | Low |

> **Why Phase 1 first:** These are **dead ends** users hit today. Every TODO(backend) is a broken user journey. Fixing them makes existing dashboards production-ready.

---

### Phase 2: Complete Missing Student Dashboards (Week 2–3)

| # | Task | Target |
|---|------|--------|
| 2.1 | Build `SeniorStudentDashboardScreen` with tabs: Dashboard, Courses, Exams, Results, Timetable, Study Tools, Finance | Parity with Tertiary |
| 2.2 | Add deep-link routes: `/student/senior/dashboard`, `/student/senior/exams`, etc. | `app_router.dart` |
| 2.3 | Add StudentDashboardRouterScreen support for senior level (already wired) | Verify |

---

### Phase 3: Implement Missing Role Dashboards (Week 3–6)

| Priority | Role | Screens Needed | Backend Dependencies |
|----------|------|----------------|---------------------|
| **P0** | Teacher | Class roster, Gradebook, Attendance marking, Lesson planner, Parent messaging | Timetable API, Gradebook API, Attendance API, Messaging API |
| **P0** | Headteacher | School KPIs, Staff management, Calendar, Discipline, Reports | School API, Staff API, Calendar API, Reports API |
| **P1** | Finance Officer | Fee collection, Invoicing, Receipts, Scholarships, Budgets | Finance API, Payment Gateway |
| **P1** | Transport Manager | Route planner, Driver dashboard, Parent tracking, Maintenance | Transport API, GPS/Tracking |
| **P2** | HR Officer | Staff directory, Payroll, Leave, Performance, Recruitment | HR API, Payroll API |
| **P2** | Inventory Manager | Stock, Purchase orders, Assets, Issuance | Inventory API |
| **P3** | Super Admin | System config, User management, Analytics, Audit logs | Admin API, Auth/Identity |

---

### Phase 4: Cross-Cutting Features (Week 4–7, parallel)

| Feature | Screens | Widgets | Backend |
|---------|---------|---------|---------|
| **Communication Hub** | Announcements, Broadcast, Surveys, Inbox (all roles) | `AnnouncementCard`, `BroadcastComposer`, `SurveyForm`, `InboxThread` | Messaging/Notification API |
| **Reports Engine** | Report builder, Scheduled exports, Dashboard widgets (Admin, Headteacher, Finance) | `ReportBuilder`, `ScheduleConfig`, `ExportButton`, `DashboardWidget` | Reporting API |
| **Exam Management** | Timetable, Seating, Results publishing, Analytics (Teacher, Headteacher, Student) | `ExamTimetableGrid`, `SeatingChart`, `ResultPublisher`, `AnalyticsDashboard` | Exam API |
| **Attendance System** | Check-in (RFID/QR/Geo), Absence alerts, Reports (Teacher, Parent, Transport) | `CheckInButton`, `GeoFenceMap`, `AbsenceAlertCard`, `AttendanceReport` | Attendance API |
| **Transport Tracking** | Route map, Driver app, Parent live tracker, Maintenance (Transport, Parent, Student) | `RouteMap`, `DriverDashboard`, `ParentTracker`, `MaintenanceLog` | Transport/Tracing API |

---

### Phase 5: Polish & Production Hardening (Week 6–8)

- Error boundaries + offline caching
- Load states / skeletons for all async widgets
- Accessibility audit (semantics, contrast, touch targets)
- Integration tests for critical flows (fee payment, exam check-in, chat)
- Performance profiling (frame timing, memory)
- CI/CD: `flutter test --coverage`, `flutter analyze`, golden tests

---

## 7. Recommended Next Steps

1. **Approve this audit** → proceed to Phase 1 implementation
2. **Confirm backend API contracts** for the 8 TODO endpoints (OpenAPI/Swagger specs)
3. **Prioritize Senior dashboard** — it's the only completely broken student level
4. **Set up feature branches** per role (teacher, headteacher, finance, etc.)
5. **Establish design system tokens** (colors, spacing, typography) — already present in `theme/`

---

## Appendix: File Index

```
flutter_ui/lib/
├── main.dart                      → App entry, theme, routes
├── app_router.dart                → 5 routes only
├── data/
│   ├── mock_users.dart            → 20 guardians, 40 students, 4 demo users
│   ├── mock_data.dart             → All dashboard payloads + 3 TODO(backend)
│   └── k1_home_widgets.dart       → Kenya Home widget grid config
├── models/                        → 6 model files (Student, Parent, K1HomeWidget, etc.)
├── screens/
│   ├── kenya_home_screen.dart     → Wrapper
│   ├── k1_home_desktop_screen.dart→ **Fully implemented** Kenya 1 home
│   ├── shule_yetu_selector_screen.dart → Role selector (5 cards)
│   ├── juniors_parent_dashboard_screen.dart → Parent CBC (7 tabs, 2 TODOs)
│   ├── seniors_student_dashboard_screen.dart → Router to stub
│   ├── student/
│   │   ├── student_dashboard_router_screen.dart → Level router
│   │   ├── primary/primary_student_dashboard_screen.dart → Full UI
│   │   ├── junior/junior_student_dashboard_screen.dart → Full UI
│   │   ├── senior/senior_student_dashboard_screen.dart → **STUB**
│   │   └── tertiary/tertiary_student_dashboard_screen.dart → Full UI (7 tabs, 3 TODOs)
├── widgets/                       → 14 reusable widgets (all functional)
└── theme/                         → Colors, icons, app theme
```

---

**End of Audit Report**  
*Ready for implementation planning.*