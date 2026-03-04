import 'package:flutter/material.dart';

import '../../theme/k1_colors.dart';

class StudentDashboardScreen extends StatelessWidget {
  const StudentDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF0F5FF),
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            final columns = _columnsForWidth(constraints.maxWidth);
            final gap = 12.0;
            final cardWidth = columns == 1
                ? constraints.maxWidth - 24
                : (constraints.maxWidth - 24 - ((columns - 1) * gap)) / columns;

            return Column(
              children: [
                const _ProfileHeader(),
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.fromLTRB(12, 10, 12, 14),
                    child: Wrap(
                      spacing: gap,
                      runSpacing: gap,
                      children: [
                        for (final section in _sections)
                          SizedBox(
                            width: cardWidth,
                            child: _GamifiedCard(
                              title: section.title,
                              description: section.description,
                              icon: section.icon,
                              imageUrl: section.imageUrl,
                              child: section.child,
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }

  int _columnsForWidth(double width) {
    if (width >= 1400) return 4;
    if (width >= 800) return 2;
    return 1;
  }

  List<_SectionData> get _sections => const [
        _SectionData(
          title: "Today's Schedule 📅",
          description: 'Your classes and activities for today',
          icon: Icons.calendar_today_outlined,
          imageUrl:
              'https://images.unsplash.com/photo-1522202176988-66273c2fd55f',
          child: _SimpleLines(
            lines: [
              '08:00 - Math (Room 4B)',
              '10:30 - Science Lab',
              '13:00 - Art Club',
            ],
          ),
        ),
        _SectionData(
          title: 'Homework Tracker 📝',
          description: 'Stay on top of assignments',
          icon: Icons.assignment_outlined,
          imageUrl:
              'https://images.unsplash.com/photo-1434030216411-0b793f4b4173',
          child: _SimpleLines(
            lines: [
              'Math worksheet - Due 6 PM',
              'English reading log - Due tomorrow',
              'Science revision - 75% done',
            ],
          ),
        ),
        _SectionData(
          title: 'Achievement Badges 🏅',
          description: 'Badges earned this week',
          icon: Icons.emoji_events_outlined,
          imageUrl:
              'https://images.unsplash.com/photo-1517649763962-0c623066013b',
          child: _SimpleLines(
            lines: [
              'Perfect Attendance',
              'Math Master',
              'Reading Hero',
            ],
          ),
        ),
        _SectionData(
          title: 'Leaderboard 🚀',
          description: 'Top learners in Grade 4',
          icon: Icons.leaderboard_outlined,
          imageUrl:
              'https://images.unsplash.com/photo-1461896836934-ffe607ba8211',
          child: _SimpleLines(
            lines: [
              '1. Aisha - 2,740 pts',
              '2. Brian - 2,450 pts',
              '3. Kevin - 2,390 pts',
            ],
          ),
        ),
        _SectionData(
          title: 'School Announcements 📣',
          description: 'Latest school updates',
          icon: Icons.campaign_outlined,
          imageUrl:
              'https://images.unsplash.com/photo-1571260899304-425eee4c7efc',
          child: _SimpleLines(
            lines: [
              'Music Fest this Friday',
              'County Expo registration open',
              'Sports Day next week',
            ],
          ),
        ),
        _SectionData(
          title: 'Bus Tracker 🚌',
          description: 'Live route and ETA',
          icon: Icons.directions_bus_outlined,
          imageUrl:
              'https://images.unsplash.com/photo-1489515217757-5fd1be406fef',
          child: _SimpleLines(
            lines: [
              'Bus #21 from Umoja',
              'Current stop: Jogoo Road',
              'ETA: 5 mins',
            ],
          ),
        ),
        _SectionData(
          title: 'Library Books 📚',
          description: 'Current reads and recommendations',
          icon: Icons.local_library_outlined,
          imageUrl:
              'https://images.unsplash.com/photo-1491841550275-ad7854e35ca6',
          child: _SimpleLines(
            lines: [
              'Borrowed: Wonders of Science',
              'Due date: 14 Mar 2026',
              'Recommended: Kenya Wildlife Stories',
            ],
          ),
        ),
        _SectionData(
          title: 'Rewards Section 🎁',
          description: 'Redeem points for goodies',
          icon: Icons.card_giftcard_outlined,
          imageUrl:
              'https://images.unsplash.com/photo-1512909006721-3d6018887383',
          child: _SimpleLines(
            lines: [
              '2,450 pts available',
              'Reward: School kit - 1,200 pts',
              'Reward: Lunch voucher - 900 pts',
            ],
          ),
        ),
        _SectionData(
          title: 'AI Study Helper 🤖',
          description: 'Ask for topic explanations',
          icon: Icons.smart_toy_outlined,
          imageUrl:
              'https://images.unsplash.com/photo-1485827404703-89b55fcc595e',
          child: _SimpleLines(
            lines: [
              'Try: Explain fractions simply',
              'Try: 5 science quiz questions',
              'Try: Revision plan for this week',
            ],
          ),
        ),
        _SectionData(
          title: 'Daily Missions 🌟',
          description: 'Gamified goals to boost your streak',
          icon: Icons.flag_outlined,
          imageUrl:
              'https://images.unsplash.com/photo-1461896836934-ffe607ba8211',
          child: _SimpleLines(
            lines: [
              'Complete 2 homework tasks',
              'Read for 20 minutes',
              'Answer 1 AI helper quiz',
            ],
          ),
        ),
      ];
}

class _ProfileHeader extends StatelessWidget {
  const _ProfileHeader();

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF2E7DFF), Color(0xFF6B4BFF)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: const Color(0x332A4FA0),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Row(
        children: [
          const CircleAvatar(
            radius: 30,
            backgroundImage: NetworkImage(
                'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d'),
          ),
          const SizedBox(width: 12),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Brian Otieno',
                    style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 18)),
                SizedBox(height: 3),
                Text('Grade 4 Blue',
                    style: TextStyle(
                        color: Color(0xFFE7EEFF), fontWeight: FontWeight.w700)),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: const [
              _StatPill(
                  icon: Icons.stars_rounded, label: 'Points', value: '2450'),
              SizedBox(height: 6),
              _StatPill(
                  icon: Icons.local_fire_department_outlined,
                  label: 'Streak',
                  value: '7 days'),
            ],
          ),
        ],
      ),
    );
  }
}

class _GamifiedCard extends StatelessWidget {
  const _GamifiedCard({
    required this.title,
    required this.description,
    required this.icon,
    required this.imageUrl,
    required this.child,
  });

  final String title;
  final String description;
  final IconData icon;
  final String imageUrl;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      constraints: const BoxConstraints(minHeight: 220),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFDCE6F6)),
        boxShadow: const [
          BoxShadow(
              color: Color(0x1A0E1A2A), blurRadius: 10, offset: Offset(0, 4)),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            height: 88,
            child: Stack(
              fit: StackFit.expand,
              children: [
                Image.network(
                  imageUrl,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) =>
                      Container(color: const Color(0xFF3769A8)),
                ),
                DecoratedBox(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        Colors.black.withValues(alpha: 0.12),
                        Colors.black.withValues(alpha: 0.50)
                      ],
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                    ),
                  ),
                ),
                Positioned(
                  left: 10,
                  top: 10,
                  child: Container(
                    width: 30,
                    height: 30,
                    decoration: BoxDecoration(
                      color: K1Colors.orange,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(icon, color: Colors.white, size: 18),
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(10, 10, 10, 10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                    style: const TextStyle(
                        fontWeight: FontWeight.w900,
                        color: K1Colors.text,
                        fontSize: 13)),
                const SizedBox(height: 3),
                Text(description,
                    style: const TextStyle(
                        color: K1Colors.muted,
                        fontSize: 11.5,
                        fontWeight: FontWeight.w600)),
                const SizedBox(height: 10),
                child,
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SimpleLines extends StatelessWidget {
  const _SimpleLines({required this.lines});

  final List<String> lines;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        for (var i = 0; i < lines.length; i++) ...[
          Row(
            children: [
              const Icon(Icons.circle, size: 6, color: K1Colors.orangeDark),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  lines[i],
                  style: const TextStyle(
                      fontWeight: FontWeight.w700,
                      color: K1Colors.text,
                      fontSize: 11.5),
                ),
              ),
            ],
          ),
          if (i < lines.length - 1) const SizedBox(height: 6),
        ],
      ],
    );
  }
}

class _SectionData {
  const _SectionData({
    required this.title,
    required this.description,
    required this.icon,
    required this.imageUrl,
    required this.child,
  });

  final String title;
  final String description;
  final IconData icon;
  final String imageUrl;
  final Widget child;
}

class _StatPill extends StatelessWidget {
  const _StatPill(
      {required this.icon, required this.label, required this.value});

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.18),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: Colors.white.withValues(alpha: 0.36)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: Colors.white),
          const SizedBox(width: 4),
          Text('$label: $value',
              style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 11)),
        ],
      ),
    );
  }
}
