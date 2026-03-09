import 'package:flutter/material.dart';

import '../data/mock_users.dart';
import 'student/student_dashboard_router_screen.dart';

class SeniorsStudentDashboardScreen extends StatelessWidget {
  const SeniorsStudentDashboardScreen({
    super.key,
    this.student = MockUsersData.tertiaryDemoStudent,
  });

  final Student student;

  @override
  Widget build(BuildContext context) {
    return StudentDashboardRouterScreen(student: student);
  }
}
