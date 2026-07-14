import 'package:flutter/material.dart';

enum K1Module {
  wallet,
  shule,
  pharmacy,
  chamaa,
  transport,
  civic,
  food,
  general,
}

class K1Colors {
  static const page = Color(0xFFEEF2F7);
  static const surface = Colors.white;
  static const border = Color(0xFFD8E0EC);
  static const text = Color(0xFF183251);
  static const muted = Color(0xFF5F7592);
  static const sidebar = Color(0xFFE8EDF5);
  static const sidebarActive = Color(0xFFDDE8FA);
  static const sidebarRail = Color(0xFFDCE4EF);
  static const orange = Color(0xFFF58A1F);
  static const orangeDark = Color(0xFFDB6A00);
  static const orangeSoft = Color(0xFFFFF0DE);
  static const cardBg = Color(0xFF1A2438);
  static const cardBorder = Color(0xFF2D3E5A);
  static const panelBg = Color(0xFF0E1420);
  static const panelBorder = Color(0xFF2D3E5A);
  static const nav = Color(0xFF91A4C0);
  static const navActive = Color(0xFF61A4FF);
  static const primary = Color(0xFF2A6CC4);
  static const primaryDark = Color(0xFF1F5DB8);
  static const accent = Color(0xFF5EA3FF);
  static const accentDark = Color(0xFF84B8FF);
  static const success = Color(0xFF4CAF50);
  static const warning = Color(0xFFFF9800);
  static const danger = Color(0xFFF44336);
  static const dangerSoft = Color(0xFFFF6B6B);
  static const successBg = Color(0xFFE8F5E9);
  static const warningBg = Color(0xFFFFF3E0);
  static const dangerBg = Color(0xFFFFEBEE);
  static const infoBg = Color(0xFFE3F2FD);
  static const lightBg = Color(0xFFF5F5F5);
  static const darkBg = Color(0xFF1A2438);
  static const darkSurface = Color(0xFF0E1420);
  static const divider = Color(0xFFE0E0E0);
  static const shadow = Color(0x1A000000);
  static const shadowDark = Color(0x33000000);
  static const overlay = Color(0x80000000);

  static const module = <K1Module, Color>{
    K1Module.wallet: Color(0xFF1E61C6),
    K1Module.shule: Color(0xFF2E7D32),
    K1Module.pharmacy: Color(0xFFD84343),
    K1Module.chamaa: Color(0xFFA76A1A),
    K1Module.transport: Color(0xFFF2A61A),
    K1Module.civic: Color(0xFF0E8FA4),
    K1Module.food: Color(0xFFE57E22),
    K1Module.general: Color(0xFF3B6CB6),
  };
}
