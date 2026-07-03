/// CBC (Competency-Based Curriculum) data models
/// Maps to App\Modules\ShuleYetu\* models in the backend.

/// A class/grade level in the CBC system (e.g. PP1, Grade 4, Senior 10)
class CbcClass {
  final String id;
  final String name;
  final String? level;
  final List<CbcStream> streams;

  const CbcClass({
    required this.id,
    required this.name,
    this.level,
    this.streams = const [],
  });

  factory CbcClass.fromJson(Map<String, dynamic> json) {
    return CbcClass(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      level: json['level']?.toString(),
      streams: (json['streams'] as List? ?? [])
          .map((e) => CbcStream.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

/// A stream within a class (e.g. A, B)
class CbcStream {
  final String id;
  final String name;
  final String? classId;
  final int? capacity;

  const CbcStream({
    required this.id,
    required this.name,
    this.classId,
    this.capacity,
  });

  factory CbcStream.fromJson(Map<String, dynamic> json) {
    return CbcStream(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      classId: json['class_id']?.toString(),
      capacity: json['capacity'] as int?,
    );
  }
}

/// A subject in the CBC curriculum (e.g. Mathematics, English)
class CbcSubject {
  final String id;
  final String name;
  final String? code;
  final bool isCore;

  const CbcSubject({
    required this.id,
    required this.name,
    this.code,
    this.isCore = false,
  });

  factory CbcSubject.fromJson(Map<String, dynamic> json) {
    return CbcSubject(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      code: json['code']?.toString(),
      isCore: json['is_core'] as bool? ?? false,
    );
  }
}

/// The full CBC setup state for a school
class CbcSetup {
  final List<CbcClass> classes;
  final List<CbcSubject> subjects;
  final String? message;

  const CbcSetup({
    this.classes = const [],
    this.subjects = const [],
    this.message,
  });

  factory CbcSetup.fromJson(Map<String, dynamic> json) {
    return CbcSetup(
      classes: (json['classes'] as List? ?? [])
          .map((e) => CbcClass.fromJson(e as Map<String, dynamic>))
          .toList(),
      subjects: (json['subjects'] as List? ?? [])
          .map((e) => CbcSubject.fromJson(e as Map<String, dynamic>))
          .toList(),
      message: json['message']?.toString(),
    );
  }
}