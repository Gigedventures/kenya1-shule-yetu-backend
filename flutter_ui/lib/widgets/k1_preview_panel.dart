import 'package:flutter/material.dart';

import '../data/module_images.dart';
import '../theme/k1_colors.dart';
import 'k1_safe_image.dart';

class K1SidebarPreviewItem {
  const K1SidebarPreviewItem({
    required this.label,
    required this.previewTitle,
    required this.previewSubtitle,
    required this.assetPath,
    required this.icon,
    required this.accent,
    this.busAnimation = false,
  });

  final String label;
  final String previewTitle;
  final String previewSubtitle;
  final String assetPath;
  final IconData icon;
  final Color accent;
  final bool busAnimation;
}

class K1SidebarWithPreview extends StatefulWidget {
  const K1SidebarWithPreview({super.key});

  @override
  State<K1SidebarWithPreview> createState() => _K1SidebarWithPreviewState();
}

class _K1SidebarWithPreviewState extends State<K1SidebarWithPreview> {
  static const _location = 'Nairobi CBD';

  final List<K1SidebarPreviewItem> _items = const [
    K1SidebarPreviewItem(
      label: 'Global Home',
      previewTitle: 'Today at a glance',
      previewSubtitle: 'Wallet, school and events synced',
      assetPath: 'assets/previews/home_preview.png',
      icon: Icons.home_outlined,
      accent: Color(0xFFE36F04),
    ),
    K1SidebarPreviewItem(
      label: 'My Children',
      previewTitle: 'Student dashboard',
      previewSubtitle: 'Homework, attendance and exam trend',
      assetPath: 'assets/previews/my_children.png',
      icon: Icons.groups_2_outlined,
      accent: Color(0xFF1D7D45),
    ),
    K1SidebarPreviewItem(
      label: 'Homework',
      previewTitle: 'Homework list',
      previewSubtitle: '2 pending assignments due tonight',
      assetPath: 'assets/previews/homework_preview.png',
      icon: Icons.menu_book_outlined,
      accent: Color(0xFF1F5CA8),
    ),
    K1SidebarPreviewItem(
      label: 'Transport',
      previewTitle: 'Mini bus tracker',
      previewSubtitle: 'Route #21 is 5 min away',
      assetPath: 'assets/previews/bus_preview.png',
      icon: Icons.directions_bus_outlined,
      accent: Color(0xFFBE7A00),
      busAnimation: true,
    ),
    K1SidebarPreviewItem(
      label: 'Library',
      previewTitle: 'Library shelf',
      previewSubtitle: 'New CBC revision packs added',
      assetPath: 'assets/previews/library.png',
      icon: Icons.local_library_outlined,
      accent: Color(0xFF7B4B16),
    ),
    K1SidebarPreviewItem(
      label: 'Fees',
      previewTitle: 'Payment desk',
      previewSubtitle: 'Balance: KES 61,000 for Term 2',
      assetPath: 'assets/previews/fees.png',
      icon: Icons.receipt_long_outlined,
      accent: Color(0xFFAB5A00),
    ),
    K1SidebarPreviewItem(
      label: 'Hospital',
      previewTitle: 'Doctor appointments',
      previewSubtitle: 'Next pediatric consult at 4:30 PM',
      assetPath: 'assets/previews/hospital.png',
      icon: Icons.local_hospital_outlined,
      accent: Color(0xFFD4553A),
    ),
    K1SidebarPreviewItem(
      label: 'E-Soko',
      previewTitle: 'Grocery marketplace',
      previewSubtitle: 'Fresh produce near your block',
      assetPath: 'assets/previews/e_soko.png',
      icon: Icons.shopping_basket_outlined,
      accent: Color(0xFF3F8A2D),
    ),
    K1SidebarPreviewItem(
      label: 'Twende',
      previewTitle: 'Taxi map preview',
      previewSubtitle: 'Estimated pickup: 3 mins',
      assetPath: 'assets/previews/twende.png',
      icon: Icons.local_taxi_outlined,
      accent: Color(0xFF1B64C3),
    ),
    K1SidebarPreviewItem(
      label: 'Events',
      previewTitle: 'Event posters',
      previewSubtitle: '2 concerts and 1 expo tonight',
      assetPath: 'assets/previews/events.png',
      icon: Icons.event_outlined,
      accent: Color(0xFFAF4E00),
    ),
  ];

  int _selected = 0;
  int? _hovered;

  @override
  Widget build(BuildContext context) {
    final active = _items[_hovered ?? _selected];

    return Container(
      padding: const EdgeInsets.fromLTRB(8, 8, 8, 10),
      decoration: BoxDecoration(
        color: K1Colors.sidebarRail,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFC9D5E6)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 7),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF4FA),
              borderRadius: BorderRadius.circular(7),
              border: Border.all(color: K1Colors.border),
            ),
            child: const Row(
              children: [
                Icon(Icons.location_on_outlined,
                    size: 15, color: K1Colors.muted),
                SizedBox(width: 5),
                Expanded(
                  child: Text(
                    _location,
                    style: TextStyle(
                        fontWeight: FontWeight.w700,
                        color: K1Colors.text,
                        fontSize: 11.5),
                  ),
                ),
                Icon(Icons.keyboard_arrow_down,
                    color: K1Colors.muted, size: 17),
              ],
            ),
          ),
          const SizedBox(height: 8),
          const Padding(
            padding: EdgeInsets.only(left: 2),
            child: Text(
              'Navigation',
              style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 11.5,
                  color: K1Colors.muted),
            ),
          ),
          const SizedBox(height: 5),
          Expanded(
            child: SingleChildScrollView(
              child: Column(
                children: [
                  for (var i = 0; i < _items.length; i++) ...[
                    _NavEntryTile(
                      item: _items[i],
                      active: i == (_hovered ?? _selected),
                      onTap: () => setState(() => _selected = i),
                      onHover: (hovering) {
                        setState(() => _hovered = hovering ? i : null);
                      },
                    ),
                    if (i < _items.length - 1) const SizedBox(height: 3),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(height: 8),
          const Divider(height: 1, color: Color(0xFFC8D4E5)),
          const SizedBox(height: 8),
          const Text(
            'Live Preview',
            style: TextStyle(
                fontWeight: FontWeight.w900,
                fontSize: 11.5,
                color: K1Colors.muted),
          ),
          const SizedBox(height: 6),
          SizedBox(
            height: 165,
            child: AnimatedSwitcher(
              duration: const Duration(milliseconds: 220),
              switchInCurve: Curves.easeOut,
              switchOutCurve: Curves.easeIn,
              child: SingleChildScrollView(
                child: _PreviewPanel(item: active, key: ValueKey(active.label)),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _NavEntryTile extends StatelessWidget {
  const _NavEntryTile({
    required this.item,
    required this.active,
    required this.onTap,
    required this.onHover,
  });

  final K1SidebarPreviewItem item;
  final bool active;
  final VoidCallback onTap;
  final ValueChanged<bool> onHover;

  @override
  Widget build(BuildContext context) {
    return MouseRegion(
      onEnter: (_) => onHover(true),
      onExit: (_) => onHover(false),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(6),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 140),
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 5),
          decoration: BoxDecoration(
            color: active ? const Color(0xFFFCEEDD) : Colors.transparent,
            borderRadius: BorderRadius.circular(6),
            border: Border.all(
              color: active ? const Color(0xFFF6C88D) : Colors.transparent,
            ),
          ),
          child: Row(
            children: [
              Container(
                width: 20,
                height: 20,
                decoration: BoxDecoration(
                  color: active
                      ? const Color(0xFFFFE4C6)
                      : const Color(0xFFEAF0F8),
                  borderRadius: BorderRadius.circular(5),
                ),
                child: Icon(
                  item.icon,
                  size: 13,
                  color: active ? K1Colors.orangeDark : const Color(0xFF274F84),
                ),
              ),
              const SizedBox(width: 7),
              Expanded(
                child: Text(
                  item.label,
                  style: const TextStyle(
                      fontWeight: FontWeight.w700,
                      color: K1Colors.text,
                      fontSize: 11.5),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PreviewPanel extends StatelessWidget {
  const _PreviewPanel({super.key, required this.item});

  final K1SidebarPreviewItem item;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            item.accent.withValues(alpha: 0.18),
            Colors.white,
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: item.accent.withValues(alpha: 0.35)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            item.previewTitle,
            style: const TextStyle(
              fontWeight: FontWeight.w900,
              color: K1Colors.text,
              fontSize: 12,
            ),
          ),
          const SizedBox(height: 3),
          Text(
            item.previewSubtitle,
            style: const TextStyle(
              fontWeight: FontWeight.w600,
              color: K1Colors.muted,
              fontSize: 11,
            ),
          ),
          const SizedBox(height: 8),
          SizedBox(
            height: 102,
            child: item.busAnimation
                ? const _MiniBusAnimationPreview()
                : ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        K1SafeImage(
                          assetPath: item.assetPath,
                          fallbackUrl: item.label == 'Global Home'
                              ? moduleImages['home_preview']!
                              : item.label == 'Homework'
                                  ? moduleImages['homework_preview']!
                                  : item.label == 'Transport'
                                      ? moduleImages['bus_preview']!
                                      : moduleImages['library_preview']!,
                          fit: BoxFit.cover,
                          placeholderIcon: item.icon,
                          placeholderBackground: item.accent,
                        ),
                        DecoratedBox(
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              colors: [
                                Colors.black.withValues(alpha: 0.00),
                                Colors.black.withValues(alpha: 0.45)
                              ],
                              begin: Alignment.topCenter,
                              end: Alignment.bottomCenter,
                            ),
                          ),
                        ),
                        Positioned(
                          left: 6,
                          bottom: 6,
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 7, vertical: 3),
                            decoration: BoxDecoration(
                              color: Colors.black.withValues(alpha: 0.45),
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: const Text(
                              'Preview',
                              style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w700,
                                  fontSize: 10),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
          ),
        ],
      ),
    );
  }
}

class _MiniBusAnimationPreview extends StatelessWidget {
  const _MiniBusAnimationPreview();

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(8),
        gradient: const LinearGradient(
          colors: [Color(0xFF1B3558), Color(0xFF2F609D)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(8),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Route 21: Umoja to School Gate',
              style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                  fontSize: 11),
            ),
            const SizedBox(height: 8),
            Expanded(
              child: LayoutBuilder(
                builder: (context, constraints) {
                  return TweenAnimationBuilder<double>(
                    tween: Tween(begin: 0.12, end: 0.82),
                    duration: const Duration(milliseconds: 2400),
                    curve: Curves.easeInOut,
                    builder: (context, value, _) {
                      return Stack(
                        children: [
                          Positioned(
                            left: 0,
                            right: 0,
                            top: constraints.maxHeight * 0.52,
                            child: Container(
                              height: 6,
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.35),
                                borderRadius: BorderRadius.circular(99),
                              ),
                            ),
                          ),
                          Positioned(
                            left: (constraints.maxWidth - 22) * value,
                            top: constraints.maxHeight * 0.45,
                            child: const Icon(Icons.directions_bus,
                                color: Color(0xFFFFCF6B), size: 22),
                          ),
                        ],
                      );
                    },
                  );
                },
              ),
            ),
            const Text(
              'ETA: 5 mins',
              style: TextStyle(
                  color: Color(0xFFFFDD99),
                  fontWeight: FontWeight.w800,
                  fontSize: 11),
            ),
          ],
        ),
      ),
    );
  }
}
