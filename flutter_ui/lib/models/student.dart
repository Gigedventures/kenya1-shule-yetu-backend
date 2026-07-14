enum SchoolLevel { primary, junior, senior, tertiary }

class Student {
  const Student({
    required this.id,
    required this.name,
    required this.className,
    required this.guardianId,
    required this.schoolLevel,
    required this.admissionNumber,
    required this.avatarUrl,
    required this.school,
    required this.grade,
    required this.transport,
    required this.homeworkCount,
    required this.attendance,
    required this.teacher,
    required this.subjects,
    required this.assignments,
    required this.homework,
    required this.examScores,
    required this.busRoute,
    required this.badges,
    this.guardianIds = const [],
    this.club,
    this.leaderboardScore,
    this.revisionPapers = const [],
    this.mockExamScore,
    this.careerInterest,
    this.course,
    this.year,
    this.semester,
    this.credits,
    this.gpa,
  });

  final int id;
  final String name;
  final String className;
  final int guardianId;
  final List<int> guardianIds;
  final SchoolLevel schoolLevel;
  final String admissionNumber;
  final String avatarUrl;
  final String school;
  final int grade;
  final bool transport;
  final int homeworkCount;
  final double attendance;
  final String teacher;
  final List<String> subjects;
  final List<String> assignments;
  final List<String> homework;
  final Map<String, double> examScores;
  final String busRoute;
  final List<String> badges;
  final String? club;
  final int? leaderboardScore;
  final List<String> revisionPapers;
  final double? mockExamScore;
  final String? careerInterest;
  final String? course;
  final int? year;
  final String? semester;
  final int? credits;
  final double? gpa;

  SchoolLevel get level => schoolLevel;
  SchoolLevel get school_level => schoolLevel;

  String get fullName => name;
}
