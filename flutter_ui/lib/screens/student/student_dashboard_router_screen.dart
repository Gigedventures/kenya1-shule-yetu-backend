import 'package:flutter/material.dart';

import '../../data/mock_users.dart';
import 'junior/junior_student_dashboard_screen.dart';
import 'primary/primary_student_dashboard_screen.dart';
import 'senior/senior_student_dashboard_screen.dart';
import 'tertiary/tertiary_student_dashboard_screen.dart';

// Router for the four Shule Yetu student dashboard levels.
class StudentDashboardRouterScreen extends StatelessWidget {
  const StudentDashboardRouterScreen({
    super.key,
    required this.student,
  });

  final Student student;

  @override
  Widget build(BuildContext context) {
    switch (student.schoolLevel) {
      case SchoolLevel.primary:
        return PrimaryStudentDashboardScreen(student: student);
      case SchoolLevel.junior:
        return JuniorStudentDashboardScreen(student: student);
      case SchoolLevel.senior:
        return SeniorStudentDashboardScreen(student: student);
      case SchoolLevel.tertiary:
        return TertiaryStudentDashboardScreen(student: student);
    }
  }
}
