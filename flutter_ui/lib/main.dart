import 'package:flutter/material.dart';

import 'app_router.dart';
import 'theme/app_theme.dart';

void main() {
  runApp(const ShuleYetuUiApp());
}

class ShuleYetuUiApp extends StatelessWidget {
  const ShuleYetuUiApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Kenya 1 / Shule Yetu UI',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      initialRoute: AppRouter.kenyaHome,
      routes: AppRouter.routes,
    );
  }
}
