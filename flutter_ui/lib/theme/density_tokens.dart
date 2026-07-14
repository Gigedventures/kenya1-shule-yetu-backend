/// Density token system for Shule Yetu SaaS UI.
/// Replaces large card-based UI with compact, data-dense components.
/// Inspired by Stripe, Notion, Linear design density.
library density_tokens;

import 'package:flutter/material.dart';

/// Spacing scale - reduced from typical 8px base to 4px base for density.
class DensitySpacing {
  static const double xs = 4.0;   // 4px - micro gaps
  static const double sm = 8.0;   // 8px - small gaps
  static const double md = 12.0;  // 12px - medium gaps
  static const double lg = 16.0;  // 16px - large gaps
  static const double xl = 24.0;  // 24px - section gaps
  static const double xxl = 32.0; // 32px - major section gaps

  // Semantic spacing
  static const double cardPadding = 12.0;
  static const double cardGap = 8.0;
  static const double sectionGap = 16.0;
  static const double inlineGap = 8.0;
  static const double denseGap = 4.0;
}

/// Border radius scale - slightly reduced for denser feel.
class DensityRadius {
  static const double xs = 4.0;
  static const double sm = 8.0;
  static const double md = 12.0;
  static const double lg = 16.0;
  static const double xl = 20.0;
  static const double pill = 999.0;

  static const double card = 12.0;
  static const double button = 8.0;
  static const double input = 8.0;
  static const tableCell = 0.0;
}

/// Typography scale - hierarchical, compact.
class DensityTypography {
  // Title: Small, semi-bold - for card headers, section titles
  static const TextStyle title = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w600,
    height: 1.33,
    letterSpacing: 0.0,
  );

  // Value: Bold, slightly larger - for metrics, counts, key data
  static const TextStyle value = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w700,
    height: 1.28,
    letterSpacing: -0.01,
  );

  // Body: Regular - for descriptions, secondary text
  static const TextStyle body = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w400,
    height: 1.5,
    letterSpacing: 0.0,
  );

  // Metadata: Muted, smaller - for timestamps, labels, hints
  static const TextStyle metadata = TextStyle(
    fontSize: 11,
    fontWeight: FontWeight.w500,
    height: 1.45,
    letterSpacing: 0.0,
  );

  // Caption: Smallest - for footnotes, status badges
  static const TextStyle caption = TextStyle(
    fontSize: 10,
    fontWeight: FontWeight.w500,
    height: 1.4,
    letterSpacing: 0.2,
  );

  // Table header: Uppercase, tracked
  static const TextStyle tableHeader = TextStyle(
    fontSize: 10,
    fontWeight: FontWeight.w600,
    height: 1.4,
    letterSpacing: 0.5,
  );

  // Table cell: Readable at density
  static const TextStyle tableCell = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w400,
    height: 1.5,
    letterSpacing: 0.0,
  );

  // Interactive: Buttons, links
  static const TextStyle interactive = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w600,
    height: 1.33,
    letterSpacing: 0.0,
  );
}

/// Color tokens - semantic, supports light/dark.
class DensityColors {
  // Background layers
  static const Color bgBase = Color(0xFFF4F7FB);        // Page background
  static const Color bgSurface = Color(0xFFFFFFFF);     // Card/surface background
  static const Color bgElevated = Color(0xFFFFFFFF);    // Elevated surfaces
  static const Color bgHover = Color(0xFFF0F4FA);       // Hover states
  static const Color bgPressed = Color(0xFFE5ECF6);     // Pressed states
  static const Color bgInput = Color(0xFFFFFFFF);       // Input backgrounds

  // Borders
  static const Color borderLight = Color(0xFFD9E3F2);   // Default borders
  static const Color borderMedium = Color(0xFFC2CFE3);  // Emphasized borders
  static const Color borderFocus = Color(0xFF2563EB);   // Focus rings

  // Text
  static const Color textPrimary = Color(0xFF0E1A2A);   // Primary text
  static const Color textSecondary = Color(0xFF4A5A7A); // Secondary text
  static const Color textMuted = Color(0xFF8CA3C7);     // Muted/disabled text
  static const Color textInverse = Color(0xFFFFFFFF);   // On dark backgrounds

  // Status colors
  static const Color success = Color(0xFF10B981);
  static const Color warning = Color(0xFFF59E0B);
  static const Color error = Color(0xFFEF4444);
  static const Color info = Color(0xFF3B82F6);

  // Status backgrounds (10% opacity)
  static const Color successBg = Color(0x1A10B981);
  static const Color warningBg = Color(0x1AF59E0B);
  static const Color errorBg = Color(0x1AEF4444);
  static const Color infoBg = Color(0x1A3B82F6);

  // Accent
  static const Color primary = Color(0xFF2563EB);
  static const Color primaryHover = Color(0xFF1D4ED8);
  static const Color primaryBg = Color(0x1A2563EB);
}

/// Shadow tokens - subtle, layered.
class DensityShadows {
  static const List<BoxShadow> card = [
    BoxShadow(
      color: Color(0x100E1A2A),
      blurRadius: 14,
      offset: Offset(0, 5),
    ),
  ];

  static const List<BoxShadow> cardHover = [
    BoxShadow(
      color: Color(0x140E1A2A),
      blurRadius: 20,
      offset: Offset(0, 8),
    ),
  ];

  static const List<BoxShadow> dropdown = [
    BoxShadow(
      color: Color(0x1A0E1A2A),
      blurRadius: 24,
      offset: Offset(0, 8),
    ),
  ];

  static const List<BoxShadow> modal = [
    BoxShadow(
      color: Color(0x1F0E1A2A),
      blurRadius: 40,
      offset: Offset(0, 16),
    ),
  ];

  static const List<BoxShadow> none = [];
}

/// Component sizing - compact dimensions.
class DensitySizing {
  // Card heights (reduced from typical 120-160px to 60-90px)
  static const double statCardHeight = 72.0;
  static const double compactCardHeight = 84.0;
  static const double mediumCardHeight = 100.0;

  // Table row heights
  static const double tableRowHeight = 44.0;
  static const double tableRowHeightCompact = 36.0;
  static const double tableHeaderHeight = 40.0;

  // Input heights
  static const double inputHeight = 36.0;
  static const double inputHeightSm = 32.0;

  // Button heights
  static const double buttonHeight = 36.0;
  static const double buttonHeightSm = 30.0;
  static const double buttonHeightLg = 44.0;

  // Icon sizes
  static const double iconXs = 14.0;
  static const double iconSm = 16.0;
  static const double iconMd = 20.0;
  static const double iconLg = 24.0;

  // Avatar sizes
  static const double avatarXs = 24.0;
  static const double avatarSm = 32.0;
  static const double avatarMd = 40.0;
  static const double avatarLg = 48.0;

  // Badge
  static const double badgeHeight = 20.0;
  static const double badgeRadius = 10.0;
}

/// Animation durations - snappy.
class DensityMotion {
  static const Duration fast = Duration(milliseconds: 120);
  static const Duration normal = Duration(milliseconds: 200);
  static const Duration slow = Duration(milliseconds: 300);

  static const Curve standard = Curves.easeOutCubic;
  static const Curve emphasized = Curves.easeInOutCubic;
}

/// Breakpoints for responsive density.
class DensityBreakpoints {
  static const double mobile = 600;
  static const double tablet = 900;
  static const double desktop = 1200;
  static const double wide = 1600;

  static bool isMobile(double width) => width < tablet;
  static bool isTablet(double width) => width >= tablet && width < desktop;
  static bool isDesktop(double width) => width >= desktop;
  static bool isWide(double width) => width >= wide;

  /// Number of stat card columns for given width.
  static int statCardColumns(double width) {
    if (width < 400) return 1;
    if (width < 700) return 2;
    if (width < 1100) return 3;
    return 4;
  }

  /// Table column visibility threshold.
  static bool showColumn(double width, int columnIndex, {int mobileCols = 3, int tabletCols = 5}) {
    if (isMobile(width)) return columnIndex < mobileCols;
    if (isTablet(width)) return columnIndex < tabletCols;
    return true;
  }
}

/// Unified density configuration.
class DensityConfig {
  const DensityConfig();

  // Spacing
  double get xs => DensitySpacing.xs;
  double get sm => DensitySpacing.sm;
  double get md => DensitySpacing.md;
  double get lg => DensitySpacing.lg;
  double get xl => DensitySpacing.xl;
  double get xxl => DensitySpacing.xxl;
  double get cardGap => DensitySpacing.cardGap;
  double get denseGap => DensitySpacing.denseGap;

  // Radius
  double get radiusXs => DensityRadius.xs;
  double get radiusSm => DensityRadius.sm;
  double get radiusMd => DensityRadius.md;
  double get radiusLg => DensityRadius.lg;
  double get radiusXl => DensityRadius.xl;
  double get radiusPill => DensityRadius.pill;

  // Typography
  TextStyle get title => DensityTypography.title;
  TextStyle get value => DensityTypography.value;
  TextStyle get body => DensityTypography.body;
  TextStyle get metadata => DensityTypography.metadata;
  TextStyle get caption => DensityTypography.caption;
  TextStyle get tableHeader => DensityTypography.tableHeader;
  TextStyle get tableCell => DensityTypography.tableCell;
  TextStyle get interactive => DensityTypography.interactive;

  // Colors
  Color get bgBase => DensityColors.bgBase;
  Color get bgSurface => DensityColors.bgSurface;
  Color get bgElevated => DensityColors.bgElevated;
  Color get bgHover => DensityColors.bgHover;
  Color get bgPressed => DensityColors.bgPressed;
  Color get borderLight => DensityColors.borderLight;
  Color get borderMedium => DensityColors.borderMedium;
  Color get borderFocus => DensityColors.borderFocus;
  Color get textPrimary => DensityColors.textPrimary;
  Color get textSecondary => DensityColors.textSecondary;
  Color get textMuted => DensityColors.textMuted;
  Color get textInverse => DensityColors.textInverse;
  Color get success => DensityColors.success;
  Color get warning => DensityColors.warning;
  Color get error => DensityColors.error;
  Color get info => DensityColors.info;
  Color get successBg => DensityColors.successBg;
  Color get warningBg => DensityColors.warningBg;
  Color get errorBg => DensityColors.errorBg;
  Color get infoBg => DensityColors.infoBg;
  Color get primary => DensityColors.primary;
  Color get primaryHover => DensityColors.primaryHover;
  Color get primaryBg => DensityColors.primaryBg;

  // Shadows
  List<BoxShadow> get shadowCard => DensityShadows.card;
  List<BoxShadow> get shadowCardHover => DensityShadows.cardHover;
  List<BoxShadow> get shadowDropdown => DensityShadows.dropdown;
  List<BoxShadow> get shadowModal => DensityShadows.modal;
  List<BoxShadow> get shadowNone => DensityShadows.none;

  // Sizing
  double get statCardHeight => DensitySizing.statCardHeight;
  double get compactCardHeight => DensitySizing.compactCardHeight;
  double get mediumCardHeight => DensitySizing.mediumCardHeight;
  double get tableRowHeight => DensitySizing.tableRowHeight;
  double get tableRowHeightCompact => DensitySizing.tableRowHeightCompact;
  double get tableHeaderHeight => DensitySizing.tableHeaderHeight;
  double get inputHeight => DensitySizing.inputHeight;
  double get buttonHeight => DensitySizing.buttonHeight;
  double get iconXs => DensitySizing.iconXs;
  double get iconSm => DensitySizing.iconSm;
  double get iconMd => DensitySizing.iconMd;
  double get avatarSm => DensitySizing.avatarSm;
  double get avatarMd => DensitySizing.avatarMd;
  double get badgeHeight => DensitySizing.badgeHeight;
  double get badgeRadius => DensitySizing.badgeRadius;

  // Motion
  Duration get motionFast => DensityMotion.fast;
  Duration get motionNormal => DensityMotion.normal;
  Duration get motionSlow => DensityMotion.slow;
  Curve get motionStandard => DensityMotion.standard;

  // Breakpoints
  int statCardColumns(double width) => DensityBreakpoints.statCardColumns(width);
  bool showColumn(double width, int index, {int mobileCols = 3, int tabletCols = 5}) =>
      DensityBreakpoints.showColumn(width, index, mobileCols: mobileCols, tabletCols: tabletCols);
}

/// Global density instance.
final density = DensityConfig();

/// Extension for easy access in widgets.
extension DensityContext on BuildContext {
  DensityConfig get density => density;
  ThemeData get theme => Theme.of(this);
}