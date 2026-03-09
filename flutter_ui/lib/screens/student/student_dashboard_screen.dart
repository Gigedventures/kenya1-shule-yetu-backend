import 'package:flutter/material.dart';

import '../../data/mock_users.dart';
import 'student_dashboard_router_screen.dart';

class StudentDashboardScreen extends StatelessWidget {
  const StudentDashboardScreen({
    super.key,
    this.student = MockUsersData.primaryDemoStudent,
  });

  final Student student;

  @override
  Widget build(BuildContext context) {
    return StudentDashboardRouterScreen(student: student);
  }
}
