import 'package:flutter/material.dart';

import '../theme/k1_colors.dart';
import 'k1_safe_image.dart';

class K1SmartOfferItem {
  const K1SmartOfferItem({
    required this.title,
    required this.subtitle,
    required this.distance,
    required this.cta,
    required this.assetPath,
    required this.icon,
  });

  final String title;
  final String subtitle;
  final String distance;
  final String cta;
  final String assetPath;
  final IconData icon;
}

class K1SmartOffersWidget extends StatelessWidget {
  const K1SmartOffersWidget({
    super.key,
    required this.title,
    required this.location,
    required this.items,
  });

  final String title;
  final String location;
  final List<K1SmartOfferItem> items;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text(
              title,
              style: const TextStyle(
                  fontWeight: FontWeight.w900,
                  color: K1Colors.text,
                  fontSize: 12.5),
            ),
            const Spacer(),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: const Color(0xFFFFE7CA),
                borderRadius: BorderRadius.circular(999),
              ),
              child: Text(
                location,
                style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    color: K1Colors.orangeDark,
                    fontSize: 10.5),
              ),
            ),
          ],
        ),
        const SizedBox(height: 7),
        Column(
          children: [
            for (var i = 0; i < items.length; i++) ...[
              _OfferCard(item: items[i]),
              if (i < items.length - 1) const SizedBox(height: 6),
            ],
          ],
        ),
      ],
    );
  }
}

class _OfferCard extends StatefulWidget {
  const _OfferCard({required this.item});

  final K1SmartOfferItem item;

  @override
  State<_OfferCard> createState() => _OfferCardState();
}

class _OfferCardState extends State<_OfferCard> {
  bool _hovered = false;

  @override
  Widget build(BuildContext context) {
    return MouseRegion(
      onEnter: (_) => setState(() => _hovered = true),
      onExit: (_) => setState(() => _hovered = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 140),
        padding: const EdgeInsets.all(7),
        decoration: BoxDecoration(
          color: _hovered ? const Color(0xFFFFF4E6) : const Color(0xFFFFFAF3),
          borderRadius: BorderRadius.circular(9),
          border: Border.all(color: const Color(0xFFFFD7AA)),
          boxShadow: [
            BoxShadow(
              color: const Color(0x1A0E1A2A),
              blurRadius: _hovered ? 12 : 7,
              offset: Offset(0, _hovered ? 4 : 2),
            ),
          ],
        ),
        child: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(7),
              child: SizedBox(
                width: 58,
                height: 46,
                child: K1SafeImage(
                  assetPath: widget.item.assetPath,
                  fallbackUrl:
                      'https://images.unsplash.com/photo-1555396273-367ea4eb4db5',
                  fit: BoxFit.cover,
                  placeholderIcon: widget.item.icon,
                  placeholderBackground: const Color(0xFF2A5D94),
                ),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    widget.item.title,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                        fontWeight: FontWeight.w900,
                        color: K1Colors.text,
                        fontSize: 11.5),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    widget.item.subtitle,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        color: K1Colors.muted,
                        fontSize: 10.5),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    widget.item.distance,
                    style: const TextStyle(
                        fontWeight: FontWeight.w800,
                        color: K1Colors.orangeDark,
                        fontSize: 10.5),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 6),
            AnimatedContainer(
              duration: const Duration(milliseconds: 140),
              padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
              decoration: BoxDecoration(
                color: _hovered ? const Color(0xFFEA7700) : K1Colors.orange,
                borderRadius: BorderRadius.circular(999),
                boxShadow: [
                  if (_hovered)
                    BoxShadow(
                      color: K1Colors.orange.withValues(alpha: 0.45),
                      blurRadius: 10,
                      offset: const Offset(0, 3),
                    ),
                ],
              ),
              child: Text(
                widget.item.cta,
                style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                    fontSize: 10.5),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
