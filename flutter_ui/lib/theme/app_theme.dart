import 'package:flutter/material.dart';
import 'k1_colors.dart';

class AppTheme {
  static const _navy = Color(0xFF11325F);
  static const _blue = Color(0xFF1E61C6);
  static const _grayBg = K1Colors.page;

  static ThemeData get light => ThemeData(
        useMaterial3: true,
        scaffoldBackgroundColor: _grayBg,
        colorScheme: ColorScheme.fromSeed(
          seedColor: _blue,
          primary: _blue,
          secondary: const Color(0xFFF9A825),
        ),
        textTheme: const TextTheme(
          headlineMedium: TextStyle(
            fontWeight: FontWeight.w800,
            color: _navy,
            letterSpacing: -0.2,
          ),
          titleLarge: TextStyle(fontWeight: FontWeight.w800, color: _navy),
          titleMedium: TextStyle(fontWeight: FontWeight.w700, color: _navy),
          bodyLarge: TextStyle(fontWeight: FontWeight.w500, color: Color(0xFF27364F)),
          bodyMedium: TextStyle(fontWeight: FontWeight.w500, color: Color(0xFF3C4B63)),
        ),
        cardTheme: CardThemeData(
          color: Colors.white,
          elevation: 1.5,
          margin: const EdgeInsets.all(0),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
            side: const BorderSide(color: K1Colors.border),
          ),
        ),
        dividerColor: K1Colors.border,
      );
}
