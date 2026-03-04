import 'package:flutter/material.dart';

import '../models/k1_home_widget.dart';
import '../theme/k1_colors.dart';

class K1HomeWidgetsData {
  static final column1 = [
    const K1HomeWidget(
      id: 'identity-nav',
      title: 'K1 Navigation',
      module: K1Module.general,
      priority: 1,
      type: K1HomeWidgetType.identityNav,
      icon: Icons.menu,
    ),
    const K1HomeWidget(
      id: 'ai-quick',
      title: 'Smart Launch Pad',
      module: K1Module.general,
      priority: 2,
      type: K1HomeWidgetType.aiQuickAction,
      icon: Icons.auto_awesome,
    ),
  ];

  static final column2 = [
    const K1HomeWidget(
      id: 'wallet-summary',
      title: 'Wallet Pulse',
      module: K1Module.wallet,
      priority: 1,
      type: K1HomeWidgetType.walletSummary,
      icon: Icons.account_balance_wallet,
    ),
    const K1HomeWidget(
      id: 'quick-actions',
      title: 'Quick Actions',
      module: K1Module.wallet,
      priority: 2,
      type: K1HomeWidgetType.quickActions,
      icon: Icons.bolt,
    ),
    const K1HomeWidget(
      id: 'alerts-today',
      title: 'Today Snapshot',
      module: K1Module.shule,
      priority: 3,
      type: K1HomeWidgetType.alertsToday,
      icon: Icons.today,
    ),
  ];

  static final column3 = [
    const K1HomeWidget(
      id: 'recommendations',
      title: 'Module Marketplace',
      module: K1Module.shule,
      priority: 1,
      type: K1HomeWidgetType.moduleRecommendations,
      icon: Icons.grid_view_rounded,
    ),
    const K1HomeWidget(
      id: 'nearby-offers',
      title: 'Nearby Discovery',
      module: K1Module.food,
      priority: 2,
      type: K1HomeWidgetType.nearbyOffers,
      icon: Icons.local_offer,
    ),
    const K1HomeWidget(
      id: 'sponsored',
      title: 'Featured Partner Energy',
      module: K1Module.civic,
      priority: 3,
      type: K1HomeWidgetType.sponsored,
      icon: Icons.campaign_outlined,
    ),
    const K1HomeWidget(
      id: 'videos',
      title: 'Short Videos',
      module: K1Module.general,
      priority: 4,
      type: K1HomeWidgetType.shortVideos,
      icon: Icons.play_circle_outline,
    ),
  ];

  static final column4 = [
    const K1HomeWidget(
      id: 'notice-board',
      title: 'Notice Board',
      module: K1Module.civic,
      priority: 1,
      type: K1HomeWidgetType.noticeBoard,
      icon: Icons.notifications_active_outlined,
    ),
    const K1HomeWidget(
      id: 'smart-alerts',
      title: 'Smart Alerts',
      module: K1Module.chamaa,
      priority: 2,
      type: K1HomeWidgetType.smartAlerts,
      icon: Icons.warning_amber_rounded,
    ),
    const K1HomeWidget(
      id: 'events',
      title: 'Events Today',
      module: K1Module.food,
      priority: 3,
      type: K1HomeWidgetType.events,
      icon: Icons.event_note,
    ),
    const K1HomeWidget(
      id: 'bus-mini',
      title: 'Bus Tracker',
      module: K1Module.transport,
      priority: 4,
      type: K1HomeWidgetType.busTrackerMini,
      icon: Icons.directions_bus,
    ),
    const K1HomeWidget(
      id: 'weather',
      title: 'Weather & Currency',
      module: K1Module.general,
      priority: 5,
      type: K1HomeWidgetType.weatherCurrency,
      icon: Icons.cloud_outlined,
    ),
  ];

  static final fallback = [
    const K1HomeWidget(
      id: 'fallback-trending',
      title: 'Trending Services',
      module: K1Module.general,
      priority: 999,
      type: K1HomeWidgetType.trendingServices,
      icon: Icons.trending_up,
    ),
    const K1HomeWidget(
      id: 'fallback-tip',
      title: 'Tip of the Day',
      module: K1Module.civic,
      priority: 1000,
      type: K1HomeWidgetType.tipOfDay,
      icon: Icons.lightbulb_outline,
    ),
    const K1HomeWidget(
      id: 'fallback-nearby',
      title: 'Popular Nearby',
      module: K1Module.food,
      priority: 1001,
      type: K1HomeWidgetType.popularNearby,
      icon: Icons.place_outlined,
    ),
  ];

  static List<K1HomeWidget> fillToMinimum(
      List<K1HomeWidget> source, int minItems) {
    final sorted = [...source]
      ..sort((a, b) => a.priority.compareTo(b.priority));
    var fallbackIndex = 0;
    while (sorted.length < minItems) {
      sorted.add(fallback[fallbackIndex % fallback.length]);
      fallbackIndex++;
    }
    return sorted;
  }
}
