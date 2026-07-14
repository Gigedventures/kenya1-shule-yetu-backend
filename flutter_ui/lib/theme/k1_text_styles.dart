import 'package:flutter/material.dart';
import 'k1_colors.dart';

/// K1 Shule Yetu — Centralized text style system.
/// All screens must use these styles — no inline TextStyle() allowed.
class K1TextStyles {
  const K1TextStyles._();

  // --- Headings ---
  static const TextStyle h1 = TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: K1Colors.text);
  static const TextStyle h2 = TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: K1Colors.text);
  static const TextStyle h3 = TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: K1Colors.text);
  static const TextStyle h4 = TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: K1Colors.text);
  static const TextStyle h5 = TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: K1Colors.text);

  // --- Body ---
  static const TextStyle body = TextStyle(fontSize: 14, fontWeight: FontWeight.w400, color: K1Colors.text);
  static const TextStyle bodyBold = TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: K1Colors.text);
  static const TextStyle bodySm = TextStyle(fontSize: 13, fontWeight: FontWeight.w400, color: K1Colors.muted);
  static const TextStyle caption = TextStyle(fontSize: 12, fontWeight: FontWeight.w400, color: K1Colors.muted);

  // --- Labels ---
  static const TextStyle label = TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: K1Colors.muted);
  static const TextStyle badge = TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: K1Colors.text);

  // --- Metrics ---
  static const TextStyle metricValue = TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: K1Colors.text);
  static const TextStyle metricLabel = TextStyle(fontSize: 12, fontWeight: FontWeight.w400, color: K1Colors.muted);

  // --- Navigation ---
  static const TextStyle nav = TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: K1Colors.nav);

  // --- Buttons ---
  static const TextStyle button = TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: Colors.white);
  static const TextStyle buttonSm = TextStyle(fontSize: 12, fontWeight: FontWeight.w600);

  // --- Dark theme (inverted) ---
  static const TextStyle darkBody = TextStyle(fontSize: 14, fontWeight: FontWeight.w400, color: Colors.white);
  static const TextStyle darkMuted = TextStyle(fontSize: 13, color: Color(0xFFC4D2E6));
}