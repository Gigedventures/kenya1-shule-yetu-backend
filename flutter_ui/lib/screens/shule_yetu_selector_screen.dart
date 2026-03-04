import 'package:flutter/material.dart';

import '../app_router.dart';
import '../widgets/k1_top_bar.dart';

class ShuleYetuSelectorScreen extends StatelessWidget {
  const ShuleYetuSelectorScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            final sidePadding = constraints.maxWidth > 520 ? 18.0 : 12.0;
            return Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF2B63B7), Color(0xFF8CB8F2)],
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                ),
              ),
              child: Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 460),
                  child: Padding(
                    padding: EdgeInsets.fromLTRB(sidePadding, 14, sidePadding, 16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const K1TopBar(title: 'Kenya 1', dark: true),
                        const SizedBox(height: 20),
                        const Center(
                          child: Text(
                            'Choose Shule Yetu Mode',
                            style: TextStyle(color: Colors.white, fontSize: 34 / 1.4, fontWeight: FontWeight.w900),
                          ),
                        ),
                        const SizedBox(height: 24),
                        Expanded(
                          child: Row(
                            children: [
                              Expanded(
                                child: _LevelCard(
                                  title: 'Parent',
                                  subtitle: 'Juniors | CBC Home View',
                                  colors: const [Color(0xFF4CAF50), Color(0xFF2E7D32)],
                                  onTap: () => Navigator.of(context).pushNamed(AppRouter.juniors),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: _LevelCard(
                                  title: 'Student',
                                  subtitle: 'Seniors | Campus View',
                                  colors: const [Color(0xFF0D1B3E), Color(0xFF1A3B77)],
                                  onTap: () => Navigator.of(context).pushNamed(AppRouter.seniors),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 14),
                        Row(
                          children: [
                            Expanded(
                              child: FilledButton(
                                style: FilledButton.styleFrom(
                                  backgroundColor: const Color(0xFFBFE7FF),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                onPressed: () => Navigator.of(context).pushNamed(AppRouter.juniors),
                                child: const Text('Parent', style: TextStyle(color: Color(0xFF12335E), fontWeight: FontWeight.w800)),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: FilledButton(
                                style: FilledButton.styleFrom(
                                  backgroundColor: const Color(0xFF192A4F),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                onPressed: () => Navigator.of(context).pushNamed(AppRouter.seniors),
                                child: const Text('Student', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}

class _LevelCard extends StatelessWidget {
  const _LevelCard({
    required this.title,
    required this.subtitle,
    required this.colors,
    required this.onTap,
  });

  final String title;
  final String subtitle;
  final List<Color> colors;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(18),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(18),
          gradient: LinearGradient(colors: colors, begin: Alignment.topCenter, end: Alignment.bottomCenter),
          border: Border.all(color: Colors.white, width: 3),
        ),
        padding: const EdgeInsets.fromLTRB(12, 20, 12, 14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Expanded(
              child: Align(
                alignment: Alignment.center,
                child: Icon(Icons.people_alt, size: 78, color: Colors.white),
              ),
            ),
            Text(title, style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.w900)),
            const SizedBox(height: 4),
            Text(subtitle, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    );
  }
}
