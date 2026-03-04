import 'package:flutter/material.dart';

import 'k1_safe_image.dart';

class K1VideoReelItem {
  const K1VideoReelItem({
    required this.title,
    required this.thumbnailAsset,
    required this.duration,
  });

  final String title;
  final String thumbnailAsset;
  final String duration;
}

class K1VideoReelWidget extends StatelessWidget {
  const K1VideoReelWidget({super.key, required this.items});

  final List<K1VideoReelItem> items;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 138,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: items.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) => _VideoCard(item: items[index]),
      ),
    );
  }
}

class _VideoCard extends StatefulWidget {
  const _VideoCard({required this.item});

  final K1VideoReelItem item;

  @override
  State<_VideoCard> createState() => _VideoCardState();
}

class _VideoCardState extends State<_VideoCard> {
  bool _hovered = false;

  @override
  Widget build(BuildContext context) {
    return MouseRegion(
      onEnter: (_) => setState(() => _hovered = true),
      onExit: (_) => setState(() => _hovered = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 140),
        width: 178,
        transform: Matrix4.translationValues(0, _hovered ? -2 : 0, 0),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: const Color(0xFFFFD3A2)),
          boxShadow: [
            BoxShadow(
              color: const Color(0x1F101B2E),
              blurRadius: _hovered ? 13 : 8,
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
                assetPath: widget.item.thumbnailAsset,
                fallbackUrl:
                    'https://images.unsplash.com/photo-1498050108023-c5249f4df085',
                fit: BoxFit.cover,
                placeholderIcon: Icons.ondemand_video,
                placeholderBackground: const Color(0xFF2A65A9),
              ),
              DecoratedBox(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      Colors.black.withValues(alpha: 0.15),
                      Colors.black.withValues(alpha: 0.72),
                    ],
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                  ),
                ),
              ),
              const Center(
                child: Icon(Icons.play_circle_fill_rounded,
                    color: Colors.white, size: 38),
              ),
              Positioned(
                top: 8,
                right: 8,
                child: Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.55),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    widget.item.duration,
                    style: const TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.w800),
                  ),
                ),
              ),
              Positioned(
                left: 8,
                right: 8,
                bottom: 8,
                child: Text(
                  widget.item.title,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                    fontSize: 11.5,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
