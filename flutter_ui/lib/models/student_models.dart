/// Student data models.
library student_models;

/// Assignment model for student view.
class Assignment {
  const Assignment({
    required this.id,
    required this.title,
    required this.subject,
    required this.type,
    required this.status,
    this.dueDate,
    this.assignedDate,
    required this.maxScore,
    this.score,
    this.percentage,
    required this.teacherName,
    this.attachments = const [],
    this.submission,
    this.examId,
    this.examTitle,
  });

  final int id;
  final String title;
  final String subject;
  final String type; // homework, exam, project
  final String status; // pending, submitted, graded, overdue
  final DateTime? dueDate;
  final DateTime? assignedDate;
  final int maxScore;
  final double? score;
  final double? percentage;
  final String teacherName;
  final List<String> attachments;
  final SubmissionInfo? submission;
  final int? examId;
  final String? examTitle;

  factory Assignment.fromJson(Map<String, dynamic> json) {
    return Assignment(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      subject: json['subject'] as String? ?? '',
      type: json['type'] as String? ?? 'assignment',
      status: json['status'] as String? ?? 'pending',
      dueDate: json['due_date'] != null ? DateTime.tryParse(json['due_date'] as String) : null,
      assignedDate: json['assigned_date'] != null ? DateTime.tryParse(json['assigned_date'] as String) : null,
      maxScore: json['max_score'] as int? ?? 100,
      score: (json['score'] as num?)?.toDouble(),
      percentage: (json['percentage'] as num?)?.toDouble(),
      teacherName: json['teacher_name'] as String? ?? 'TBD',
      attachments: (json['attachments'] as List?)?.map((e) => e as String).toList() ?? [],
      submission: json['submission'] != null ? SubmissionInfo.fromJson(json['submission'] as Map<String, dynamic>) : null,
      examId: json['exam_id'] as int?,
      examTitle: json['exam_title'] as String?,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'subject': subject,
        'type': type,
        'status': status,
        'due_date': dueDate?.toIso8601String(),
        'assigned_date': assignedDate?.toIso8601String(),
        'max_score': maxScore,
        'score': score,
        'percentage': percentage,
        'teacher_name': teacherName,
        'attachments': attachments,
        'submission': submission?.toJson(),
        'exam_id': examId,
        'exam_title': examTitle,
      };

  bool get isPending => status == 'pending';
  bool get isSubmitted => status == 'submitted';
  bool get isGraded => status == 'graded';
  bool get isOverdue => status == 'overdue';
  bool get isCompleted => isSubmitted || isGraded;

  String get statusLabel {
    switch (status) {
      case 'pending':
        return 'Pending';
      case 'submitted':
        return 'Submitted';
      case 'graded':
        return 'Graded';
      case 'overdue':
        return 'Overdue';
      default:
        return status;
    }
  }

  String get formattedDueDate {
    if (dueDate == null) return 'No due date';
    final now = DateTime.now();
    final diff = dueDate!.difference(now).inDays;
    if (diff < 0) return 'Overdue';
    if (diff == 0) return 'Due today';
    if (diff == 1) return 'Due tomorrow';
    return 'Due in $diff days';
  }
}

/// Submission information.
class SubmissionInfo {
  const SubmissionInfo({
    required this.submittedAt,
    this.gradedAt,
    this.feedback,
  });

  final DateTime? submittedAt;
  final DateTime? gradedAt;
  final String? feedback;

  factory SubmissionInfo.fromJson(Map<String, dynamic> json) {
    return SubmissionInfo(
      submittedAt: json['submitted_at'] != null ? DateTime.tryParse(json['submitted_at'] as String) : null,
      gradedAt: json['graded_at'] != null ? DateTime.tryParse(json['graded_at'] as String) : null,
      feedback: json['feedback'] as String?,
    );
  }

  Map<String, dynamic> toJson() => {
        'submitted_at': submittedAt?.toIso8601String(),
        'graded_at': gradedAt?.toIso8601String(),
        'feedback': feedback,
      };
}

/// Attendance record for student.
class AttendanceRecord {
  const AttendanceRecord({
    required this.id,
    required this.date,
    required this.status,
    required this.statusLabel,
    required this.statusColor,
    required this.className,
    required this.subject,
    required this.markedBy,
    this.checkInTime,
    this.checkOutTime,
    this.notes,
    this.metadata,
  });

  final int id;
  final DateTime date;
  final String status; // present, absent, late, excused
  final String statusLabel;
  final String statusColor;
  final String className;
  final String subject;
  final String markedBy;
  final String? checkInTime;
  final String? checkOutTime;
  final String? notes;
  final Map<String, dynamic>? metadata;

  factory AttendanceRecord.fromJson(Map<String, dynamic> json) {
    return AttendanceRecord(
      id: json['id'] as int,
      date: DateTime.tryParse(json['date'] as String? ?? '') ?? DateTime.now(),
      status: json['status'] as String? ?? 'present',
      statusLabel: json['status_label'] as String? ?? '',
      statusColor: json['status_color'] as String? ?? '',
      className: json['class_name'] as String? ?? '',
      subject: json['subject'] as String? ?? '',
      markedBy: json['marked_by'] as String? ?? '',
      checkInTime: json['check_in_time'] as String?,
      checkOutTime: json['check_out_time'] as String?,
      notes: json['notes'] as String?,
      metadata: json['metadata'] as Map<String, dynamic>?,
    );
  }

  bool get isPresent => status == 'present';
  bool get isAbsent => status == 'absent';
  bool get isLate => status == 'late';
  bool get isExcused => status == 'excused';
}

/// Class schedule for student.
class ClassSchedule {
  const ClassSchedule({
    required this.id,
    required this.name,
    required this.teacher,
    this.teacherId,
    required this.room,
    required this.color,
    required this.schedule,
    this.streamId,
    this.assignmentIds = const [],
  });

  final int id;
  final String name;
  final String teacher;
  final int? teacherId;
  final String room;
  final String color;
  final List<ScheduleEntry> schedule;
  final int? streamId;
  final List<int> assignmentIds;

  factory ClassSchedule.fromJson(Map<String, dynamic> json) {
    return ClassSchedule(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      teacher: json['teacher'] as String? ?? 'TBD',
      teacherId: json['teacher_id'] as int?,
      room: json['room'] as String? ?? 'TBD',
      color: json['color'] as String? ?? '#1E88E5',
      schedule: (json['schedule'] as List? ?? [])
          .map((e) => ScheduleEntry.fromJson(e as Map<String, dynamic>))
          .toList(),
      streamId: json['stream_id'] as int?,
      assignmentIds: (json['assignment_ids'] as List?)?.map((e) => e as int).toList() ?? [],
    );
  }

  List<ScheduleEntry> getTodaySchedule() {
    final today = DateTime.now().weekday; // 1 = Monday
    return schedule.where((s) => s.day == today).toList();
  }

  ScheduleEntry? getCurrentOrNextClass() {
    final now = DateTime.now();
    final todaySchedule = getTodaySchedule()..sort((a, b) => a.startTime.compareTo(b.startTime));

    for (final entry in todaySchedule) {
      final endTime = entry.endTime;
      if (endTime.isAfter(now)) return entry;
    }
    return todaySchedule.isNotEmpty ? todaySchedule.first : null;
  }
}

/// Schedule entry.
class ScheduleEntry {
  const ScheduleEntry({
    required this.day,
    required this.dayName,
    required this.period,
    required this.start,
    required this.end,
    required this.room,
    this.subjectName = '',
  });

  final int day; // 1-5 (Mon-Fri)
  final String dayName;
  final int period;
  final String start;
  final String end;
  final String room;
  final String subjectName;

  DateTime get startTime => _parseTime(start);
  DateTime get endTime => _parseTime(end);

  DateTime _parseTime(String timeStr) {
    final parts = timeStr.split(':');
    final now = DateTime.now();
    return DateTime(now.year, now.month, now.day, int.parse(parts[0]), int.parse(parts[1]));
  }

  factory ScheduleEntry.fromJson(Map<String, dynamic> json) {
    return ScheduleEntry(
      day: json['day'] as int? ?? 1,
      dayName: json['day_name'] as String? ?? '',
      period: json['period'] as int? ?? 1,
      start: json['start'] as String? ?? '08:00',
      end: json['end'] as String? ?? '08:50',
      room: json['room'] as String? ?? 'TBD',
      subjectName: json['subject_name'] as String? ?? json['subject'] as String? ?? '',
    );
  }
}

/// Assignment summary for overview.
class AssignmentSummary {
  const AssignmentSummary({
    required this.total,
    required this.pending,
    required this.submitted,
    required this.graded,
    required this.overdue,
  });

  final int total;
  final int pending;
  final int submitted;
  final int graded;
  final int overdue;

  factory AssignmentSummary.fromMap(Map<String, dynamic> map) {
    return AssignmentSummary(
      total: map['total'] as int? ?? 0,
      pending: map['pending'] as int? ?? 0,
      submitted: map['submitted'] as int? ?? 0,
      graded: map['graded'] as int? ?? 0,
      overdue: map['overdue'] as int? ?? 0,
    );
  }

  int get completed => submitted + graded;
  double get completionRate => total > 0 ? completed / total : 0;
}

/// Attendance summary for overview.
class AttendanceSummary {
  const AttendanceSummary({
    required this.present,
    required this.absent,
    required this.late,
    required this.excused,
    required this.rate,
  });

  final int present;
  final int absent;
  final int late;
  final int excused;
  final double rate;

  factory AttendanceSummary.fromMap(Map<String, dynamic> map) {
    return AttendanceSummary(
      present: map['present'] as int? ?? 0,
      absent: map['absent'] as int? ?? 0,
      late: map['late'] as int? ?? 0,
      excused: map['excused'] as int? ?? 0,
      rate: (map['rate'] as num?)?.toDouble() ?? 100.0,
    );
  }

  int get total => present + absent + late + excused;
}