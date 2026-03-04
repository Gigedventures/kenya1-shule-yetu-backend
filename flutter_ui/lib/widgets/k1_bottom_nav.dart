import 'package:flutter/material.dart';

import '../theme/app_icons.dart';

class K1BottomNav extends StatelessWidget {
  const K1BottomNav({super.key, required this.index});

  final int index;

  static const _items = [
    ('Home', AppIcons.navHome),
    ('Services', AppIcons.navServices),
    ('Wallet', AppIcons.navWallet),
    ('Profile', AppIcons.navProfile),
    ('Chat', AppIcons.navChat),
  ];

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 78,
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: Color(0xFFE0E5EF))),
      ),
      child: Row(
        children: List.generate(_items.length, (i) {
          final active = i == index;
          final item = _items[i];
          return Expanded(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                AppIcons.svg(
                  item.$2,
                  width: 24,
                  height: 24,
                  colorFilter: ColorFilter.mode(active ? const Color(0xFF1E61C6) : const Color(0xFF7B8AA3), BlendMode.srcIn),
                ),
                const SizedBox(height: 4),
                Text(
                  item.$1,
                  style: TextStyle(color: active ? const Color(0xFF1E61C6) : const Color(0xFF7B8AA3), fontWeight: FontWeight.w700),
                ),
              ],
            ),
          );
        }),
      ),
    );
  }
}
