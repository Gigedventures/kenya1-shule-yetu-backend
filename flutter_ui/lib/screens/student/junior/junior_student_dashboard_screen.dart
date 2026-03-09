import 'package:flutter/material.dart';

import '../../../data/mock_users.dart';
import '../../../theme/k1_colors.dart';

class JuniorStudentDashboardScreen extends StatelessWidget {
  const JuniorStudentDashboardScreen({
    super.key,
    required this.student,
  });

  final Student student;

  static const _stats = [
    _StatItem('Assignments', '4 due', Icons.assignment_turned_in_outlined, Color(0xFF2563EB)),
    _StatItem('Quizzes', '2 today', Icons.quiz_outlined, Color(0xFF0F9D7A)),
    _StatItem('Clubs', 'Debate', Icons.groups_outlined, Color(0xFFF59E0B)),
    _StatItem('Projects', '1 build', Icons.architecture_outlined, Color(0xFFE35D8E)),
  ];

  static const _assignments = [
    _TaskItem('Integrated Science', 'Energy transfer worksheet', 'Due today • 5:30 PM', 0.75),
    _TaskItem('Mathematics', 'Algebra challenge set 3', 'Due tomorrow', 0.4),
    _TaskItem('English', 'Speech writing outline', 'Due Friday', 0.6),
  ];

  static const _clubs = [
    _ClubItem('Debate Club', 'Main Hall', 'Mock debate at 3:20 PM'),
    _ClubItem('Robotics Club', 'Lab 2', 'Bridge-building trial this week'),
    _ClubItem('Scouts', 'Field', 'Weekend camp briefing'),
  ];

  static const _leaderboard = [
    _LeaderItem('Sharon Wanjiru', '4,820 pts'),
    _LeaderItem('Jayden Kiptoo', '4,610 pts'),
    _LeaderItem('Michelle Akinyi', '4,480 pts'),
    _LeaderItem('Noel Ochieng', '4,310 pts'),
  ];

  static const _projects = [
    _ProjectItem('Water filter prototype', 'Team Orion', 'Review tomorrow'),
    _ProjectItem('Community clean-up report', 'Social Studies', 'Slides 80% ready'),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F7FB),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(14, 14, 14, 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _JuniorHero(student: student),
              const SizedBox(height: 14),
              LayoutBuilder(
                builder: (context, constraints) {
                  final width = (constraints.maxWidth - 12) / 2;
                  return Wrap(
                    spacing: 12,
                    runSpacing: 12,
                    children: _stats.map((item) => SizedBox(width: width, child: _StatCard(item: item))).toList(),
                  );
                },
              ),
              const SizedBox(height: 14),
              _Panel(
                title: 'Assignments Queue',
                subtitle: 'Structured worklist for today and this week',
                child: Column(children: _assignments.map((task) => _TaskCard(task: task)).toList()),
              ),
              const SizedBox(height: 14),
              _Panel(
                title: 'Quiz + Leaderboard',
                subtitle: 'Compete in your subject league and track points',
                child: Column(
                  children: _leaderboard
                      .asMap()
                      .entries
                      .map((entry) => _LeaderRow(rank: entry.key + 1, item: entry.value))
                      .toList(),
                ),
              ),
              const SizedBox(height: 14),
              _Panel(
                title: 'Clubs and Projects',
                subtitle: 'After-class activities and ongoing builds',
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    ..._clubs.map((club) => _ClubCard(item: club)),
                    const SizedBox(height: 6),
                    ..._projects.map((project) => _ProjectCard(item: project)),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              _Panel(
                title: 'Study Rhythm',
                subtitle: 'Keep revision visual and easy to follow',
                child: Column(
                  children: const [
                    _StudyMeter(label: 'Math mastery', value: 0.76, color: Color(0xFF2563EB)),
                    _StudyMeter(label: 'Science revision', value: 0.69, color: Color(0xFF0F9D7A)),
                    _StudyMeter(label: 'Languages progress', value: 0.81, color: Color(0xFFE35D8E)),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _JuniorHero extends StatelessWidget {
  const _JuniorHero({required this.student});

  final Student student;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF163B72), Color(0xFF2F65B8), Color(0xFF8FC8FF)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(student.name, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 22)),
          const SizedBox(height: 4),
          Text(
            '${student.className} • Adm ${student.admissionNumber}',
            style: const TextStyle(color: Color(0xFFDDEBFF), fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 12),
          const Wrap(
            spacing: 10,
            runSpacing: 10,
            children: [
              _HeroChip(label: 'Weekly Rank', value: '#1'),
              _HeroChip(label: 'Project Team', value: 'Orion'),
              _HeroChip(label: 'Club Day', value: 'Today'),
            ],
          ),
        ],
      ),
    );
  }
}

class _Panel extends StatelessWidget {
  const _Panel({
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
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFFD9E3F2)),
        boxShadow: const [
          BoxShadow(color: Color(0x100E1A2A), blurRadius: 14, offset: Offset(0, 5)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w900, fontSize: 16)),
          const SizedBox(height: 4),
          Text(subtitle, style: const TextStyle(color: K1Colors.muted, fontWeight: FontWeight.w600)),
          const SizedBox(height: 14),
          child,
        ],
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  const _StatCard({required this.item});

  final _StatItem item;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFD9E3F2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(color: item.color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(14)),
            child: Icon(item.icon, color: item.color),
          ),
          const SizedBox(height: 14),
          Text(item.label, style: const TextStyle(color: K1Colors.muted, fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text(item.value, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w900, fontSize: 18)),
        ],
      ),
    );
  }
}

class _TaskCard extends StatelessWidget {
  const _TaskCard({required this.task});

  final _TaskItem task;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFD),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFDCE6F6)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(task.subject, style: const TextStyle(color: Color(0xFF2563EB), fontWeight: FontWeight.w900)),
          const SizedBox(height: 4),
          Text(task.title, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w800)),
          const SizedBox(height: 4),
          Text(task.meta, style: const TextStyle(color: K1Colors.muted, fontWeight: FontWeight.w700, fontSize: 12)),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(999),
            child: LinearProgressIndicator(
              value: task.progress,
              minHeight: 8,
              backgroundColor: const Color(0xFFD9E5F8),
              valueColor: const AlwaysStoppedAnimation(Color(0xFF2563EB)),
            ),
          ),
        ],
      ),
    );
  }
}

class _LeaderRow extends StatelessWidget {
  const _LeaderRow({required this.rank, required this.item});

  final int rank;
  final _LeaderItem item;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: rank == 1 ? const Color(0xFFEFF6FF) : const Color(0xFFF8FAFD),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFDCE6F6)),
      ),
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: rank == 1 ? const Color(0xFF2563EB) : const Color(0xFFCBD8EE),
            foregroundColor: Colors.white,
            child: Text('$rank', style: const TextStyle(fontWeight: FontWeight.w900)),
          ),
          const SizedBox(width: 10),
          Expanded(child: Text(item.name, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w800))),
          Text(item.points, style: const TextStyle(color: K1Colors.muted, fontWeight: FontWeight.w800)),
        ],
      ),
    );
  }
}

class _ClubCard extends StatelessWidget {
  const _ClubCard({required this.item});

  final _ClubItem item;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: Container(
        width: 42,
        height: 42,
        decoration: BoxDecoration(color: const Color(0xFFEEF4FF), borderRadius: BorderRadius.circular(14)),
        child: const Icon(Icons.groups_outlined, color: Color(0xFF2563EB)),
      ),
      title: Text(item.title, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w800)),
      subtitle: Text('${item.place} • ${item.meta}', style: const TextStyle(color: K1Colors.muted, fontWeight: FontWeight.w700)),
      trailing: const Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Color(0xFF8CA3C7)),
    );
  }
}

class _ProjectCard extends StatelessWidget {
  const _ProjectCard({required this.item});

  final _ProjectItem item;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(top: 8),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF13243D), Color(0xFF244975)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(item.title, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900)),
          const SizedBox(height: 4),
          Text(item.team, style: const TextStyle(color: Color(0xFFB9CDE8), fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text(item.meta, style: const TextStyle(color: Color(0xFFD8E5F8), fontWeight: FontWeight.w600, fontSize: 12)),
        ],
      ),
    );
  }
}

class _StudyMeter extends StatelessWidget {
  const _StudyMeter({
    required this.label,
    required this.value,
    required this.color,
  });

  final String label;
  final double value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(color: K1Colors.text, fontWeight: FontWeight.w800)),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(999),
            child: LinearProgressIndicator(
              value: value,
              minHeight: 10,
              backgroundColor: const Color(0xFFD9E5F8),
              valueColor: AlwaysStoppedAnimation(color),
            ),
          ),
        ],
      ),
    );
  }
}

class _HeroChip extends StatelessWidget {
  const _HeroChip({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: Colors.white.withValues(alpha: 0.22)),
      ),
      child: Text('$label: $value', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
    );
  }
}

class _StatItem {
  const _StatItem(this.label, this.value, this.icon, this.color);

  final String label;
  final String value;
  final IconData icon;
  final Color color;
}

class _TaskItem {
  const _TaskItem(this.subject, this.title, this.meta, this.progress);

  final String subject;
  final String title;
  final String meta;
  final double progress;
}

class _ClubItem {
  const _ClubItem(this.title, this.place, this.meta);

  final String title;
  final String place;
  final String meta;
}

class _LeaderItem {
  const _LeaderItem(this.name, this.points);

  final String name;
  final String points;
}

class _ProjectItem {
  const _ProjectItem(this.title, this.team, this.meta);

  final String title;
  final String team;
  final String meta;
}
