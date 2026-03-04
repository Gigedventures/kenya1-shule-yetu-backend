import 'package:flutter/material.dart';

class K1SafeImage extends StatelessWidget {
  const K1SafeImage({
    super.key,
    required this.assetPath,
    required this.fallbackUrl,
    this.fit = BoxFit.cover,
    this.placeholderIcon = Icons.image_not_supported_outlined,
    this.placeholderBackground = const Color(0xFF2C598F),
  });

  final String assetPath;
  final String fallbackUrl;
  final BoxFit fit;
  final IconData placeholderIcon;
  final Color placeholderBackground;

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      assetPath,
      fit: fit,
      errorBuilder: (_, __, ___) {
        return Image.network(
          fallbackUrl,
          fit: fit,
          errorBuilder: (_, __, ___) {
            return Container(
              color: placeholderBackground,
              alignment: Alignment.center,
              child: Icon(placeholderIcon, color: Colors.white, size: 26),
            );
          },
        );
      },
    );
  }
}
