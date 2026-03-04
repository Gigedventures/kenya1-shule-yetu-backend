import 'package:flutter/material.dart';

import '../data/module_images.dart';
import '../theme/k1_colors.dart';
import 'k1_safe_image.dart';

class K1SidebarItem {
  const K1SidebarItem({
    required this.label,
    required this.icon,
    required this.previewTitle,
    required this.previewSubtitle,
    required this.assetPath,
  });

  final String label;
  final IconData icon;
  final String previewTitle;
  final String previewSubtitle;
  final String assetPath;
}

class K1NavigationSidebar extends StatefulWidget {
  const K1NavigationSidebar({super.key});

  @override
  State<K1NavigationSidebar> createState() => _K1NavigationSidebarState();
}

class _K1NavigationSidebarState extends State<K1NavigationSidebar> {
  final List<K1SidebarItem> _items = const [
    K1SidebarItem(
      label: 'Global Home',
      icon: Icons.home_outlined,
      previewTitle: 'Welcome back',
      previewSubtitle: 'Your family dashboard is ready',
      assetPath: 'assets/previews/home_preview.png',
    ),
    K1SidebarItem(
      label: 'My Children',
      icon: Icons.groups_2_outlined,
      previewTitle: 'Student snapshot',
      previewSubtitle: 'Attendance, homework and exams',
      assetPath: 'assets/previews/my_children.png',
    ),
    K1SidebarItem(
      label: 'Homework',
      icon: Icons.menu_book_outlined,
      previewTitle: 'Homework due today',
      previewSubtitle: '2 assignments pending',
      assetPath: 'assets/previews/homework_preview.png',
    ),
    K1SidebarItem(
      label: 'Transport',
      icon: Icons.directions_bus_outlined,
      previewTitle: 'Bus tracker',
      previewSubtitle: 'Route 21 arriving in 5 mins',
      assetPath: 'assets/previews/bus_preview.png',
    ),
    K1SidebarItem(
      label: 'Library',
      icon: Icons.local_library_outlined,
      previewTitle: 'Library picks',
      previewSubtitle: 'CBC revision books trending',
      assetPath: 'assets/previews/library.png',
    ),
    K1SidebarItem(
      label: 'Fees',
      icon: Icons.receipt_long_outlined,
      previewTitle: 'Fee payment',
      previewSubtitle: 'Balance and quick pay options',
      assetPath: 'assets/previews/fees.png',
    ),
  ];

  int _selected = 0;
  int? _hovered;

  @override
  Widget build(BuildContext context) {
    final active = _items[_hovered ?? _selected];
    return Container(
      padding: const EdgeInsets.all(8),
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
                    'Nairobi CBD',
                    style: TextStyle(
                        fontWeight: FontWeight.w700,
                        color: K1Colors.text,
                        fontSize: 11.5),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Navigation',
            style: TextStyle(
                fontWeight: FontWeight.w900,
                color: K1Colors.muted,
                fontSize: 11.5),
          ),
          const SizedBox(height: 6),
          SizedBox(
            height: 212,
            child: ListView.separated(
              padding: EdgeInsets.zero,
              itemCount: _items.length,
              separatorBuilder: (_, __) => const SizedBox(height: 3),
              itemBuilder: (context, i) {
                final item = _items[i];
                final activeTile = i == (_hovered ?? _selected);
                return MouseRegion(
                  onEnter: (_) => setState(() => _hovered = i),
                  onExit: (_) => setState(() => _hovered = null),
                  child: InkWell(
                    onTap: () => setState(() => _selected = i),
                    borderRadius: BorderRadius.circular(6),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 6, vertical: 5),
                      decoration: BoxDecoration(
                        color: activeTile
                            ? const Color(0xFFFCEEDD)
                            : Colors.transparent,
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(
                            color: activeTile
                                ? const Color(0xFFF6C88D)
                                : Colors.transparent),
                      ),
                      child: Row(
                        children: [
                          Icon(item.icon,
                              size: 14,
                              color: activeTile
                                  ? K1Colors.orangeDark
                                  : const Color(0xFF2B507F)),
                          const SizedBox(width: 7),
                          Expanded(
                            child: Text(
                              item.label,
                              style: const TextStyle(
                                  fontWeight: FontWeight.w700,
                                  fontSize: 11.5,
                                  color: K1Colors.text),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 8),
          const Divider(height: 1, color: Color(0xFFC8D4E5)),
          const SizedBox(height: 8),
          const Text(
            'Preview',
            style: TextStyle(
                fontWeight: FontWeight.w900,
                color: K1Colors.muted,
                fontSize: 11.5),
          ),
          const SizedBox(height: 6),
          AnimatedSwitcher(
            duration: const Duration(milliseconds: 220),
            child: _SidebarPreview(key: ValueKey(active.label), item: active),
          ),
        ],
      ),
    );
  }
}

class _SidebarPreview extends StatelessWidget {
  const _SidebarPreview({super.key, required this.item});

  final K1SidebarItem item;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 160,
      width: double.infinity,
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(8),
        gradient: const LinearGradient(
          colors: [Color(0xFFFFF4E7), Colors.white],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        border: Border.all(color: const Color(0xFFFFD4A6)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(item.previewTitle,
              style: const TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 12,
                  color: K1Colors.text)),
          const SizedBox(height: 3),
          Text(item.previewSubtitle,
              style: const TextStyle(
                  fontSize: 11,
                  color: K1Colors.muted,
                  fontWeight: FontWeight.w600)),
          const SizedBox(height: 8),
          Expanded(
            child: ClipRRect(
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
                    placeholderBackground: const Color(0xFF2C598F),
                  ),
                  DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          Colors.black.withValues(alpha: 0.05),
                          Colors.black.withValues(alpha: 0.45)
                        ],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
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
