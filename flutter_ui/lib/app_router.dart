import 'package:flutter/material.dart';

import 'data/mock_users.dart';
import 'screens/juniors_parent_dashboard_screen.dart';
import 'screens/k1_home_desktop_screen.dart';
import 'screens/seniors_student_dashboard_screen.dart';
import 'screens/shule_yetu_selector_screen.dart';
import 'screens/student/student_dashboard_router_screen.dart';
import 'screens/student/assignments_list_screen.dart';
import 'screens/student/attendance_list_screen.dart';
import 'screens/student/classes_list_screen.dart';
import 'screens/student/message_thread_screen.dart';
import 'models/message_models.dart';

class AppRouter {
  static const kenyaHome = '/';
  static const shuleYetuSelector = '/shule-yetu-selector';
  static const juniors = '/juniors';
  static const seniors = '/seniors';
  static const studentDashboard = '/student-dashboard';
  static const studentAssignments = '/student/assignments';
  static const studentAttendance = '/student/attendance';
  static const studentClasses = '/student/classes';
  static const studentMessages = '/student/messages';

  static Map<String, WidgetBuilder> get routes => {
        kenyaHome: (_) => const K1HomeDesktopScreen(),
        shuleYetuSelector: (_) => const ShuleYetuSelectorScreen(),
        juniors: (_) => const JuniorsParentDashboardScreen(),
        seniors: (_) => const SeniorsStudentDashboardScreen(student: MockUsersData.seniorDemoStudent),
        studentDashboard: (_) => const StudentDashboardRouterScreen(student: MockUsersData.tertiaryDemoStudent),
        studentAssignments: (_) => const StudentAssignmentsListScreen(),
        studentAttendance: (_) => const StudentAttendanceListScreen(),
        studentClasses: (_) => const StudentClassesListScreen(),
        studentMessages: (_) => MessageThreadScreen(thread: Thread(
          id: 'demo',
          participant: const Participant(id: 0, name: 'Demo', role: 'Teacher'),
          lastMessage: 'Demo message',
          lastMessageTime: DateTime.now(),
          unreadCount: 0,
        )),
      };
}