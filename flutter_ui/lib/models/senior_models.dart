/// Senior secondary (Grade 10-12 / Senior 4-6) dashboard data models.
/// Maps to SeniorData in MockData and backend API responses.

/// A key performance indicator displayed on the senior dashboard
class SeniorKpi {
  final String label;
  final String value;
  final String icon;
  final String? color;

  const SeniorKpi({
    required this.label,
    required this.value,
    this.icon = '',
    this.color,
  });

  factory SeniorKpi.fromJson(Map<String, dynamic> json) {
    return SeniorKpi(
      label: json['label']?.toString() ?? '',
      value: json['value']?.toString() ?? '',
      icon: json['icon']?.toString() ?? '',
      color: json['color']?.toString(),
    );
  }
}

/// A schedule block on the senior timetable
class SeniorScheduleBlock {
  final String time;
  final String title;
  final String? location;
  final String? instructor;

  const SeniorScheduleBlock({
    required this.time,
    required this.title,
    this.location,
    this.instructor,
  });

  factory SeniorScheduleBlock.fromJson(Map<String, dynamic> json) {
    return SeniorScheduleBlock(
      time: json['time']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      location: json['location']?.toString(),
      instructor: json['instructor']?.toString(),
    );
  }
}

/// Full senior dashboard data
class SeniorDashboardData {
  final String studentName;
  final String? program;
  final String? semester;
  final List<SeniorKpi> kpis;
  final List<SeniorScheduleBlock> schedule;
  final List<String> announcements;
  final List<String> alerts;

  const SeniorDashboardData({
    required this.studentName,
    this.program,
    this.semester,
    this.kpis = const [],
    this.schedule = const [],
    this.announcements = const [],
    this.alerts = const [],
  });

  factory SeniorDashboardData.fromJson(Map<String, dynamic> json) {
    return SeniorDashboardData(
      studentName: json['student_name']?.toString() ?? '',
      program: json['program']?.toString(),
      semester: json['semester']?.toString(),
      kpis: (json['kpis'] as List? ?? [])
          .map((e) => SeniorKpi.fromJson(e as Map<String, dynamic>))
          .toList(),
      schedule: (json['schedule'] as List? ?? [])
          .map((e) => SeniorScheduleBlock.fromJson(e as Map<String, dynamic>))
          .toList(),
      announcements: (json['announcements'] as List? ?? []).cast<String>(),
      alerts: (json['alerts'] as List? ?? []).cast<String>(),
    );
  }
}