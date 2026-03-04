import 'package:flutter/material.dart';

import '../theme/k1_colors.dart';

enum K1WidgetCardStyle {
  standard,
  hero,
  strip,
  pill,
  circular,
}

class K1WidgetCard extends StatefulWidget {
  const K1WidgetCard({
    super.key,
    required this.title,
    required this.accent,
    required this.icon,
    required this.child,
    this.minHeight = 132,
    this.style = K1WidgetCardStyle.standard,
  });

  final String title;
  final Color accent;
  final IconData icon;
  final Widget child;
  final double minHeight;
  final K1WidgetCardStyle style;

  @override
  State<K1WidgetCard> createState() => _K1WidgetCardState();
}

class _K1WidgetCardState extends State<K1WidgetCard> {
  bool _hovered = false;

  @override
  Widget build(BuildContext context) {
    final radius = widget.style == K1WidgetCardStyle.pill
        ? 999.0
        : widget.style == K1WidgetCardStyle.circular
            ? 20.0
            : 8.0;
    final isHero = widget.style == K1WidgetCardStyle.hero;
    final isStrip = widget.style == K1WidgetCardStyle.strip;
    final isCircular = widget.style == K1WidgetCardStyle.circular;

    return MouseRegion(
      onEnter: (_) => setState(() => _hovered = true),
      onExit: (_) => setState(() => _hovered = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 140),
        constraints: BoxConstraints(minHeight: widget.minHeight),
        decoration: BoxDecoration(
          color: K1Colors.surface,
          borderRadius: BorderRadius.circular(radius),
          border: Border.all(
              color: isHero ? const Color(0xFFF9B566) : K1Colors.border),
          gradient: isHero
              ? const LinearGradient(
                  colors: [Color(0xFFFFAA3D), Color(0xFFE26C00)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                )
              : null,
          boxShadow: [
            BoxShadow(
              color: const Color(0x1F0E1A2A),
              blurRadius: _hovered ? 15 : 9,
              offset: Offset(0, _hovered ? 5 : 2),
            ),
          ],
        ),
        clipBehavior: Clip.antiAlias,
        transform: Matrix4.translationValues(0, _hovered ? -1.5 : 0, 0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              height: isStrip ? 2.5 : 4,
              decoration: BoxDecoration(
                color: isHero
                    ? Colors.white.withValues(alpha: 0.6)
                    : widget.accent,
                borderRadius:
                    BorderRadius.vertical(top: Radius.circular(radius)),
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: isHero
                    ? Colors.white.withValues(alpha: 0.16)
                    : widget.accent.withValues(alpha: 0.08),
                border: Border(
                    bottom: BorderSide(
                        color: isHero
                            ? Colors.white.withValues(alpha: 0.35)
                            : widget.accent.withValues(alpha: 0.30))),
              ),
              child: Row(
                children: [
                  Container(
                    width: isCircular ? 26 : 22,
                    height: isCircular ? 26 : 22,
                    decoration: BoxDecoration(
                      color: isHero
                          ? Colors.white.withValues(alpha: 0.28)
                          : (_hovered ? K1Colors.orange : K1Colors.orangeSoft),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(
                          color: isHero
                              ? Colors.white.withValues(alpha: 0.50)
                              : const Color(0xFFFFD29D)),
                      boxShadow: [
                        if (_hovered && !isHero)
                          BoxShadow(
                            color: K1Colors.orange.withValues(alpha: 0.35),
                            blurRadius: 10,
                            offset: const Offset(0, 2),
                          ),
                      ],
                    ),
                    child: Icon(
                      widget.icon,
                      size: isCircular ? 15 : 14,
                      color: isHero
                          ? Colors.white
                          : (_hovered ? Colors.white : K1Colors.orangeDark),
                    ),
                  ),
                  const SizedBox(width: 7),
                  Expanded(
                    child: Text(
                      widget.title,
                      style: TextStyle(
                        color: isHero ? Colors.white : widget.accent,
                        fontWeight: FontWeight.w900,
                        fontSize: 12.5,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: EdgeInsets.fromLTRB(9, 9, 9, isStrip ? 8 : 9),
              child: widget.child,
            ),
          ],
        ),
      ),
    );
  }
}
