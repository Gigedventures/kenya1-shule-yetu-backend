/// Student classes provider.
library student_classes_provider;

import 'package:flutter/material.dart';
import '../repositories/student_repository.dart';
import '../../models/student_models.dart';

enum StudentClassesState { idle, loading, loaded, error }

class StudentClassesProvider extends ChangeNotifier {
  StudentClassesProvider({required StudentRepository repository}) : _repository = repository;

  final StudentRepository _repository;

  StudentClassesState _state = StudentClassesState.idle;
  List<ClassSchedule> _classes = [];
  String? _errorMessage;

  StudentClassesState get state => _state;
  List<ClassSchedule> get classes => _classes;
  String? get errorMessage => _errorMessage;
  bool get isLoading => _state == StudentClassesState.loading;
  bool get hasError => _state == StudentClassesState.error;
  bool get hasData => _classes.isNotEmpty;

  Future<void> load({bool refresh = false}) async {
    _setState(StudentClassesState.loading);

    try {
      _classes = await _repository.getClasses();
      _setState(StudentClassesState.loaded);
    } catch (e) {
      _errorMessage = e.toString();
      _setState(StudentClassesState.error);
    }
  }

  Future<void> refresh() async {
    await load(refresh: true);
  }

  ClassSchedule? getClassById(int id) {
    try {
      return _classes.firstWhere((c) => c.id == id);
    } catch (_) {
      return null;
    }
  }

  ClassSchedule? getCurrentClass() {
    return _classes.firstWhere(
      (c) => c.getCurrentOrNextClass() != null,
      orElse: () => _classes.isNotEmpty ? _classes.first : throw StateError('No classes'),
    );
  }

  List<ScheduleEntry> getTodaySchedule() {
    final current = getCurrentClass();
    return current?.getTodaySchedule() ?? [];
  }

  void _setState(StudentClassesState newState) {
    _state = newState;
    notifyListeners();
  }
}