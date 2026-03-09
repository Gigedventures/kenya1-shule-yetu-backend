import 'package:flutter/material.dart';

import '../../../data/mock_users.dart';
import '../../../theme/k1_colors.dart';

class PrimaryStudentDashboardScreen extends StatelessWidget {
  const PrimaryStudentDashboardScreen({
    super.key,
    required this.student,
  });

  final Student student;

  static const _schedule = [
    _ScheduleItem('07:45 AM', '📘 Mathematics Quest', 'Room 4B'),
    _ScheduleItem('09:30 AM', '📖 Reading Circle', 'Reading Lab'),
    _ScheduleItem('11:00 AM', '🔬 Science Explorers', 'Innovation Hub'),
    _ScheduleItem('02:00 PM', '⚽ Playtime League', 'Main Field'),
  ];

  static const _homework = [
    _HomeworkItem('Math fractions worksheet', 'Due today, 6:00 PM', 0.8, '🍕'),
    _HomeworkItem('English reading log', 'Due tomorrow', 0.45, '📚'),
    _HomeworkItem('Science experiment notes', 'Due Friday', 0.6, '🧪'),
  ];

  static const _missions = [
    _MissionItem('7-day reading streak', '5/7 days', Icons.local_fire_department_rounded, Color(0xFFFF8F3D)),
    _MissionItem('Homework hero', '2 tasks left', Icons.task_alt_rounded, Color(0xFF3478F6)),
    _MissionItem('Star collector', '2450 stars', Icons.star_rounded, Color(0xFFE9B308)),
  ];

  static const _reading = [
    _ReadingItem('The Clever Hare', '12 pages left', '🐇'),
    _ReadingItem('Junior Science Wonders', 'Page 84 reached', '🚀'),
  ];

  static const _transportStops = [
    _TransportStop('Bus 21', 'Jogoo Road', true),
    _TransportStop('Next stop', 'Donholm Stage', false),
    _TransportStop('ETA', '5 minutes', false),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFFF7EF),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(14, 14, 14, 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _PrimaryHero(student: student),
              const SizedBox(height: 14),
              _SectionShell(
                title: 'Mission Board',
                subtitle: 'Stars, streaks, and fun targets for today',
                child: Column(children: _missions.map((mission) => _MissionCard(item: mission)).toList()),
              ),
              const SizedBox(height: 14),
              _SectionShell(
                title: 'Today\'s Adventure Map',
                subtitle: 'All your classes and activities in one scroll',
                child: Column(
                  children: _schedule
                      .map((item) => Padding(
                            padding: const EdgeInsets.only(bottom: 10),
                            child: _ScheduleCard(item: item),
                          ))
                      .toList(),
                ),
              ),
              const SizedBox(height: 14),
              _SectionShell(
                title: 'Homework Island',
                subtitle: 'Finish each task to unlock more stars',
                child: Column(children: _homework.map((item) => _HomeworkCard(item: item)).toList()),
              ),
              const SizedBox(height: 14),
              _SectionShell(
                title: 'Reading Rocket',
                subtitle: 'Books you are enjoying this week',
                child: Column(children: _reading.map((item) => _ReadingCard(item: item)).toList()),
              ),
              const SizedBox(height: 14),
              _SectionShell(
                title: 'Emoji Rewards',
                subtitle: 'Big wins from your class teacher',
                child: Wrap(
                  spacing: 10,
                  runSpacing: 10,
                  children: const [
                    _RewardChip(label: 'Super Reader', emoji: '📘', color: Color(0xFF3478F6)),
                    _RewardChip(label: 'Kind Friend', emoji: '💛', color: Color(0xFFE35D8E)),
                    _RewardChip(label: 'Math Champ', emoji: '🧠', color: Color(0xFF0F9D7A)),
                    _RewardChip(label: 'Clean Desk', emoji: '✨', color: Color(0xFFF59E0B)),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              _SectionShell(
                title: 'Transport Tracker',
                subtitle: 'Check how far your bus is from home',
                child: Column(children: _transportStops.map((item) => _TransportRow(item: item)).toList()),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PrimaryHero extends StatelessWidget {
  const _PrimaryHero({required this.student});

  final Student student;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFFFFC94B), Color(0xFFFF8D5C), Color(0xFF6C8DFF)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(28),
        boxShadow: const [
          BoxShadow(
            color: Color(0x26D77E28),
            blurRadius: 24,
            offset: Offset(0, 12),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 72,
                height: 72,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.22),
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white.withValues(alpha: 0.45), width: 2),
                ),
                child: const Text('🧒', style: TextStyle(fontSize: 34)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      student.name,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 22),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${student.className} • Adm ${student.admissionNumber}',
                      style: const TextStyle(color: Color(0xFFFFF4DA), fontWeight: FontWeight.w700),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Teacher: Ms. Akinyi • Today looks bright and busy',
                      style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 12),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          const Wrap(
            spacing: 10,
            runSpacing: 10,
            children: [
              _HeroPill(label: '⭐ Stars', value: '2450'),
              _HeroPill(label: '🔥 Streak', value: '7 days'),
              _HeroPill(label: '📍 Bus', value: '5 mins'),
            ],
          ),
        ],
      ),
    );
  }
}

class _SectionShell extends StatelessWidget {
  const _SectionShell({
    required this.title,
    required this.subtitle,
    required this.child,
  });

  final String title;
  final String subtitle;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: const Color(0xFFFFE6CC)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x120E1A2A),
            blurRadius: 16,
            offset: Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w900, fontSize: 17)),
          const SizedBox(height: 4),
          Text(subtitle, style: const TextStyle(color: K1Colors.muted, fontWeight: FontWeight.w600)),
          const SizedBox(height: 14),
          child,
        ],
      ),
    );
  }
}

class _MissionCard extends StatelessWidget {
  const _MissionCard({required this.item});

  final _MissionItem item;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: item.color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: item.color.withValues(alpha: 0.2)),
      ),
      child: Row(
        children: [
          Container(
            width: 46,
            height: 46,
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16)),
            child: Icon(item.icon, color: item.color),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item.title, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w800)),
                const SizedBox(height: 4),
                Text(item.value, style: const TextStyle(color: K1Colors.muted, fontWeight: FontWeight.w700)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _ScheduleCard extends StatelessWidget {
  const _ScheduleCard({required this.item});

  final _ScheduleItem item;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF4E5),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFFFE0B2)),
      ),
      child: Row(
        children: [
          Container(
            width: 72,
            padding: const EdgeInsets.symmetric(vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Text(
              item.time,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Color(0xFFDF6C17), fontWeight: FontWeight.w900, fontSize: 12),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item.title, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w800)),
                const SizedBox(height: 3),
                Text(item.location, style: const TextStyle(color: K1Colors.muted, fontWeight: FontWeight.w700)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _HomeworkCard extends StatelessWidget {
  const _HomeworkCard({required this.item});

  final _HomeworkItem item;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFFF2F7FF), Color(0xFFFFFFFF)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFD7E6FF)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(item.emoji, style: const TextStyle(fontSize: 22)),
              const SizedBox(width: 10),
              Expanded(
                child: Text(item.title, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w800)),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(item.deadline, style: const TextStyle(color: K1Colors.muted, fontWeight: FontWeight.w700, fontSize: 12)),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(999),
            child: LinearProgressIndicator(
              value: item.progress,
              minHeight: 10,
              backgroundColor: const Color(0xFFD9E5F8),
              valueColor: const AlwaysStoppedAnimation(Color(0xFF3478F6)),
            ),
          ),
        ],
      ),
    );
  }
}

class _ReadingCard extends StatelessWidget {
  const _ReadingCard({required this.item});

  final _ReadingItem item;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFEFFAF2),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFC8EFD0)),
      ),
      child: Row(
        children: [
          Text(item.emoji, style: const TextStyle(fontSize: 24)),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item.title, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w800)),
                Text(item.meta, style: const TextStyle(color: K1Colors.muted, fontWeight: FontWeight.w700, fontSize: 12)),
              ],
            ),
          ),
          const Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Color(0xFF5E8A67)),
        ],
      ),
    );
  }
}

class _RewardChip extends StatelessWidget {
  const _RewardChip({
    required this.label,
    required this.emoji,
    required this.color,
  });

  final String label;
  final String emoji;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Text('$emoji  $label', style: TextStyle(color: color, fontWeight: FontWeight.w800)),
    );
  }
}

class _TransportRow extends StatelessWidget {
  const _TransportRow({required this.item});

  final _TransportStop item;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        children: [
          Container(
            width: 14,
            height: 14,
            decoration: BoxDecoration(
              color: item.highlight ? const Color(0xFF0F9D7A) : const Color(0xFFD0DCF0),
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(child: Text(item.label, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w800))),
          Text(item.value, style: const TextStyle(color: K1Colors.muted, fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}

class _HeroPill extends StatelessWidget {
  const _HeroPill({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.18),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: Colors.white.withValues(alpha: 0.28)),
      ),
      child: Text('$label  $value', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900)),
    );
  }
}

class _ScheduleItem {
  const _ScheduleItem(this.time, this.title, this.location);

  final String time;
  final String title;
  final String location;
}

class _HomeworkItem {
  const _HomeworkItem(this.title, this.deadline, this.progress, this.emoji);

  final String title;
  final String deadline;
  final double progress;
  final String emoji;
}

class _MissionItem {
  const _MissionItem(this.title, this.value, this.icon, this.color);

  final String title;
  final String value;
  final IconData icon;
  final Color color;
}

class _ReadingItem {
  const _ReadingItem(this.title, this.meta, this.emoji);

  final String title;
  final String meta;
  final String emoji;
}

class _TransportStop {
  const _TransportStop(this.label, this.value, this.highlight);

  final String label;
  final String value;
  final bool highlight;
}
