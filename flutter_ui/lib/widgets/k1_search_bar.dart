import 'package:flutter/material.dart';

import '../theme/app_icons.dart';

class K1SearchBar extends StatelessWidget {
  const K1SearchBar({super.key, required this.hint});

  final String hint;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      height: 56,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: const [BoxShadow(color: Color(0x14000000), blurRadius: 8, offset: Offset(0, 2))],
      ),
      child: Row(
        children: [
          AppIcons.svg(
            AppIcons.search,
            width: 24,
            height: 24,
            colorFilter: const ColorFilter.mode(Color(0xFF8592A8), BlendMode.srcIn),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              hint,
              style: const TextStyle(color: Color(0xFF78879D), fontSize: 16, fontWeight: FontWeight.w500),
            ),
          ),
          Container(width: 1, height: 24, color: const Color(0xFFD4DAE6)),
          const SizedBox(width: 12),
          AppIcons.svg(AppIcons.mic, width: 24, height: 24),
        ],
      ),
    );
  }
}
