import 'package:flutter/material.dart';

import '../theme/app_icons.dart';

class K1TopBar extends StatelessWidget {
  const K1TopBar({
    super.key,
    required this.title,
    this.subtitle,
    this.dark = false,
    this.mailBadge = 0,
  });

  final String title;
  final String? subtitle;
  final bool dark;
  final int mailBadge;

  @override
  Widget build(BuildContext context) {
    final textColor = dark ? Colors.white : const Color(0xFF11325F);
    return Row(
      children: [
        AppIcons.svg(AppIcons.kenyaFlag, width: 30, height: 20),
        const SizedBox(width: 10),
        Expanded(
          child: RichText(
            text: TextSpan(
              children: [
                TextSpan(
                  text: title,
                  style: TextStyle(
                    color: textColor,
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                if (subtitle != null)
                  TextSpan(
                    text: subtitle,
                    style: const TextStyle(
                      color: Color(0xFFF5B02A),
                      fontSize: 20,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
              ],
            ),
          ),
        ),
        Stack(
          clipBehavior: Clip.none,
          children: [
            Icon(dark ? Icons.mail_outline : Icons.mail, color: textColor),
            if (mailBadge > 0)
              Positioned(
                top: -8,
                right: -8,
                child: Container(
                  width: 18,
                  height: 18,
                  alignment: Alignment.center,
                  decoration: const BoxDecoration(color: Color(0xFFE53935), shape: BoxShape.circle),
                  child: Text(
                    '$mailBadge',
                    style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800),
                  ),
                ),
              ),
          ],
        ),
        const SizedBox(width: 12),
        Container(
          width: 38,
          height: 38,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(20),
            gradient: const LinearGradient(colors: [Color(0xFF8BC34A), Color(0xFF4CAF50)]),
            border: Border.all(color: Colors.white, width: 2),
          ),
          child: const Icon(Icons.person, color: Colors.white),
        ),
      ],
    );
  }
}
