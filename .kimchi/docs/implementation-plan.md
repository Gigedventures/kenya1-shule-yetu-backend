# Shule Yetu Production Transformation - Implementation Plan

## Overview
Transform the partially scaffolded school platform into a fully data-driven, production-grade multi-user SaaS application with real API-backed data flow, dense UI, and comprehensive data seeding.

## Current State Analysis
- **Backend**: Laravel with ShuleYetu module - models, controllers, API routes exist but lack student/teacher-specific endpoints
- **Frontend**: Flutter with extensive mock data, basic API service, no repository layer
- **Data**: Minimal seeders, no realistic data volume

## Required API Endpoints (New)

| Endpoint | Purpose | Data Source |
|----------|---------|-------------|
| `GET /api/v1/student/assignments` | Student assignments with status | ShuleExam, ShuleExamSubject, ShuleExamScore |
| `GET /api/v1/student/attendance` | Student attendance history | Attendance model (to create) |
| `GET /api/v1/student/classes` | Student class schedule | ShuleClass, ShuleStream, ShuleTeacherAssignment |
| `GET /api/v1/teacher/assignments` | Teacher assignments to grade | ShuleExam, ShuleExamSubject |
| `GET /api/v1/teacher/attendance` | Teacher attendance marking | Attendance model |
| `GET /api/v1/messages/inbox` | Unified message inbox | ShuleMessage |

## Implementation Phases

### Phase 1: Backend API Layer & Data Models
**Complexity**: Complex (concurrency, data relationships, multi-role endpoints)

#### Chunk 1.1: Attendance Model & Migration
- Create `attendance` migration with fields: student_id, class_id, date, status (present/absent/late), marked_by, notes
- Create `Attendance` model in `App/Modules/ShuleYetu/Models/`
- Add relationships to ShuleStudent, ShuleClass, User

#### Chunk 1.2: Student API Controller
- Create `StudentController` with methods: assignments, attendance, classes
- Use existing models: ShuleExam, ShuleExamSubject, ShuleExamScore, ShuleClass, ShuleStream
- Return consistent JSON contract: `{status, data, meta}`

#### Chunk 1.3: Teacher API Controller
- Create `TeacherController` with methods: assignments, attendance
- Include grading workflow endpoints

#### Chunk 1.4: Messages Inbox API
- Extend existing MessageController with `/inbox` endpoint
- Return unified thread list with unread counts

#### Chunk 1.5: API Routes Registration
- Add new routes to `routes/api.php` under sanctum + tenancy middleware
- Ensure consistent naming: `/api/v1/student/...`, `/api/v1/teacher/...`, `/api/v1/messages/inbox`

### Phase 2: Database Seeding (Massive Data Volume)
**Complexity**: Complex (large volume, relationships, realistic distributions)

#### Chunk 2.1: Core Entity Seeders
- 5 schools with realistic Kenyan names/locations
- 10 academic years, 30 terms
- 50 classes across grades PP1-Grade 12
- 100 teachers with subject specializations
- **350 students per module baseline** = ~1,750 total students
- Class assignments, stream assignments

#### Chunk 2.2: Academic Data Seeders
- **1000+ assignments** across exams, homework, projects
- Exam types: CAT, Mid-term, End-term, KCPE, KCSE
- Subjects per class per term
- Realistic score distributions (normal curves)

#### Chunk 2.3: Attendance History Seeder
- Full academic year of daily attendance per student
- Realistic patterns: 95% present, 3% absent, 2% late
- Term-by-term analytics ready

#### Chunk 2.4: Communication Seeders
- Message threads between teachers-students-parents
- Announcements per class/school
- 500+ message threads with 2-10 messages each

### Phase 3: Flutter Data Layer & Repository Pattern
**Complexity**: Complex (architecture, async, caching, error handling)

#### Chunk 3.1: Repository Layer
- Create `StudentRepository`, `TeacherRepository`, `MessageRepository`
- Implement: API → Repository → Provider/BLoC → UI flow
- Add caching with timestamp-based invalidation
- Error handling with retry logic

#### Chunk 3.2: API Service Enhancement
- Extend `K1ApiService` with typed methods for each endpoint
- Add pagination support (page, per_page)
- Add filtering parameters (status, date_range, subject)
- Response parsing to typed models

#### Chunk 3.3: State Management (Provider/BLoC)
- Create providers: `StudentAssignmentsProvider`, `StudentAttendanceProvider`, `StudentClassesProvider`, `TeacherAssignmentsProvider`, `TeacherAttendanceProvider`, `MessagesProvider`
- Implement loading, error, empty, success states
- Real-time updates via polling or WebSocket (future)

### Phase 4: Flutter UI Transformation - Density System
**Complexity**: Complex (design system, recursive widget refactoring)

#### Chunk 4.1: Design System Overhaul
- Create `DensityTokens` class: compact spacing (4, 8, 12, 16), reduced padding
- Typography scale: title (12sp), value (14sp bold), metadata (11sp muted)
- Card height reduction: 25-35% of current
- Table-first layout components

#### Chunk 4.2: Overview Layer (Stat Cards)
- Compact KPI cards: attendance %, pending assignments, unread messages
- Grid layout: 2-4 columns based on screen width
- Skeleton loading states

#### Chunk 4.3: Work Layer - List/Table Components
- Generic `DataTable<T>` with sorting, filtering, pagination
- `AssignmentList`, `AttendanceList`, `ClassScheduleList`, `MessageList`
- Filter chips: status, date range, subject
- Pull-to-refresh, infinite scroll

#### Chunk 4.4: Detail Layer - Drill-down Screens
- `AssignmentDetailScreen`: full description, attachments, submission status
- `AttendanceDetailScreen`: calendar heatmap, term breakdown, stats
- `MessageThreadScreen`: real-time chat UI, reply, mark read

### Phase 5: Screen Refactoring (Replace Mock Data)
**Complexity**: Simple per screen, but many screens

#### Chunk 5.1: Junior Student Dashboard
- Replace all static data with API calls
- Use new density components
- Loading skeletons, empty states

#### Chunk 5.2: Senior Student Dashboard
- Same transformation

#### Chunk 5.3: Teacher Dashboard
- Assignments to grade, attendance marking, class overview

#### Chunk 5.4: Parent Dashboard
- Children overview, fees, messages, attendance

#### Chunk 5.5: Admin Dashboard
- School analytics, staff performance, system health

### Phase 6: Real-time Features & Polish
**Complexity**: Complex (WebSockets, background sync)

#### Chunk 6.1: Real-time Updates
- WebSocket connection for messages, attendance updates
- Background sync for offline support

#### Chunk 6.2: Testing & Verification
- Integration tests for all API endpoints
- Widget tests for density components
- E2E flows: student submits assignment → teacher grades → parent sees result

---

## Detailed Specifications

### API Contract Standard
```json
{
  "status": "success|error",
  "data": [...],
  "meta": {
    "pagination": {"current_page": 1, "per_page": 20, "total": 100},
    "filters": {"status": "pending", "date_from": "2026-01-01"},
    "summary": {"total": 100, "pending": 25, "completed": 75}
  }
}
```

### Student Assignments Response
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "title": "Energy Transfer Worksheet",
      "subject": "Integrated Science",
      "type": "homework|exam|project",
      "status": "pending|submitted|graded|overdue",
      "due_date": "2026-01-15T17:30:00Z",
      "assigned_date": "2026-01-10T08:00:00Z",
      "max_score": 50,
      "score": null,
      "teacher_name": "Ms. Wanjiku",
      "attachments": [],
      "submission": null
    }
  ],
  "meta": {"pagination": {...}, "summary": {"pending": 4, "submitted": 2, "graded": 10}}
}
```

### Student Attendance Response
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "date": "2026-01-15",
      "status": "present|absent|late",
      "class_name": "Grade 8 North",
      "subject": "Integrated Science",
      "marked_by": "Mr. Otieno",
      "notes": "Arrived 10 min late"
    }
  ],
  "meta": {"summary": {"present": 180, "absent": 5, "late": 3, "rate": 95.7}}
}
```

### Student Classes Response
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Integrated Science",
      "teacher": "Mr. Otieno",
      "room": "Science Block",
      "schedule": [
        {"day": 1, "start": "07:50", "end": "08:40"},
        {"day": 3, "start": "10:10", "end": "11:00"}
      ],
      "color": "#1E88E5"
    }
  ]
}
```

### Messages Inbox Response
```json
{
  "status": "success",
  "data": [
    {
      "id": "thread-123",
      "participant": {"id": 45, "name": "Ms. Wanjiku", "role": "teacher", "avatar_url": "..."},
      "last_message": "Please submit your worksheet by Friday",
      "last_message_time": "2026-01-15T14:30:00Z",
      "unread_count": 3,
      "is_online": true
    }
  ],
  "meta": {"total_threads": 12, "total_unread": 8}
}
```

---

## Acceptance Criteria

### Backend
- [ ] All 6 new API endpoints return 200 with valid JSON contract
- [ ] Seeders create 1750+ students, 1000+ assignments, full attendance, 500+ message threads
- [ ] API responses include pagination, filtering, summary metadata
- [ ] All endpoints respect tenancy (school isolation)
- [ ] Proper authorization (student sees own, teacher sees classes, admin sees all)

### Frontend
- [ ] Zero mock data in any screen - all data from repositories
- [ ] Repository pattern implemented for all data domains
- [ ] Loading skeletons on all list/detail screens
- [ ] Empty states with actionable CTAs
- [ ] Density system: cards 25-35% height of original, table layouts
- [ ] Filtering, sorting, pagination on all list screens
- [ ] Pull-to-refresh and infinite scroll working

### UI/UX
- [ ] Overview layer: compact stat cards (4 per row on desktop)
- [ ] Work layer: DataTable with sticky headers, row actions
- [ ] Detail layer: full-screen drill-down with tabs/sections
- [ ] Consistent typography scale across all screens
- [ ] Dark mode support maintained

### Data Quality
- [ ] 350 students per module (junior, primary, senior, tertiary)
- [ ] 1000+ assignments with varied types, statuses, due dates
- [ ] Full academic year attendance per student
- [ ] Message threads between all role combinations

---

## File Mapping

### Backend New Files
```
app/Modules/ShuleYetu/Models/Attendance.php
app/Http/Controllers/Api/V1/ShuleYetu/StudentController.php
app/Http/Controllers/Api/V1/ShuleYetu/TeacherController.php
app/Http/Resources/Api/V1/ShuleYetu/Student/AssignmentResource.php
app/Http/Resources/Api/V1/ShuleYetu/Student/AttendanceResource.php
app/Http/Resources/Api/V1/ShuleYetu/Student/ClassScheduleResource.php
app/Http/Resources/Api/V1/ShuleYetu/Teacher/AssignmentResource.php
app/Http/Resources/Api/V1/ShuleYetu/Teacher/AttendanceResource.php
database/migrations/2026_07_xx_create_attendance_table.php
database/seeders/ShuleYetuMassiveSeeder.php
```

### Frontend New Files
```
flutter_ui/lib/data/repositories/student_repository.dart
flutter_ui/lib/data/repositories/teacher_repository.dart
flutter_ui/lib/data/repositories/message_repository.dart
flutter_ui/lib/data/providers/student_assignments_provider.dart
flutter_ui/lib/data/providers/student_attendance_provider.dart
flutter_ui/lib/data/providers/student_classes_provider.dart
flutter_ui/lib/data/providers/teacher_assignments_provider.dart
flutter_ui/lib/data/providers/teacher_attendance_provider.dart
flutter_ui/lib/data/providers/messages_provider.dart
flutter_ui/lib/theme/density_tokens.dart
flutter_ui/lib/widgets/density/compact_card.dart
flutter_ui/lib/widgets/density/data_table.dart
flutter_ui/lib/widgets/density/stat_card.dart
flutter_ui/lib/widgets/density/skeleton_loader.dart
flutter_ui/lib/widgets/density/empty_state.dart
flutter_ui/lib/screens/student/assignments_list_screen.dart
flutter_ui/lib/screens/student/attendance_list_screen.dart
flutter_ui/lib/screens/student/classes_list_screen.dart
flutter_ui/lib/screens/student/assignment_detail_screen.dart
flutter_ui/lib/screens/student/attendance_detail_screen.dart
flutter_ui/lib/screens/student/message_thread_screen.dart
flutter_ui/lib/screens/teacher/assignments_list_screen.dart
flutter_ui/lib/screens/teacher/attendance_screen.dart
```

### Frontend Modified Files
```
flutter_ui/lib/services/k1_api_service.dart (extend)
flutter_ui/lib/screens/student/junior/junior_student_dashboard_screen.dart (replace mock)
flutter_ui/lib/screens/student/senior/senior_student_dashboard_screen.dart (replace mock)
flutter_ui/lib/screens/student/student_dashboard_screen.dart (router)
flutter_ui/lib/data/mock_data.dart (remove/deprecate)
```

---

## Complexity Classification

| Chunk | Classification | Reason |
|-------|----------------|--------|
| 1.1 Attendance Model | Simple | Single model, migration, relationships |
| 1.2 Student Controller | Complex | Multi-model queries, authorization, pagination |
| 1.3 Teacher Controller | Complex | Grading workflow, bulk operations |
| 1.4 Messages Inbox | Simple | Extends existing controller |
| 1.5 Routes | Simple | Route registration |
| 2.1 Core Entities | Complex | Large volume, many relationships |
| 2.2 Academic Data | Complex | 1000+ records, score distributions |
| 2.3 Attendance History | Complex | 365 days × 1750 students = 638k records |
| 2.4 Communications | Complex | Threading, multi-role |
| 3.1 Repository Layer | Complex | Architecture, caching, error handling |
| 3.2 API Service | Simple | Wrapper methods |
| 3.3 State Management | Complex | Provider pattern, async state |
| 4.1 Design System | Complex | Token system, recursive widget updates |
| 4.2 Overview Layer | Simple | Stat card components |
| 4.3 Work Layer | Complex | Generic DataTable, filtering, pagination |
| 4.4 Detail Layer | Simple | Screen components |
| 5.1-5.5 Screen Refactor | Simple each | Replace mock with API, apply density |
| 6.1 Real-time | Complex | WebSockets, background sync |
| 6.2 Testing | Complex | Integration, widget, E2E |

---

## Execution Order

1. **Phase 1** (Backend APIs) - Blocks Phase 3, 4, 5
2. **Phase 2** (Seeding) - Can run parallel with Phase 1, needed for Phase 3+
3. **Phase 3** (Flutter Data Layer) - Blocks Phase 4, 5
4. **Phase 4** (Density System) - Blocks Phase 5
5. **Phase 5** (Screen Refactoring) - Consumes Phase 3, 4
6. **Phase 6** (Polish) - Final phase

## Dependencies
- Phase 1.1 must complete before 1.2, 1.3
- Phase 2 must complete before Phase 3 can test with real data
- Phase 3.1 must complete before 3.2, 3.3
- Phase 4.1 must complete before 4.2, 4.3, 4.4
- Phase 5 requires Phase 3 + Phase 4 complete