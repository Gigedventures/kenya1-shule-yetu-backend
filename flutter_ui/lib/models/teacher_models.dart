/// Teacher data models.
library teacher_models;

/// Teacher assignment model.
class TeacherAssignment {
  const TeacherAssignment({
    required this.id,
    required this.title,
    required this.subject,
    this.subjectId,
    required this.className,
    this.classId,
    this.stream,
    this.streamId,
    required this.type,
    required this.examType,
    required this.examTitle,
    required this.examId,
    required this.dueDate,
    required this.assignedDate,
    required this.maxScore,
    required this.weightPercentage,
    required this.status,
    required this.submissionStats,
    this.attachments = const [],
  });

  final int id;
  final String title;
  final String subject;
  final int? subjectId;
  final String className;
  final int? classId;
  final String? stream;
  final int? streamId;
  final String type;
  final String examType;
  final String examTitle;
  final int examId;
  final DateTime? dueDate;
  final DateTime? assignedDate;
  final int maxScore;
  final int weightPercentage;
  final String status;
  final SubmissionStats submissionStats;
  final List<String> attachments;

  factory TeacherAssignment.fromJson(Map<String, dynamic> json) {
    return TeacherAssignment(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      subject: json['subject'] as String? ?? '',
      subjectId: json['subject_id'] as int?,
      className: json['class'] as String? ?? '',
      classId: json['class_id'] as int?,
      stream: json['stream'] as String?,
      streamId: json['stream_id'] as int?,
      type: json['type'] as String? ?? 'exam',
      examType: json['exam_type'] as String? ?? '',
      examTitle: json['exam_title'] as String? ?? '',
      examId: json['exam_id'] as int? ?? 0,
      dueDate: json['due_date'] != null ? DateTime.tryParse(json['due_date'] as String) : null,
      assignedDate: json['assigned_date'] != null ? DateTime.tryParse(json['assigned_date'] as String) : null,
      maxScore: json['max_score'] as int? ?? 100,
      weightPercentage: json['weight_percentage'] as int? ?? 100,
      status: json['status'] as String? ?? 'draft',
      submissionStats: SubmissionStats.fromJson(json['submission_stats'] as Map<String, dynamic>? ?? {}),
      attachments: (json['attachments'] as List?)?.map((e) => e as String).toList() ?? [],
    );
  }

  bool get needsGrading => submissionStats.graded < submissionStats.submitted;
  double get completionRate => submissionStats.completionRate;
  String get statusLabel => status[0].toUpperCase() + status.substring(1);
}

/// Submission statistics for teacher assignment.
class SubmissionStats {
  const SubmissionStats({
    required this.total,
    required this.submitted,
    required this.graded,
    required this.pending,
    this.average,
  });

  final int total;
  final int submitted;
  final int graded;
  final int pending;
  final double? average;

  factory SubmissionStats.fromJson(Map<String, dynamic> json) {
    return SubmissionStats(
      total: json['total'] as int? ?? 0,
      submitted: json['submitted'] as int? ?? 0,
      graded: json['graded'] as int? ?? 0,
      pending: json['pending'] as int? ?? 0,
      average: (json['average'] as num?)?.toDouble(),
    );
  }

  double get completionRate => total > 0 ? submitted / total : 0;
  double get gradingRate => submitted > 0 ? graded / submitted : 0;
}

/// Attendance record for teacher view.
class AttendanceRecord {
  const AttendanceRecord({
    required this.id,
    required this.studentId,
    required this.studentName,
    this.admissionNo,
    required this.date,
    required this.status,
    required this.statusLabel,
    required this.statusColor,
    required this.className,
    this.streamName,
    required this.subject,
    required this.markedBy,
    this.checkInTime,
    this.checkOutTime,
    this.notes,
  });

  final int id;
  final int studentId;
  final String studentName;
  final String? admissionNo;
  final DateTime date;
  final String status;
  final String statusLabel;
  final String statusColor;
  final String className;
  final String? streamName;
  final String subject;
  final String markedBy;
  final String? checkInTime;
  final String? checkOutTime;
  final String? notes;

  factory AttendanceRecord.fromJson(Map<String, dynamic> json) {
    return AttendanceRecord(
      id: json['id'] as int,
      studentId: json['student_id'] as int,
      studentName: json['student_name'] as String? ?? '',
      admissionNo: json['admission_no'] as String?,
      date: DateTime.tryParse(json['date'] as String? ?? '') ?? DateTime.now(),
      status: json['status'] as String? ?? 'present',
      statusLabel: json['status_label'] as String? ?? '',
      statusColor: json['status_color'] as String? ?? '',
      className: json['class_name'] as String? ?? '',
      streamName: json['stream_name'] as String?,
      subject: json['subject'] as String? ?? '',
      markedBy: json['marked_by'] as String? ?? '',
      checkInTime: json['check_in_time'] as String?,
      checkOutTime: json['check_out_time'] as String?,
      notes: json['notes'] as String?,
    );
  }

  bool get isPresent => status == 'present';
  bool get isAbsent => status == 'absent';
  bool get isLate => status == 'late';
  bool get isExcused => status == 'excused';
}

/// Attendance stats per class for teacher.
class AttendanceStats {
  const AttendanceStats({
    required this.classId,
    required this.present,
    required this.absent,
    required this.late,
    required this.excused,
    required this.total,
    required this.rate,
  });

  final int classId;
  final int present;
  final int absent;
  final int late;
  final int excused;
  final int total;
  final double rate;

  factory AttendanceStats.fromJson(Map<String, dynamic> json) {
    return AttendanceStats(
      classId: json['class_id'] as int,
      present: json['present'] as int? ?? 0,
      absent: json['absent'] as int? ?? 0,
      late: json['late'] as int? ?? 0,
      excused: json['excused'] as int? ?? 0,
      total: json['total'] as int? ?? 0,
      rate: (json['rate'] as num?)?.toDouble() ?? 100.0,
    );
  }
}

/// Teacher assignment summary.
class TeacherAssignmentSummary {
  const TeacherAssignmentSummary({
    required this.total,
    required this.byStatus,
    required this.byClass,
    required this.bySubject,
    required this.pendingGrading,
  });

  final int total;
  final Map<String, int> byStatus;
  final Map<String, int> byClass;
  final Map<String, int> bySubject;
  final int pendingGrading;

  factory TeacherAssignmentSummary.fromMap(Map<String, dynamic> map) {
    return TeacherAssignmentSummary(
      total: map['total'] as int? ?? 0,
      byStatus: Map<String, int>.from(map['by_status'] as Map? ?? {}),
      byClass: Map<String, int>.from(map['by_class'] as Map? ?? {}),
      bySubject: Map<String, int>.from(map['by_subject'] as Map? ?? {}),
      pendingGrading: map['pending_grading'] as int? ?? 0,
    );
  }
}