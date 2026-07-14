/// Mock API data for student dashboards when backend is unavailable.
/// Used as fallback by repositories until live APIs are reachable.
import '../models/fee_models.dart';
import '../models/message_models.dart';
import '../models/student_models.dart';
import '../models/transcript_models.dart';

class MockStudentApiData {
  static final DateTime _today = DateTime.now();
  static DateTime _daysFromNow(int days) => _today.add(Duration(days: days));

  static List<Assignment> get assignments => [
    Assignment(
      id: 1,
      title: 'Mathematics: Algebra Practice',
      subject: 'Mathematics',
      type: 'homework',
      status: 'pending',
      dueDate: _daysFromNow(2),
      assignedDate: _daysFromNow(-3),
      maxScore: 100,
      teacherName: 'Mr. Omondi',
    ),
    Assignment(
      id: 2,
      title: 'English Comprehension Passage',
      subject: 'English',
      type: 'homework',
      status: 'pending',
      dueDate: _daysFromNow(1),
      assignedDate: _daysFromNow(-2),
      maxScore: 100,
      teacherName: 'Mrs. Kilonzo',
    ),
    Assignment(
      id: 3,
      title: 'Biology Practical Report',
      subject: 'Biology',
      type: 'project',
      status: 'overdue',
      dueDate: _daysFromNow(-1),
      assignedDate: _daysFromNow(-5),
      maxScore: 100,
      teacherName: 'Ms. Wairimu',
    ),
    Assignment(
      id: 4,
      title: 'History Essay: Independence',
      subject: 'History',
      type: 'homework',
      status: 'submitted',
      dueDate: _daysFromNow(-2),
      assignedDate: _daysFromNow(-6),
      maxScore: 100,
      teacherName: 'Mr. Muturi',
    ),
    Assignment(
      id: 5,
      title: 'Chemistry Equations Quiz',
      subject: 'Chemistry',
      type: 'exam',
      status: 'graded',
      dueDate: _daysFromNow(-4),
      assignedDate: _daysFromNow(-10),
      maxScore: 50,
      score: 42,
      percentage: 84,
      teacherName: 'Mrs. Njeri',
    ),
  ];

  static AssignmentSummary get assignmentSummary => AssignmentSummary(
    total: assignments.length,
    pending: assignments.where((a) => a.status == 'pending').length,
    submitted: assignments.where((a) => a.status == 'submitted').length,
    graded: assignments.where((a) => a.status == 'graded').length,
    overdue: assignments.where((a) => a.status == 'overdue').length,
  );

  static List<AttendanceRecord> get attendanceRecords => [
    AttendanceRecord(
      id: 1,
      date: _today,
      status: 'present',
      statusLabel: 'Present',
      statusColor: '#10B981',
      className: 'Form 3 Red',
      subject: 'Mathematics',
      markedBy: 'Mr. Omondi',
      checkInTime: '07:55',
    ),
    AttendanceRecord(
      id: 2,
      date: _today,
      status: 'present',
      statusLabel: 'Present',
      statusColor: '#10B981',
      className: 'Form 3 Red',
      subject: 'Physics',
      markedBy: 'Mrs. Njeri',
      checkInTime: '09:40',
    ),
    AttendanceRecord(
      id: 3,
      date: _daysFromNow(-1),
      status: 'late',
      statusLabel: 'Late',
      statusColor: '#F59E0B',
      className: 'Form 3 Red',
      subject: 'Chemistry',
      markedBy: 'Ms. Wairimu',
      checkInTime: '08:15',
      notes: 'Arrived after morning assembly',
    ),
    AttendanceRecord(
      id: 4,
      date: _daysFromNow(-2),
      status: 'present',
      statusLabel: 'Present',
      statusColor: '#10B981',
      className: 'Form 3 Red',
      subject: 'English',
      markedBy: 'Mrs. Kilonzo',
      checkInTime: '07:50',
    ),
    AttendanceRecord(
      id: 5,
      date: _daysFromNow(-3),
      status: 'present',
      statusLabel: 'Present',
      statusColor: '#10B981',
      className: 'Form 3 Red',
      subject: 'Geography',
      markedBy: 'Mr. Muturi',
      checkInTime: '08:00',
    ),
  ];

  static AttendanceSummary get attendanceSummary => AttendanceSummary(
    present: 42,
    absent: 2,
    late: 1,
    excused: 1,
    rate: 94.4,
  );

  static List<ClassSchedule> get classSchedules => [
    ClassSchedule(
      id: 1,
      name: 'Mathematics',
      teacher: 'Mr. Omondi',
      room: 'Room 4B',
      color: '#2563EB',
      schedule: _weekScheduleFor('Mathematics'),
    ),
    ClassSchedule(
      id: 2,
      name: 'Physics',
      teacher: 'Mrs. Njeri',
      room: 'Science Lab',
      color: '#7C3AED',
      schedule: _weekScheduleFor('Physics'),
    ),
    ClassSchedule(
      id: 3,
      name: 'Chemistry',
      teacher: 'Ms. Wairimu',
      room: 'Chemistry Lab',
      color: '#0F9D7A',
      schedule: _weekScheduleFor('Chemistry'),
    ),
    ClassSchedule(
      id: 4,
      name: 'English',
      teacher: 'Mrs. Kilonzo',
      room: 'Hall 2',
      color: '#E9B308',
      schedule: _weekScheduleFor('English'),
    ),
  ];

  static List<ScheduleEntry> _weekScheduleFor(String subject) {
    final entries = <ScheduleEntry>[];
    final days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    for (int i = 0; i < 5; i++) {
      entries.add(ScheduleEntry(
        day: i + 1,
        dayName: days[i],
        period: 1,
        start: '08:00',
        end: '08:45',
        room: 'Room ${i + 1}',
        subjectName: subject,
      ));
    }
    return entries;
  }
}

class MockFinanceApiData {
  static StudentStatement statementFor(String studentId) {
    return StudentStatement(
      studentId: studentId,
      bills: [
        StudentBill(
          id: 'b1',
          studentId: studentId,
          feeStructureId: 'fs1',
          totalAmount: 45000,
          paidAmount: 25000,
          balance: 20000,
          status: 'partial',
          feeStructureName: 'Tuition Fee - Semester 2',
          invoiceNumber: 'INV-2026-001',
          dueDate: DateTime.now().add(const Duration(days: 14)),
        ),
        StudentBill(
          id: 'b2',
          studentId: studentId,
          feeStructureId: 'fs2',
          totalAmount: 12000,
          paidAmount: 12000,
          balance: 0,
          status: 'paid',
          feeStructureName: 'Library & ICT Fee',
          invoiceNumber: 'INV-2026-002',
          dueDate: DateTime.now().add(const Duration(days: 30)),
        ),
      ],
      payments: [
        Payment(
          id: 'p1',
          studentId: studentId,
          amount: 15000,
          status: 'posted',
          paymentMethod: 'M-Pesa',
          reference: 'S26P1M',
          paymentDate: DateTime.now().subtract(const Duration(days: 21)),
          receiptNumber: 'RCT-001',
        ),
        Payment(
          id: 'p2',
          studentId: studentId,
          amount: 10000,
          status: 'posted',
          paymentMethod: 'Bank Transfer',
          reference: 'S26B2K',
          paymentDate: DateTime.now().subtract(const Duration(days: 7)),
          receiptNumber: 'RCT-002',
        ),
      ],
      summary: const StatementSummary(
        totalBilled: 57000,
        totalPaid: 37000,
        balance: 20000,
      ),
    );
  }
}

class MockTranscriptApiData {
  static AcademicTranscript transcriptFor(String studentId, String studentName, String admissionNo) {
    return AcademicTranscript(
      student: TranscriptStudent(
        id: studentId,
        name: studentName,
        admissionNo: admissionNo,
      ),
      terms: [
        TranscriptTerm(
          term: TermInfo(id: 't1', name: 'Semester 1'),
          totalMarks: 420,
          totalPercentage: 84,
          average: 3.5,
          overallGrade: 'B+',
          rank: 12,
        ),
        TranscriptTerm(
          term: TermInfo(id: 't2', name: 'Semester 2'),
          totalMarks: 450,
          totalPercentage: 90,
          average: 3.78,
          overallGrade: 'A',
          rank: 5,
        ),
      ],
      cumulative: const TranscriptCumulative(
        totalTerms: 2,
        average: 3.64,
        highestGrade: 'A',
      ),
    );
  }
}

class MockMessageApiData {
  static final DateTime _now = DateTime.now();

  static List<Thread> get threads => [
    Thread(
      id: '1',
      participant: const Participant(id: 1, name: 'Mr. Omondi', role: 'Mathematics Teacher', avatarUrl: 'https://i.pravatar.cc/150?img=61'),
      lastMessage: 'Remember to submit your algebra worksheet before Friday.',
      lastMessageTime: _now.subtract(const Duration(hours: 2)),
      unreadCount: 1,
      subject: 'Mathematics Assignment',
    ),
    Thread(
      id: '2',
      participant: const Participant(id: 2, name: 'Mrs. Kilonzo', role: 'Class Teacher', avatarUrl: 'https://i.pravatar.cc/150?img=62'),
      lastMessage: 'PTA meeting is scheduled for Saturday at 10 AM.',
      lastMessageTime: _now.subtract(const Duration(hours: 5)),
      unreadCount: 0,
      subject: 'PTA Meeting',
    ),
    Thread(
      id: '3',
      participant: const Participant(id: 3, name: 'School Admin', role: 'Admin', avatarUrl: 'https://i.pravatar.cc/150?img=63'),
      lastMessage: 'Exam card clearance deadline has been extended.',
      lastMessageTime: _now.subtract(const Duration(days: 1)),
      unreadCount: 2,
      subject: 'Exam Clearance',
    ),
  ];

  static List<Announcement> get announcements => [
    Announcement(
      id: '1',
      title: 'Sports Day Postponed',
      body: 'Sports day has been moved to next term due to weather conditions.',
      time: _now.subtract(const Duration(days: 2)),
      priority: 'high',
      isRead: false,
    ),
    Announcement(
      id: '2',
      title: 'Library Hours Extended',
      body: 'The library will remain open until 7 PM during the exam period.',
      time: _now.subtract(const Duration(days: 3)),
      priority: 'normal',
      isRead: true,
    ),
  ];

  static InboxSummary get inboxSummary => InboxSummary(
    totalThreads: threads.length,
    unreadCount: threads.fold<int>(0, (sum, t) => sum + t.unreadCount),
    urgentCount: 1,
    announcementCount: announcements.where((a) => !a.isRead).length,
    draftCount: 0,
  );
}
