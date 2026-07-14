import 'package:flutter/material.dart';

import '../../../data/mock_users.dart';
import '../../../models/senior_models.dart';
import '../../../services/senior_service.dart';
import '../../../widgets/k1_bottom_nav.dart';
import '../../../widgets/k1_top_bar.dart';

SeniorDashboardData _mockSeniorDashboard() {
  return const SeniorDashboardData(
    studentName: 'John Otieno',
    program: 'Form 3 | Alliance High School',
    semester: 'Term 2, 2026',
    kpis: [
      SeniorKpi(label: 'Attendance', value: '94.4%', icon: 'fact_check'),
      SeniorKpi(label: 'Mock Score', value: '81%', icon: 'school'),
      SeniorKpi(label: 'Assignments', value: '3 pending', icon: 'assignment'),
      SeniorKpi(label: 'Career', value: 'Engineering', icon: 'insights'),
    ],
    schedule: [
      SeniorScheduleBlock(time: '07:50', title: 'Mathematics', location: 'Room 4B'),
      SeniorScheduleBlock(time: '09:40', title: 'Physics', location: 'Science Lab'),
      SeniorScheduleBlock(time: '11:20', title: 'Chemistry', location: 'Chem Lab'),
      SeniorScheduleBlock(time: '14:00', title: 'Geography', location: 'Hall 2'),
    ],
    announcements: [
      'KCSE prep classes start next Monday.',
      'Career day registration closes Friday.',
      'Physics practical materials fee due by end of term.',
    ],
    alerts: [
      '2 assignments due this week',
      'Mock exam results published',
    ],
  );
}

class SeniorStudentDashboardScreen extends StatefulWidget {
  const SeniorStudentDashboardScreen({
    super.key,
    required this.student,
  });

  final dynamic student;

  @override
  State<SeniorStudentDashboardScreen> createState() =>
      _SeniorStudentDashboardScreenState();
}

class _SeniorStudentDashboardScreenState
    extends State<SeniorStudentDashboardScreen> {
  SeniorDashboardData? _data;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    try {
      final service = SeniorService(
        baseUrl: const String.fromEnvironment('API_BASE_URL', defaultValue: 'http://localhost:8000/api'),
        tokenProvider: () async => null,
      );
      final data = await service.getSeniorDashboard(
        widget.student.id?.toString() ?? '',
      );
      if (mounted) setState(() => _data = data);
    } catch (e) {
      if (mounted) {
        setState(() => _data = _mockSeniorDashboard());
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return Scaffold(
        backgroundColor: const Color(0xFF0E1420),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_error != null || _data == null) {
      return Scaffold(
        backgroundColor: const Color(0xFF0E1420),
        body: Center(
          child: Text(
            _error ?? 'No data',
            style: const TextStyle(color: Color(0xFFFF6B6B)),
          ),
        ),
      );
    }

    final data = _data!;
    return DefaultTabController(
      length: 7,
      child: Scaffold(
        backgroundColor: const Color(0xFF0E1420),
        body: SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) {
              final sidePadding = constraints.maxWidth > 520 ? 18.0 : 12.0;
              return Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 460),
                  child: Padding(
                    padding: EdgeInsets.fromLTRB(sidePadding, 10, sidePadding, 0),
                    child: Column(
                      children: [
                        _Header(data: data, student: widget.student),
                        const SizedBox(height: 10),
                        Container(
                          decoration: BoxDecoration(
                            color: const Color(0xFF1A2438),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const TabBar(
                            tabs: [
                              Tab(text: 'Overview'),
                              Tab(text: 'Courses'),
                              Tab(text: 'Exams'),
                              Tab(text: 'Results'),
                              Tab(text: 'Timetable'),
                              Tab(text: 'CBC'),
                              Tab(text: 'Profile'),
                            ],
                            isScrollable: true,
                            labelColor: Colors.white,
                            unselectedLabelColor: Color(0xFF91A4C0),
                            indicatorColor: Color(0xFF61A4FF),
                            dividerColor: Colors.transparent,
                          ),
                        ),
                        const SizedBox(height: 10),
                        Expanded(
                          child: TabBarView(
                            children: [
                              _OverviewTab(data: data, student: widget.student),
                              _CoursesTab(student: widget.student),
                              _ExamsTab(student: widget.student),
                              _ResultsTab(student: widget.student),
                              _TimetableTab(data: data),
                              _CbcTab(student: widget.student),
                              _ProfileTab(student: widget.student, data: data),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
        bottomNavigationBar: const K1BottomNav(index: 1),
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.data, required this.student});
  final SeniorDashboardData data;
  final dynamic student;

  @override
  Widget build(BuildContext context) {
    final stu = student is Student ? student as Student : null;
    return Container(
      padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        gradient: const LinearGradient(
          colors: [Color(0xFF13233E), Color(0xFF254977)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const K1TopBar(
            title: 'Shule Yetu ',
            subtitle: 'Senior Student',
            dark: true,
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              CircleAvatar(
                radius: 24,
                backgroundColor: Colors.white24,
                backgroundImage: stu?.avatarUrl != null ? NetworkImage(stu!.avatarUrl) : null,
                child: stu?.avatarUrl == null ? const Icon(Icons.person, color: Colors.white) : null,
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      data.studentName,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 16,
                      ),
                    ),
                    if (data.program != null)
                      Text(
                        data.program!,
                        style: const TextStyle(
                          color: Color(0xFFC8D4E8),
                          fontWeight: FontWeight.w600,
                          fontSize: 12,
                        ),
                      ),
                    if (data.semester != null)
                      Text(
                        data.semester!,
                        style: const TextStyle(
                          color: Color(0xFFC8D4E8),
                          fontWeight: FontWeight.w600,
                          fontSize: 11,
                        ),
                      ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _OverviewTab extends StatelessWidget {
  const _OverviewTab({required this.data, required this.student});
  final SeniorDashboardData data;
  final dynamic student;

  @override
  Widget build(BuildContext context) {
    final stu = student is Student ? student as Student : null;
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: data.kpis
              .map(
                (kpi) => SizedBox(
                  width: (MediaQuery.of(context).size.width > 460 ? 460 : MediaQuery.of(context).size.width - 40) / 2 - 4,
                  child: _DarkTile(
                    title: kpi.label,
                    value: kpi.value,
                    icon: _iconFor(kpi.icon),
                  ),
                ),
              )
              .toList(),
        ),
        const SizedBox(height: 10),
        _DarkPanel(
          title: 'Subjects',
          child: Wrap(
            spacing: 8,
            runSpacing: 8,
            children: (stu?.subjects ?? ['Mathematics', 'English', 'Physics', 'Chemistry', 'Biology'])
                .map((s) => _SubjectChip(label: s))
                .toList(),
          ),
        ),
        const SizedBox(height: 10),
        _DarkPanel(title: 'Announcements', child: _announcements()),
        const SizedBox(height: 10),
        _DarkPanel(
          title: 'Quick Actions',
          child: Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              _ActionChip(icon: Icons.assignment_outlined, label: 'Assignments'),
              _ActionChip(icon: Icons.fact_check_outlined, label: 'Attendance'),
              _ActionChip(icon: Icons.school_outlined, label: 'Revision Papers'),
              _ActionChip(icon: Icons.chat_outlined, label: 'Messages'),
            ],
          ),
        ),
      ],
    );
  }

  IconData _iconFor(String icon) {
    switch (icon) {
      case 'fact_check':
        return Icons.fact_check_outlined;
      case 'school':
        return Icons.school_outlined;
      case 'assignment':
        return Icons.assignment_outlined;
      case 'insights':
        return Icons.insights_outlined;
      default:
        return Icons.analytics_outlined;
    }
  }

  Widget _announcements() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: data.announcements
          .asMap()
          .entries
          .map((e) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      margin: const EdgeInsets.only(top: 4),
                      width: 6,
                      height: 6,
                      decoration: const BoxDecoration(color: Color(0xFF84B8FF), shape: BoxShape.circle),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        e.value,
                        style: const TextStyle(
                          color: Color(0xFFC4D2E6),
                          fontSize: 13,
                        ),
                      ),
                    ),
                  ],
                ),
              ))
          .toList(),
    );
  }
}

class _CoursesTab extends StatelessWidget {
  const _CoursesTab({required this.student});
  final dynamic student;

  @override
  Widget build(BuildContext context) {
    final stu = student is Student ? student as Student : null;
    final subjects = stu?.subjects ?? ['Mathematics', 'English', 'Physics', 'Chemistry', 'Biology'];
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        _DarkPanel(
          title: 'Current Courses',
          child: Column(
            children: subjects
                .map((s) => ListTile(
                      dense: true,
                      leading: const Icon(Icons.book_outlined, color: Color(0xFF84B8FF)),
                      title: Text(s, style: const TextStyle(color: Colors.white)),
                      subtitle: Text('Teacher: ${stu?.teacher ?? "TBD"}', style: const TextStyle(color: Color(0xFFC4D2E6))),
                      trailing: const Icon(Icons.chevron_right, color: Color(0xFF84B8FF)),
                    ))
                .toList(),
          ),
        ),
      ],
    );
  }
}

class _ExamsTab extends StatelessWidget {
  const _ExamsTab({required this.student});
  final dynamic student;

  @override
  Widget build(BuildContext context) {
    final stu = student is Student ? student as Student : null;
    final subjects = stu?.subjects ?? ['Mathematics', 'English', 'Physics', 'Chemistry', 'Biology'];
    final exams = [
      ('${subjects.length > 0 ? subjects[0] : "Mathematics"} Paper 1', '14 Mar 2026 - 09:00 AM', 'Main Hall'),
      ('${subjects.length > 1 ? subjects[1] : "English"} Paper 2', '17 Mar 2026 - 11:00 AM', 'Hall 2'),
      ('${subjects.length > 2 ? subjects[2] : "Physics"} Practical', '20 Mar 2026 - 02:00 PM', 'Science Lab'),
    ];
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        _DarkPanel(
          title: 'Upcoming Exams',
          child: Column(
            children: exams
                .map((e) => ListTile(
                      dense: true,
                      leading: const Icon(Icons.event_note_outlined, color: Color(0xFF84B8FF)),
                      title: Text(e.$1, style: const TextStyle(color: Colors.white)),
                      subtitle: Text('${e.$2} • ${e.$3}', style: const TextStyle(color: Color(0xFFC4D2E6))),
                    ))
                .toList(),
          ),
        ),
      ],
    );
  }
}

class _ResultsTab extends StatelessWidget {
  const _ResultsTab({required this.student});
  final dynamic student;

  @override
  Widget build(BuildContext context) {
    final stu = student is Student ? student as Student : null;
    final scores = stu?.examScores ?? {'Mathematics': 76.0, 'English': 74.0, 'Biology': 80.0};
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        _DarkPanel(
          title: 'Latest Results',
          child: Column(
            children: scores.entries
                .map((e) => ListTile(
                      dense: true,
                      leading: const Icon(Icons.grade_outlined, color: Color(0xFF84B8FF)),
                      title: Text(e.key, style: const TextStyle(color: Colors.white)),
                      trailing: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: _gradeColor(e.value).withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          '${e.value.round()}%',
                          style: TextStyle(color: _gradeColor(e.value), fontWeight: FontWeight.w900),
                        ),
                      ),
                    ))
                .toList(),
          ),
        ),
      ],
    );
  }

  Color _gradeColor(double score) {
    if (score >= 80) return const Color(0xFF4ADE80);
    if (score >= 60) return const Color(0xFFFACC15);
    return const Color(0xFFF87171);
  }
}

class _TimetableTab extends StatelessWidget {
  const _TimetableTab({required this.data});
  final SeniorDashboardData data;

  @override
  Widget build(BuildContext context) {
    return _DarkPanel(
      title: 'Schedule',
      child: Column(
        children: data.schedule
            .map((s) => ListTile(
                  dense: true,
                  leading: const Icon(Icons.access_time, color: Color(0xFF84B8FF)),
                  title: Text(
                    s.title,
                    style: const TextStyle(color: Colors.white),
                  ),
                  subtitle: Text(
                    '${s.time} - ${s.location ?? ''}',
                    style: const TextStyle(color: Color(0xFFC4D2E6)),
                  ),
                ))
            .toList(),
      ),
    );
  }
}

class _CbcTab extends StatelessWidget {
  const _CbcTab({required this.student});
  final dynamic student;

  @override
  Widget build(BuildContext context) {
    final stu = student is Student ? student as Student : null;
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        _DarkPanel(
          title: 'CBC Overview',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Core competencies and values tracking for ${stu?.name ?? "student"}.',
                style: const TextStyle(color: Color(0xFFC4D2E6), fontSize: 13),
              ),
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  _CompetencyChip(label: 'Communication'),
                  _CompetencyChip(label: 'Critical Thinking'),
                  _CompetencyChip(label: 'Creativity'),
                  _CompetencyChip(label: 'Digital Literacy'),
                  _CompetencyChip(label: 'Citizenship'),
                  _CompetencyChip(label: 'Self-Efficacy'),
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ProfileTab extends StatelessWidget {
  const _ProfileTab({required this.student, required this.data});
  final dynamic student;
  final SeniorDashboardData data;

  @override
  Widget build(BuildContext context) {
    final stu = student is Student ? student as Student : null;
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        _DarkPanel(
          title: 'Student Profile',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _ProfileRow(label: 'Name', value: stu?.name ?? data.studentName),
              _ProfileRow(label: 'Admission No', value: stu?.admissionNumber ?? 'N/A'),
              _ProfileRow(label: 'Class', value: stu?.className ?? 'N/A'),
              _ProfileRow(label: 'School', value: stu?.school ?? 'N/A'),
              _ProfileRow(label: 'Career Interest', value: stu?.careerInterest ?? 'Not set'),
              _ProfileRow(label: 'Attendance', value: '${stu?.attendance ?? 94}%'),
              if (stu?.mockExamScore != null)
                _ProfileRow(label: 'Mock Exam', value: '${stu!.mockExamScore!.round()}%'),
            ],
          ),
        ),
      ],
    );
  }
}

class _DarkTile extends StatelessWidget {
  const _DarkTile({required this.title, required this.value, this.icon});
  final String title;
  final String value;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(10, 8, 10, 8),
      decoration: BoxDecoration(
        color: const Color(0xFF1A2438),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFF2D3E5A)),
      ),
      child: Row(
        children: [
          if (icon != null) ...[
            Icon(icon, color: const Color(0xFF84B8FF), size: 18),
            const SizedBox(width: 8),
          ],
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: Color(0xFF9EB4D0),
                    fontSize: 11,
                  ),
                ),
                Text(
                  value,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                    fontSize: 13,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SubjectChip extends StatelessWidget {
  const _SubjectChip({required this.label});
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: const Color(0xFF2D3E5A),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(label, style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600)),
    );
  }
}

class _ActionChip extends StatelessWidget {
  const _ActionChip({required this.icon, required this.label});
  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFF2D3E5A),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: const Color(0xFF84B8FF), size: 16),
          const SizedBox(width: 6),
          Text(label, style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

class _CompetencyChip extends StatelessWidget {
  const _CompetencyChip({required this.label});
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: const Color(0xFF13233E),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: const Color(0xFF2D3E5A)),
      ),
      child: Text(label, style: const TextStyle(color: Color(0xFFC4D2E6), fontSize: 11, fontWeight: FontWeight.w600)),
    );
  }
}

class _ProfileRow extends StatelessWidget {
  const _ProfileRow({required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        children: [
          Expanded(
            child: Text(
              label,
              style: const TextStyle(color: Color(0xFF9EB4D0), fontSize: 12),
            ),
          ),
          Text(
            value,
            style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700),
          ),
        ],
      ),
    );
  }
}

class _DarkPanel extends StatelessWidget {
  const _DarkPanel({required this.title, required this.child});
  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: const Color(0xFF1A2438),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFF2D3E5A)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 8),
          child,
        ],
      ),
    );
  }
}