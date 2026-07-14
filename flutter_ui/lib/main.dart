import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'app_router.dart';
import 'services/k1_api_service.dart';
import 'theme/app_theme.dart';

void main() {
  runApp(const ShuleYetuUiApp());
}

class ShuleYetuUiApp extends StatelessWidget {
  const ShuleYetuUiApp({super.key});

  @override
  Widget build(BuildContext context) {
    return Provider<K1ApiService>(
      create: (_) => K1ApiService(
        baseUrl: const String.fromEnvironment('API_BASE_URL', defaultValue: 'http://localhost:8000'),
        tokenProvider: () async => null,
      ),
      child: MaterialApp(
        title: 'Kenya 1 / Shule Yetu UI',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        initialRoute: AppRouter.kenyaHome,
        routes: AppRouter.routes,
      ),
    );
  }
}
