import 'package:flutter/material.dart';

import '../theme/k1_colors.dart';

enum K1HomeWidgetType {
  identityNav,
  aiQuickAction,
  walletSummary,
  quickActions,
  alertsToday,
  moduleRecommendations,
  nearbyOffers,
  sponsored,
  shortVideos,
  noticeBoard,
  smartAlerts,
  events,
  busTrackerMini,
  weatherCurrency,
  trendingServices,
  tipOfDay,
  popularNearby,
}

class K1HomeWidget {
  const K1HomeWidget({
    required this.id,
    required this.title,
    required this.module,
    required this.priority,
    required this.type,
    this.icon,
  });

  final String id;
  final String title;
  final K1Module module;
  final int priority;
  final K1HomeWidgetType type;
  final IconData? icon;
}
