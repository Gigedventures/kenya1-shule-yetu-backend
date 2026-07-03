/// K1 AI Engine — prediction, detection, analysis, and planning models.
///
/// Maps to \App\K1\Ai\Services\* backend responses.

/// Predicted student performance result
class StudentPerformancePrediction {
  final double predictedAverage;
  final String riskLevel;
  final List<String> strongestSubjects;
  final List<String> weakestSubjects;
  final double confidenceScore;

  const StudentPerformancePrediction({
    required this.predictedAverage,
    required this.riskLevel,
    this.strongestSubjects = const [],
    this.weakestSubjects = const [],
    required this.confidenceScore,
  });

  factory StudentPerformancePrediction.fromJson(Map<String, dynamic> json) {
    return StudentPerformancePrediction(
      predictedAverage: (json['predicted_average'] as num?)?.toDouble() ?? 0.0,
      riskLevel: json['risk_level']?.toString() ?? 'unknown',
      strongestSubjects: (json['strongest_subjects'] as List?)?.cast<String>() ?? [],
      weakestSubjects: (json['weakest_subjects'] as List?)?.cast<String>() ?? [],
      confidenceScore: (json['confidence_score'] as num?)?.toDouble() ?? 0.0,
    );
  }

  bool get isLowRisk => riskLevel == 'low';
  bool get isMediumRisk => riskLevel == 'medium';
  bool get isHighRisk => riskLevel == 'high';
}

/// At-risk student detection result
class AtRiskResult {
  final int riskScore;
  final String riskLevel;
  final List<String> reasons;
  final List<String> recommendedActions;

  const AtRiskResult({
    required this.riskScore,
    required this.riskLevel,
    this.reasons = const [],
    this.recommendedActions = const [],
  });

  factory AtRiskResult.fromJson(Map<String, dynamic> json) {
    return AtRiskResult(
      riskScore: (json['risk_score'] as num?)?.toInt() ?? 0,
      riskLevel: json['risk_level']?.toString() ?? 'unknown',
      reasons: (json['reasons'] as List?)?.cast<String>() ?? [],
      recommendedActions: (json['recommended_actions'] as List?)?.cast<String>() ?? [],
    );
  }

  bool get isAtRisk => riskScore >= 40;
  bool get isCritical => riskScore >= 70;
}

/// CBC competency gap analysis result
class CompetencyGap {
  final String subject;
  final double currentAverage;
  final double expected;
  final double gap;
  final String severity;

  const CompetencyGap({
    required this.subject,
    required this.currentAverage,
    required this.expected,
    required this.gap,
    required this.severity,
  });

  factory CompetencyGap.fromJson(Map<String, dynamic> json) {
    return CompetencyGap(
      subject: json['subject']?.toString() ?? '',
      currentAverage: (json['current_average'] as num?)?.toDouble() ?? 0.0,
      expected: (json['expected'] as num?)?.toDouble() ?? 0.0,
      gap: (json['gap'] as num?)?.toDouble() ?? 0.0,
      severity: json['severity']?.toString() ?? 'unknown',
    );
  }
}

/// Full competency gap analysis result
class CompetencyGapResult {
  final List<CompetencyGap> competencyGaps;
  final List<String> strengths;
  final List<String> interventions;

  const CompetencyGapResult({
    this.competencyGaps = const [],
    this.strengths = const [],
    this.interventions = const [],
  });

  factory CompetencyGapResult.fromJson(Map<String, dynamic> json) {
    return CompetencyGapResult(
      competencyGaps: (json['competency_gaps'] as List?)
          ?.map((e) => CompetencyGap.fromJson(e as Map<String, dynamic>))
          .toList() ?? [],
      strengths: (json['strengths'] as List?)?.cast<String>() ?? [],
      interventions: (json['interventions'] as List?)?.cast<String>() ?? [],
    );
  }
}

/// A single day from a 14-day learning plan
class LearningPlanDay {
  final int day;
  final List<String> focus;
  final List<String> review;
  final double hours;

  const LearningPlanDay({
    required this.day,
    this.focus = const [],
    this.review = const [],
    required this.hours,
  });

  factory LearningPlanDay.fromJson(Map<String, dynamic> json) {
    return LearningPlanDay(
      day: (json['day'] as num?)?.toInt() ?? 0,
      focus: (json['focus'] as List?)?.cast<String>() ?? [],
      review: (json['review'] as List?)?.cast<String>() ?? [],
      hours: (json['hours'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

/// Full learning plan result
class LearningPlanResult {
  final List<LearningPlanDay> plan;
  final int totalSessions;
  final double estimatedHours;

  const LearningPlanResult({
    this.plan = const [],
    this.totalSessions = 0,
    this.estimatedHours = 0.0,
  });

  factory LearningPlanResult.fromJson(Map<String, dynamic> json) {
    return LearningPlanResult(
      plan: (json['plan'] as List?)
          ?.map((e) => LearningPlanDay.fromJson(e as Map<String, dynamic>))
          .toList() ?? [],
      totalSessions: (json['total_sessions'] as num?)?.toInt() ?? 0,
      estimatedHours: (json['estimated_hours'] as num?)?.toDouble() ?? 0.0,
    );
  }

  double get average => totalSessions > 0 ? estimatedHours / totalSessions : 0.0;
}