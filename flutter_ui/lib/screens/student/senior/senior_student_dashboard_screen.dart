import 'package:flutter/material.dart';

import '../../../models/senior_models.dart';
import '../../../services/senior_service.dart';
import '../../../widgets/k1_bottom_nav.dart';
import '../../../widgets/k1_top_bar.dart';

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
        baseUrl: 'http://localhost:8000/api',
        tokenProvider: () async => null,
      );
      final data = await service.getSeniorDashboard(
        widget.student.id?.toString() ?? '',
      );
      if (mounted) setState(() => _data = data);
    } catch (e) {
      if (mounted) setState(() => _error = e.toString());
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
                        _Header(data: data),
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
                              _OverviewTab(data: data),
                              _CoursesTab(data: data),
                              const _ExamsTab(),
                              _ResultsTab(data: data),
                              _TimetableTab(data: data),
                              _CbcTab(data: data),
                              _ProfileTab(data: data),
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
  const _Header({required this.data});
  final SeniorDashboardData data;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(10, 8, 10, 10),
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
          const SizedBox(height: 8),
          Text(
            data.studentName,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w900,
              fontSize: 16,
            ),
          ),
          if (data.program != null) ...[
            const SizedBox(height: 2),
            Text(
              data.program!,
              style: const TextStyle(
                color: Color(0xFFC8D4E8),
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
          if (data.semester != null)
            Text(
              data.semester!,
              style: const TextStyle(
                color: Color(0xFFC8D4E8),
                fontWeight: FontWeight.w600,
                fontSize: 12,
              ),
            ),
        ],
      ),
    );
  }
}

class _OverviewTab extends StatelessWidget {
  const _OverviewTab({required this.data});
  final SeniorDashboardData data;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: data.kpis
              .map(
                (kpi) => _DarkTile(
                  title: kpi.label,
                  value: kpi.value,
                ),
              )
              .toList(),
        ),
        const SizedBox(height: 10),
        _DarkPanel(title: 'Announcements', child: _announcements()),
      ],
    );
  }

  Widget _announcements() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: data.announcements
          .map((a) => Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Text(
                  a,
                  style: const TextStyle(
                    color: Color(0xFFC4D2E6),
                    fontSize: 13,
                  ),
                ),
              ))
          .toList(),
    );
  }
}

class _CoursesTab extends StatelessWidget {
  const _CoursesTab({required this.data});
  final SeniorDashboardData data;

  @override
  Widget build(BuildContext context) {
    return const _DarkPanel(
      title: 'Current Courses',
      child: Column(
        children: [
          ListTile(
            dense: true,
            leading: Icon(Icons.book_outlined, color: Color(0xFF84B8FF)),
            title: Text('Data Structures', style: TextStyle(color: Colors.white)),
            subtitle: Text('3 Credits', style: TextStyle(color: Color(0xFFC4D2E6))),
          ),
          ListTile(
            dense: true,
            leading: Icon(Icons.book_outlined, color: Color(0xFF84B8FF)),
            title: Text('Algorithms', style: TextStyle(color: Colors.white)),
            subtitle: Text('3 Credits', style: TextStyle(color: Color(0xFFC4D2E6))),
          ),
        ],
      ),
    );
  }
}

class _ExamsTab extends StatelessWidget {
  const _ExamsTab();

  @override
  Widget build(BuildContext context) {
    return const _DarkPanel(
      title: 'Upcoming Exams',
      child: Column(
        children: [
          ListTile(
            leading: Icon(Icons.event_note_outlined, color: Color(0xFF84B8FF)),
            title: Text('Data Structures', style: TextStyle(color: Colors.white)),
            subtitle: Text('14 Mar 2026', style: TextStyle(color: Color(0xFFC4D2E6))),
          ),
        ],
      ),
    );
  }
}

class _ResultsTab extends StatelessWidget {
  const _ResultsTab({required this.data});
  final SeniorDashboardData data;

  @override
  Widget build(BuildContext context) {
    return const _DarkPanel(
      title: 'Results',
      child: Text(
        'View transcript for detailed results',
        style: TextStyle(color: Color(0xFFC4D2E6)),
      ),
    );
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
  const _CbcTab({required this.data});
  final SeniorDashboardData data;

  @override
  Widget build(BuildContext context) {
    return const _DarkPanel(
      title: 'CBC Overview',
      child: Text(
        'CBC curriculum data available in parent dashboard',
        style: TextStyle(color: Color(0xFFC4D2E6)),
      ),
    );
  }
}

class _ProfileTab extends StatelessWidget {
  const _ProfileTab({required this.data});
  final SeniorDashboardData data;

  @override
  Widget build(BuildContext context) {
    return const _DarkPanel(
      title: 'Student Profile',
      child: Text(
        'Profile data loaded from backend API',
        style: TextStyle(color: Color(0xFFC4D2E6)),
      ),
    );
  }
}

class _DarkTile extends StatelessWidget {
  const _DarkTile({required this.title, required this.value});
  final String title;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: const Color(0xFF1A2438),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFF2D3E5A)),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: Color(0xFF9EB4D0),
                    fontSize: 12,
                  ),
                ),
                Text(
                  value,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
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