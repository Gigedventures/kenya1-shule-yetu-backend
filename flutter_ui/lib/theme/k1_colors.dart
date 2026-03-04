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
