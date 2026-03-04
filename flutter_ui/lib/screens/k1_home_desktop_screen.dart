import 'package:flutter/material.dart';

import '../app_router.dart';
import '../data/k1_home_widgets.dart';
import '../data/mock_users.dart';
import '../models/k1_home_widget.dart';
import '../theme/app_icons.dart';
import '../theme/k1_colors.dart';
import '../widgets/k1_column.dart';
import '../widgets/k1_image_module_cards.dart';
import '../widgets/k1_navigation_sidebar.dart';
import '../widgets/k1_smart_offers_widget.dart';
import '../widgets/k1_video_reel_widget.dart';
import '../widgets/k1_widget_card.dart';
import '../widgets/wallet_pulse_widget.dart';

class K1HomeDesktopScreen extends StatelessWidget {
  const K1HomeDesktopScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: K1Colors.page,
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            final columns = _columnCount(constraints.maxWidth);
            final columnData = _buildColumns(columns);
            return Column(
              children: [
                _GlobalTopBar(width: constraints.maxWidth),
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(10, 8, 10, 10),
                    child: _buildResponsiveGrid(context, columns, columnData),
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }

  int _columnCount(double width) {
    if (width >= 1400) return 4;
    if (width >= 1100) return 3;
    if (width >= 800) return 2;
    return 1;
  }

  List<List<K1HomeWidget>> _buildColumns(int count) {
    final col1 = K1HomeWidgetsData.fillToMinimum(K1HomeWidgetsData.column1, 4);
    final col2 = K1HomeWidgetsData.fillToMinimum(K1HomeWidgetsData.column2, 4);
    final col3 = K1HomeWidgetsData.fillToMinimum(K1HomeWidgetsData.column3, 5);
    final col4 = K1HomeWidgetsData.fillToMinimum(K1HomeWidgetsData.column4, 5);
    if (count == 4) return [col1, col2, col3, col4];
    if (count == 3)
      return [
        col1,
        col2,
        [...col3, ...col4]
      ];
    if (count == 2)
      return [
        [...col1, ...col2],
        [...col3, ...col4]
      ];
    return [
      [...col1, ...col2, ...col3, ...col4]
    ];
  }

  Widget _buildResponsiveGrid(
      BuildContext context, int columns, List<List<K1HomeWidget>> columnData) {
    if (columns == 1) return _scrollableColumn(context, columnData.first);

    if (columns == 4) {
      return Row(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SizedBox(
              width: 190,
              child: _scrollableColumn(context, columnData[0], leftRail: true)),
          const SizedBox(width: 8),
          Expanded(child: _scrollableColumn(context, columnData[1])),
          const SizedBox(width: 8),
          Expanded(child: _scrollableColumn(context, columnData[2])),
          const SizedBox(width: 8),
          Expanded(child: _scrollableColumn(context, columnData[3])),
        ],
      );
    }

    final flexes = columns == 3 ? [12, 16, 24] : [1, 1];
    return Row(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var i = 0; i < columnData.length; i++) ...[
          i == 0 && columns == 3
              ? SizedBox(
                  width: 190,
                  child:
                      _scrollableColumn(context, columnData[i], leftRail: true))
              : Expanded(
                  flex: flexes[i],
                  child: _scrollableColumn(context, columnData[i])),
          if (i < columnData.length - 1) const SizedBox(width: 8),
        ],
      ],
    );
  }

  Widget _scrollableColumn(BuildContext context, List<K1HomeWidget> items,
      {bool leftRail = false}) {
    final cards = [
      for (final item in items) _buildWidgetCard(context, item),
      if (leftRail) ...[
        _simpleCard(
          title: 'Recent Modules',
          icon: Icons.history,
          content: _feedRows([
            'Shule Yetu opened 8m ago',
            'E-Soko opened 23m ago',
          ]),
        ),
        _simpleCard(
          title: 'Quick Shortcuts',
          icon: Icons.flash_on_outlined,
          content: const Wrap(
            spacing: 6,
            runSpacing: 6,
            children: [
              _Chip(label: 'Pay Fees'),
              _Chip(label: 'Track Bus'),
              _Chip(label: 'Order Lunch'),
            ],
          ),
        ),
        _simpleCard(
          title: 'AI Suggestions',
          icon: Icons.auto_awesome,
          content: _feedRows([
            'Set reminder for fees due Friday',
            'Bundle bus + lunch payment',
          ]),
        ),
      ]
    ];

    return SingleChildScrollView(
      child: K1Column(children: cards),
    );
  }

  Widget _simpleCard({
    required String title,
    required IconData icon,
    required Widget content,
  }) {
    return K1WidgetCard(
      title: title,
      accent: K1Colors.module[K1Module.general]!,
      icon: icon,
      minHeight: 100,
      child: content,
    );
  }

  Widget _buildWidgetCard(BuildContext context, K1HomeWidget item) {
    if (item.type == K1HomeWidgetType.identityNav) {
      return const K1NavigationSidebar();
    }
    return K1WidgetCard(
      title: item.title,
      accent:
          K1Colors.module[item.module] ?? K1Colors.module[K1Module.general]!,
      icon: item.icon ?? Icons.widgets_outlined,
      style: _styleFor(item.type),
      minHeight: _minHeightFor(item.type),
      child: _contentFor(context, item),
    );
  }

  K1WidgetCardStyle _styleFor(K1HomeWidgetType type) {
    if (type == K1HomeWidgetType.sponsored) return K1WidgetCardStyle.hero;
    if (type == K1HomeWidgetType.quickActions ||
        type == K1HomeWidgetType.smartAlerts) return K1WidgetCardStyle.pill;
    if (type == K1HomeWidgetType.events || type == K1HomeWidgetType.tipOfDay)
      return K1WidgetCardStyle.strip;
    if (type == K1HomeWidgetType.weatherCurrency)
      return K1WidgetCardStyle.circular;
    return K1WidgetCardStyle.standard;
  }

  double _minHeightFor(K1HomeWidgetType type) {
    switch (type) {
      case K1HomeWidgetType.moduleRecommendations:
        return 280;
      case K1HomeWidgetType.aiQuickAction:
      case K1HomeWidgetType.walletSummary:
      case K1HomeWidgetType.nearbyOffers:
      case K1HomeWidgetType.sponsored:
        return 220;
      case K1HomeWidgetType.shortVideos:
      case K1HomeWidgetType.noticeBoard:
      case K1HomeWidgetType.smartAlerts:
      case K1HomeWidgetType.alertsToday:
      case K1HomeWidgetType.popularNearby:
        return 160;
      default:
        return 100;
    }
  }

  Widget _contentFor(BuildContext context, K1HomeWidget item) {
    switch (item.type) {
      case K1HomeWidgetType.aiQuickAction:
        final primaryGuardian = MockUsersData.maryOtieno();
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Active profile: ${primaryGuardian.name}',
                style: const TextStyle(
                    fontWeight: FontWeight.w800, color: K1Colors.text)),
            const SizedBox(height: 4),
            Text('Modules: ${primaryGuardian.activeModules.join(', ')}',
                style: const TextStyle(fontSize: 11, color: K1Colors.muted)),
            const SizedBox(height: 10),
            FilledButton.icon(
              style: FilledButton.styleFrom(
                  backgroundColor: K1Colors.orange,
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 38)),
              onPressed: () =>
                  Navigator.of(context).pushNamed(AppRouter.shuleYetuSelector),
              icon: const Icon(Icons.auto_awesome, size: 17),
              label: const Text('Open Shule Yetu'),
            ),
          ],
        );
      case K1HomeWidgetType.walletSummary:
        return const WalletPulseWidget();
      case K1HomeWidgetType.quickActions:
        return Wrap(
          spacing: 6,
          runSpacing: 6,
          children: [
            _action('Send', Icons.send),
            _action('Scan', Icons.qr_code_scanner),
            _action('Request', Icons.request_quote_outlined),
            InkWell(
              onTap: () =>
                  Navigator.of(context).pushNamed(AppRouter.shuleYetuSelector),
              child: _action('Shule', Icons.school_outlined),
            ),
          ],
        );
      case K1HomeWidgetType.alertsToday:
        return _feedRows([
          'Homework: 2 due today',
          'Bus route 21 is 5 mins away',
          'Wallet: 1 bill due tomorrow'
        ]);
      case K1HomeWidgetType.moduleRecommendations:
        return K1ImageModuleCards(modules: _moduleCards);
      case K1HomeWidgetType.nearbyOffers:
        return const K1SmartOffersWidget(
          title: 'Nearby Offers',
          location: 'Nairobi CBD',
          items: [
            K1SmartOfferItem(
                title: 'E-Soko vegetables discount',
                subtitle: 'Fresh produce combo',
                distance: '0.9 km',
                cta: 'Open',
                assetPath: 'assets/images/modules/esoko.jpg',
                icon: Icons.shopping_basket_outlined),
            K1SmartOfferItem(
                title: 'Just Eat lunch promo',
                subtitle: '20% off before 2 PM',
                distance: '1.1 km',
                cta: 'Open',
                assetPath: 'assets/images/modules/restaurant.jpg',
                icon: Icons.restaurant_outlined),
          ],
        );
      case K1HomeWidgetType.sponsored:
        return const Text(
            'Featured partner bundles: Hospital cover + school transport pass.',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700));
      case K1HomeWidgetType.shortVideos:
        return const K1VideoReelWidget(items: [
          K1VideoReelItem(
              title: 'How to pay fees',
              thumbnailAsset: 'assets/previews/video_fees.png',
              duration: '0:43'),
          K1VideoReelItem(
              title: 'Bus tracker tips',
              thumbnailAsset: 'assets/previews/video_bus.png',
              duration: '0:31'),
          K1VideoReelItem(
              title: 'Savings guide',
              thumbnailAsset: 'assets/previews/video_savings.png',
              duration: '0:58'),
        ]);
      case K1HomeWidgetType.noticeBoard:
        return _feedRows([
          'Announcement: PTM meeting Friday',
          'Announcement: Sports day next week'
        ]);
      case K1HomeWidgetType.smartAlerts:
        return _feedRows([
          'Guardian ${MockUsersData.guardians[0].name} has 1 pending invoice',
          'Homework reminder sent to 14 students'
        ]);
      case K1HomeWidgetType.events:
        return _feedRows(['Music Fest', 'County Expo', 'School Sports Day']);
      case K1HomeWidgetType.busTrackerMini:
        return _feedRows(['Bus #21 from Umoja - ETA 5 mins']);
      case K1HomeWidgetType.weatherCurrency:
        return const Row(children: [
          Expanded(child: _Chip(label: 'Nairobi 24 C')),
          SizedBox(width: 6),
          Expanded(child: _Chip(label: 'USD/KES 129.7'))
        ]);
      case K1HomeWidgetType.trendingServices:
        return const Wrap(spacing: 6, runSpacing: 6, children: [
          _Chip(label: 'School Fees'),
          _Chip(label: 'Transport'),
          _Chip(label: 'E-Soko'),
          _Chip(label: 'Events')
        ]);
      case K1HomeWidgetType.tipOfDay:
        return const Text(
            'Tip: link one guardian profile across modules to test cross-app journeys.',
            style: TextStyle(
                color: K1Colors.text,
                fontWeight: FontWeight.w600,
                fontSize: 12));
      case K1HomeWidgetType.popularNearby:
        return _feedRows(['Green Basket 0.8 km', 'City Clinic 1.2 km']);
      case K1HomeWidgetType.identityNav:
        return const SizedBox.shrink();
    }
  }

  Widget _feedRows(List<String> lines) => Column(
        children: [
          for (var i = 0; i < lines.length; i++) ...[
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFFFFF8EE),
                borderRadius: BorderRadius.circular(6),
                border: Border.all(color: const Color(0xFFFFE1B8)),
              ),
              child: Text(lines[i],
                  style: const TextStyle(
                      fontWeight: FontWeight.w700,
                      color: K1Colors.text,
                      fontSize: 11.5)),
            ),
            if (i < lines.length - 1) const SizedBox(height: 6),
          ]
        ],
      );

  Widget _action(String label, IconData icon) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(999),
          color: const Color(0xFFFFF1DF),
          border: Border.all(color: const Color(0xFFFFD2A0)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 16, color: K1Colors.orangeDark),
            const SizedBox(width: 5),
            Text(label,
                style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    color: K1Colors.orangeDark,
                    fontSize: 11.5)),
          ],
        ),
      );

  List<K1ImageModule> get _moduleCards => const [
        K1ImageModule(
            name: 'E-Soko',
            description: 'Fresh produce near you',
            moduleKey: 'esoko',
            assetPath: 'assets/images/modules/esoko.jpg',
            icon: Icons.shopping_basket_outlined,
            ctaLabel: 'Open',
            height: 160),
        K1ImageModule(
            name: 'Hospital',
            description: 'Book a doctor quickly',
            moduleKey: 'hospital',
            assetPath: 'assets/images/modules/hospital.jpg',
            icon: Icons.local_hospital_outlined,
            ctaLabel: 'Open',
            height: 100),
        K1ImageModule(
            name: 'Twende',
            description: 'Taxi rides near you',
            moduleKey: 'twende',
            assetPath: 'assets/images/modules/twende.jpg',
            icon: Icons.local_taxi_outlined,
            ctaLabel: 'Open',
            height: 220),
        K1ImageModule(
            name: 'Kenya Cademy',
            description: 'Learn online, anytime',
            moduleKey: 'kenya_cademy',
            assetPath: 'assets/images/modules/kenya_cademy.jpg',
            icon: Icons.school_outlined,
            ctaLabel: 'Open',
            height: 160),
        K1ImageModule(
            name: 'Gas Monitor',
            description: 'Smart home energy safety',
            moduleKey: 'gas_monitor',
            assetPath: 'assets/images/modules/gas_monitor.jpg',
            icon: Icons.gas_meter_outlined,
            ctaLabel: 'Open',
            height: 100),
        K1ImageModule(
            name: 'Restaurant',
            description: 'Food deals and ordering',
            moduleKey: 'restaurant',
            assetPath: 'assets/images/modules/restaurant.jpg',
            icon: Icons.restaurant_outlined,
            ctaLabel: 'Open',
            height: 160),
        K1ImageModule(
            name: 'Property & Rent',
            description: 'Discover homes nearby',
            moduleKey: 'property',
            assetPath: 'assets/images/modules/property.jpg',
            icon: Icons.apartment_outlined,
            ctaLabel: 'Open',
            height: 100),
        K1ImageModule(
            name: 'Parcel Delivery',
            description: 'Track every package',
            moduleKey: 'parcel',
            assetPath: 'assets/images/modules/parcel.jpg',
            icon: Icons.local_shipping_outlined,
            ctaLabel: 'Open',
            height: 160),
      ];
}

class _GlobalTopBar extends StatelessWidget {
  const _GlobalTopBar({required this.width});

  final double width;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 56,
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: const BoxDecoration(
        gradient:
            LinearGradient(colors: [Color(0xFFFFFFFF), Color(0xFFF4F7FC)]),
        border: Border(bottom: BorderSide(color: K1Colors.border)),
      ),
      child: Row(
        children: [
          const Icon(Icons.menu, color: K1Colors.text),
          const SizedBox(width: 10),
          AppIcons.svg(AppIcons.kenyaFlag, width: 26, height: 18),
          const SizedBox(width: 8),
          const Text('KENYA 1',
              style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 16,
                  color: K1Colors.text)),
          const SizedBox(width: 18),
          if (width >= 900)
            Expanded(
              child: Container(
                height: 36,
                decoration: BoxDecoration(
                  color: const Color(0xFFF7FAFF),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: K1Colors.border),
                ),
                padding: const EdgeInsets.symmetric(horizontal: 10),
                child: const Row(
                  children: [
                    Icon(Icons.search, color: K1Colors.muted, size: 18),
                    SizedBox(width: 8),
                    Expanded(
                        child: Text('Search modules, services, payments...',
                            style: TextStyle(
                                color: K1Colors.muted, fontSize: 13))),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _Chip extends StatelessWidget {
  const _Chip({required this.label});
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 7),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF5E8),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: const Color(0xFFFFD8AB)),
      ),
      child: Text(label,
          style: const TextStyle(
              color: K1Colors.text,
              fontWeight: FontWeight.w700,
              fontSize: 11.5)),
    );
  }
}
