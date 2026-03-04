import 'package:flutter/material.dart';

import 'screens/juniors_parent_dashboard_screen.dart';
import 'screens/k1_home_desktop_screen.dart';
import 'screens/seniors_student_dashboard_screen.dart';
import 'screens/shule_yetu_selector_screen.dart';

class AppRouter {
  static const kenyaHome = '/';
  static const shuleYetuSelector = '/shule-yetu-selector';
  static const juniors = '/juniors';
  static const seniors = '/seniors';

  static Map<String, WidgetBuilder> get routes => {
        kenyaHome: (_) => const K1HomeDesktopScreen(),
        shuleYetuSelector: (_) => const ShuleYetuSelectorScreen(),
        juniors: (_) => const JuniorsParentDashboardScreen(),
        seniors: (_) => const SeniorsStudentDashboardScreen(),
      };
}
