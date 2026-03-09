import 'package:flutter/material.dart';

import '../data/mock_users.dart';
import '../widgets/k1_top_bar.dart';
import 'juniors_parent_dashboard_screen.dart';
import 'student/junior/junior_student_dashboard_screen.dart';
import 'student/primary/primary_student_dashboard_screen.dart';
import 'student/senior/senior_student_dashboard_screen.dart';
import 'student/tertiary/tertiary_student_dashboard_screen.dart';

class ShuleYetuSelectorScreen extends StatelessWidget {
  const ShuleYetuSelectorScreen({super.key});

  static const _cards = [
    _LevelCardData(
      title: 'Parent',
      subtitle: 'View your children and school updates',
      icon: Icons.groups_2_rounded,
      colors: [Color(0xFF2F8F63), Color(0xFF79C89B)],
    ),
    _LevelCardData(
      title: 'Primary Student',
      subtitle: 'Grades 1-5',
      icon: Icons.auto_awesome_rounded,
      colors: [Color(0xFFF59E0B), Color(0xFFFFD76A)],
    ),
    _LevelCardData(
      title: 'Junior Student',
      subtitle: 'Grades 6-8',
      icon: Icons.quiz_rounded,
      colors: [Color(0xFF1E63B5), Color(0xFF7EB8FF)],
    ),
    _LevelCardData(
      title: 'Senior Student',
      subtitle: 'Grades 9-12',
      icon: Icons.insights_rounded,
      colors: [Color(0xFF1D2A44), Color(0xFF4E6D98)],
    ),
    _LevelCardData(
      title: 'Tertiary Student',
      subtitle: 'College / TVET / University',
      icon: Icons.school_rounded,
      colors: [Color(0xFF6A2E8C), Color(0xFFAE7BDA)],
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            final columns = _columnsForWidth(constraints.maxWidth);
            final sidePadding = constraints.maxWidth >= 1100
                ? 24.0
                : constraints.maxWidth >= 700
                    ? 18.0
                    : 12.0;
            final gridWidth = constraints.maxWidth >= 1200 ? 1100.0 : constraints.maxWidth;
            final itemWidth = (gridWidth - (sidePadding * 2) - ((columns - 1) * 16)) / columns;

            return Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF19477F), Color(0xFF4C87D1), Color(0xFFEAF4FF)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
              child: Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 1100),
                  child: SingleChildScrollView(
                    padding: EdgeInsets.fromLTRB(sidePadding, 14, sidePadding, 20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const K1TopBar(title: 'Kenya 1', dark: true),
                        const SizedBox(height: 18),
                        const Text(
                          'Choose Shule Yetu Experience',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 28,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Select the parent view or a student level-specific dashboard.',
                          style: TextStyle(
                            color: Color(0xFFE0ECFF),
                            fontWeight: FontWeight.w600,
                            fontSize: 14,
                          ),
                        ),
                        const SizedBox(height: 22),
                        Wrap(
                          spacing: 16,
                          runSpacing: 16,
                          children: [
                            for (var i = 0; i < _cards.length; i++)
                              SizedBox(
                                width: itemWidth,
                                child: _LevelCard(
                                  data: _cards[i],
                                  onTap: () => _openCard(context, i),
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

  int _columnsForWidth(double width) {
    if (width >= 1000) return 3;
    if (width >= 640) return 2;
    return 1;
  }

  void _openCard(BuildContext context, int index) {
    final page = switch (index) {
      0 => const JuniorsParentDashboardScreen(),
      1 => const PrimaryStudentDashboardScreen(student: MockUsersData.primaryDemoStudent),
      2 => const JuniorStudentDashboardScreen(student: MockUsersData.juniorDemoStudent),
      3 => const SeniorStudentDashboardScreen(student: MockUsersData.seniorDemoStudent),
      _ => const TertiaryStudentDashboardScreen(student: MockUsersData.tertiaryDemoStudent),
    };

    Navigator.of(context).push(MaterialPageRoute(builder: (_) => page));
  }
}

class _LevelCardData {
  const _LevelCardData({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.colors,
  });

  final String title;
  final String subtitle;
  final IconData icon;
  final List<Color> colors;
}

class _LevelCard extends StatefulWidget {
  const _LevelCard({
    required this.data,
    required this.onTap,
  });

  final _LevelCardData data;
  final VoidCallback onTap;

  @override
  State<_LevelCard> createState() => _LevelCardState();
}

class _LevelCardState extends State<_LevelCard> {
  bool _hovered = false;
  bool _pressed = false;

  @override
  Widget build(BuildContext context) {
    final scale = _pressed ? 0.98 : (_hovered ? 1.01 : 1.0);

    return MouseRegion(
      onEnter: (_) => setState(() => _hovered = true),
      onExit: (_) => setState(() {
        _hovered = false;
        _pressed = false;
      }),
      child: AnimatedScale(
        scale: scale,
        duration: const Duration(milliseconds: 140),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          curve: Curves.easeOut,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(24),
            gradient: LinearGradient(
              colors: widget.data.colors,
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            border: Border.all(
              color: Colors.white.withValues(alpha: _hovered ? 0.85 : 0.65),
              width: _hovered ? 2.6 : 2,
            ),
            boxShadow: [
              BoxShadow(
                color: const Color(0x33193A69),
                blurRadius: _hovered ? 26 : 18,
                offset: Offset(0, _hovered ? 14 : 9),
              ),
            ],
          ),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              borderRadius: BorderRadius.circular(24),
              onTap: widget.onTap,
              onTapDown: (_) => setState(() => _pressed = true),
              onTapCancel: () => setState(() => _pressed = false),
              onTapUp: (_) => setState(() => _pressed = false),
              child: Padding(
                padding: const EdgeInsets.fromLTRB(18, 20, 18, 18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 58,
                      height: 58,
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.18),
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: Colors.white.withValues(alpha: 0.24)),
                      ),
                      child: Icon(widget.data.icon, color: Colors.white, size: 30),
                    ),
                    const SizedBox(height: 22),
                    Text(
                      widget.data.title,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 22,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      widget.data.subtitle,
                      style: const TextStyle(
                        color: Color(0xFFF5F9FF),
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        height: 1.35,
                      ),
                    ),
                    const SizedBox(height: 22),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.16),
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: const Text(
                            'Open dashboard',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w800,
                              fontSize: 12,
                            ),
                          ),
                        ),
                        const Spacer(),
                        Icon(
                          Icons.arrow_forward_rounded,
                          color: Colors.white.withValues(alpha: 0.95),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
