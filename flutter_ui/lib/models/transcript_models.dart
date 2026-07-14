class AcademicTranscript {
  const AcademicTranscript({
    required this.student,
    required this.terms,
    required this.cumulative,
  });

  final TranscriptStudent student;
  final List<TranscriptTerm> terms;
  final TranscriptCumulative cumulative;

  factory AcademicTranscript.fromJson(Map<String, dynamic> json) {
    return AcademicTranscript(
      student: TranscriptStudent.fromJson(json['student'] as Map<String, dynamic>? ?? {}),
      terms: (json['terms'] as List? ?? [])
          .map((e) => TranscriptTerm.fromJson(e))
          .toList(),
      cumulative: TranscriptCumulative.fromJson(json['cumulative'] as Map<String, dynamic>? ?? {}),
    );
  }

  double get cumulativeAverage => cumulative.average ?? 0.0;
  int get totalTerms => cumulative.totalTerms ?? 0;
  double get gpa => cumulativeAverage;
}

class TranscriptStudent {
  const TranscriptStudent({
    required this.id,
    this.name,
    this.admissionNo,
    this.currentClassId,
  });

  final String id;
  final String? name;
  final String? admissionNo;
  final String? currentClassId;

  factory TranscriptStudent.fromJson(Map<String, dynamic> json) {
    return TranscriptStudent(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString(),
      admissionNo: json['admission_no']?.toString(),
      currentClassId: json['current_class_id']?.toString(),
    );
  }
}

class TranscriptTerm {
  const TranscriptTerm({
    required this.term,
    this.totalMarks,
    this.totalPercentage,
    this.average,
    this.overallGrade,
    this.rank,
  });

  final TermInfo term;
  final double? totalMarks;
  final double? totalPercentage;
  final double? average;
  final String? overallGrade;
  final int? rank;

  factory TranscriptTerm.fromJson(Map<String, dynamic> json) {
    return TranscriptTerm(
      term: TermInfo.fromJson(json['term'] as Map<String, dynamic>? ?? {}),
      totalMarks: (json['total_marks'] as num?)?.toDouble(),
      totalPercentage: (json['total_percentage'] as num?)?.toDouble(),
      average: (json['average'] as num?)?.toDouble(),
      overallGrade: json['overall_grade']?.toString(),
      rank: json['rank'] as int?,
    );
  }

  String get gradeLabel => overallGrade ?? 'N/A';
  double get percentage => average ?? 0.0;
}

class TermInfo {
  const TermInfo({
    this.id,
    this.name,
    this.academicYearId,
  });

  final String? id;
  final String? name;
  final String? academicYearId;

  factory TermInfo.fromJson(Map<String, dynamic> json) {
    return TermInfo(
      id: json['id']?.toString(),
      name: json['name']?.toString(),
      academicYearId: json['academic_year_id']?.toString(),
    );
  }
}

class TranscriptCumulative {
  const TranscriptCumulative({
    this.totalTerms,
    this.average,
    this.highestGrade,
  });

  final int? totalTerms;
  final double? average;
  final String? highestGrade;

  factory TranscriptCumulative.fromJson(Map<String, dynamic> json) {
    return TranscriptCumulative(
      totalTerms: json['total_terms'] as int?,
      average: (json['average'] as num?)?.toDouble(),
      highestGrade: json['highest_grade']?.toString(),
    );
  }
}