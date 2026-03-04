import 'package:flutter/material.dart';

import '../data/module_images.dart';
import '../theme/k1_colors.dart';
import 'k1_safe_image.dart';

class K1ImageModule {
  const K1ImageModule({
    required this.name,
    required this.description,
    required this.moduleKey,
    required this.assetPath,
    required this.icon,
    required this.height,
    this.ctaLabel = 'Open',
    this.onTap,
  });

  final String name;
  final String description;
  final String moduleKey;
  final String assetPath;
  final IconData icon;
  final double height;
  final String ctaLabel;
  final VoidCallback? onTap;
}

class K1ImageModuleCards extends StatelessWidget {
  const K1ImageModuleCards({super.key, required this.modules});

  final List<K1ImageModule> modules;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final twoCols = constraints.maxWidth >= 500;
        if (!twoCols) {
          return Column(
            children: [
              for (var i = 0; i < modules.length; i++) ...[
                _ModuleImageCard(module: modules[i]),
                if (i < modules.length - 1) const SizedBox(height: 8),
              ],
            ],
          );
        }

        final left = <K1ImageModule>[];
        final right = <K1ImageModule>[];
        for (var i = 0; i < modules.length; i++) {
          if (i.isEven) {
            left.add(modules[i]);
          } else {
            right.add(modules[i]);
          }
        }

        return Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Column(
                children: [
                  for (var i = 0; i < left.length; i++) ...[
                    _ModuleImageCard(module: left[i]),
                    if (i < left.length - 1) const SizedBox(height: 8),
                  ],
                ],
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Column(
                children: [
                  for (var i = 0; i < right.length; i++) ...[
                    _ModuleImageCard(module: right[i]),
                    if (i < right.length - 1) const SizedBox(height: 8),
                  ],
                ],
              ),
            ),
          ],
        );
      },
    );
  }
}

class _ModuleImageCard extends StatefulWidget {
  const _ModuleImageCard({required this.module});

  final K1ImageModule module;

  @override
  State<_ModuleImageCard> createState() => _ModuleImageCardState();
}

class _ModuleImageCardState extends State<_ModuleImageCard> {
  bool _hovered = false;

  @override
  Widget build(BuildContext context) {
    return MouseRegion(
      onEnter: (_) => setState(() => _hovered = true),
      onExit: (_) => setState(() => _hovered = false),
      child: InkWell(
        onTap: widget.module.onTap,
        borderRadius: BorderRadius.circular(10),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 160),
          height: widget.module.height,
          transform: Matrix4.translationValues(0, _hovered ? -2 : 0, 0),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: const Color(0xFFFFD7A8)),
            boxShadow: [
              BoxShadow(
                color: const Color(0x220D1A2B),
                blurRadius: _hovered ? 14 : 9,
                offset: Offset(0, _hovered ? 5 : 3),
              ),
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: Stack(
              fit: StackFit.expand,
              children: [
                K1SafeImage(
                  assetPath: widget.module.assetPath,
                  fallbackUrl: moduleImages[widget.module.moduleKey] ??
                      moduleImages['esoko']!,
                  fit: BoxFit.cover,
                  placeholderIcon: widget.module.icon,
                  placeholderBackground: const Color(0xFF2F588E),
                ),
                DecoratedBox(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        Colors.black.withValues(alpha: 0.18),
                        Colors.black.withValues(alpha: 0.68),
                      ],
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                    ),
                  ),
                ),
                Positioned(
                  left: 10,
                  top: 10,
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 140),
                    width: 28,
                    height: 28,
                    decoration: BoxDecoration(
                      color: _hovered
                          ? K1Colors.orange
                          : Colors.white.withValues(alpha: 0.90),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      widget.module.icon,
                      size: 16,
                      color: _hovered ? Colors.white : K1Colors.orangeDark,
                    ),
                  ),
                ),
                Positioned(
                  left: 10,
                  right: 10,
                  bottom: 10,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.module.name,
                        style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            fontSize: 13),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        widget.module.description,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.94),
                          fontWeight: FontWeight.w600,
                          fontSize: 11.5,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 9, vertical: 4),
                        decoration: BoxDecoration(
                          color: K1Colors.orange,
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text(
                          widget.module.ctaLabel,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            fontSize: 10.5,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
