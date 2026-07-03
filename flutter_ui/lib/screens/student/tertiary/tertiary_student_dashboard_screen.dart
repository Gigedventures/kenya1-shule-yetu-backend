import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';

import '../../../data/mock_data.dart';
import '../../../data/mock_users.dart';
import '../../../models/transcript_models.dart';
import '../../../services/transcript_service.dart';
import '../../../widgets/k1_bottom_nav.dart';
import '../../../widgets/k1_top_bar.dart';

class TertiaryStudentDashboardScreen extends StatefulWidget {
  const TertiaryStudentDashboardScreen({
    super.key,
    required this.student,
  });

  final Student student;

  @override
  State<TertiaryStudentDashboardScreen> createState() => _TertiaryStudentDashboardScreenState();
}

class _TertiaryStudentDashboardScreenState extends State<TertiaryStudentDashboardScreen> {
  int _courseSubTab = 0;

  static const _tabs = [
    Tab(text: 'Dashboard'),
    Tab(text: 'Courses'),
    Tab(text: 'Exams'),
    Tab(text: 'Results'),
    Tab(text: 'Timetable'),
    Tab(text: 'AI Tutor'),
    Tab(text: 'Finance'),
  ];

  @override
  Widget build(BuildContext context) {
    final data = MockData.seniorData;
    return DefaultTabController(
      length: _tabs.length,
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
                            tabs: _tabs,
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
                              _DashboardTab(data: data),
                              _CoursesTab(
                                selectedSubTab: _courseSubTab,
                                onSubTabChanged: (value) {
                                  setState(() {
                                    _courseSubTab = value;
                                  });
                                },
                              ),
                              const _ExamsTab(),
                              _ResultsTab(data: data, student: widget.student),
                              _TimetableTab(data: data),
                              const _AiTutorTab(),
                              const _FinanceTab(),
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

  final SeniorData data;
  final Student student;

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
          const K1TopBar(title: 'Shule Yetu ', subtitle: 'Tertiary Student', dark: true, mailBadge: 1),
          const SizedBox(height: 8),
          Text(student.name, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 16)),
          const SizedBox(height: 2),
          Text(data.program, style: const TextStyle(color: Color(0xFFC8D4E8), fontWeight: FontWeight.w600)),
          Text(data.semester, style: const TextStyle(color: Color(0xFFC8D4E8), fontWeight: FontWeight.w600, fontSize: 12)),
          Text('Student No: ${student.admissionNumber}', style: const TextStyle(color: Color(0xFFB5C8E6), fontWeight: FontWeight.w600, fontSize: 12)),
        ],
      ),
    );
  }
}

class _DashboardTab extends StatelessWidget {
  const _DashboardTab({required this.data});

  final SeniorData data;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final tileWidth = (constraints.maxWidth - 8) / 2;
        return ListView(
          padding: const EdgeInsets.only(bottom: 16),
          children: [
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: data.kpis
                  .map(
                    (kpi) => SizedBox(
                      width: tileWidth,
                      child: _DarkTile(title: kpi.label, value: kpi.value, icon: kpi.icon),
                    ),
                  )
                  .toList(),
            ),
            const SizedBox(height: 10),
            _DarkPanel(
              title: 'Today Schedule',
              child: Column(
                children: data.schedule
                    .map(
                      (item) => Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: Row(
                          children: [
                            SizedBox(
                              width: 58,
                              child: Text(item.time, style: const TextStyle(color: Color(0xFF80AAE5), fontWeight: FontWeight.w800)),
                            ),
                            Expanded(
                              child: Text('${item.title} - ${item.location}', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13)),
                            ),
                          ],
                        ),
                      ),
                    )
                    .toList(),
              ),
            ),
            const SizedBox(height: 10),
            _DarkPanel(
              title: 'Campus Updates',
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ...data.updates.map(
                    (update) => Padding(
                      padding: const EdgeInsets.only(bottom: 6),
                      child: Text('- $update', style: const TextStyle(color: Color(0xFFC4D2E6), fontSize: 13)),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10),
            _DarkPanel(
              title: 'Alerts',
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ...data.alerts.map(
                    (alert) => Padding(
                      padding: const EdgeInsets.only(bottom: 6),
                      child: Row(
                        children: [
                          Icon(
                            alert.severity == 'high' ? Icons.warning_amber_rounded : Icons.info_outline,
                            color: alert.severity == 'high' ? const Color(0xFFFF9A7A) : const Color(0xFF84B8FF),
                          ),
                          const SizedBox(width: 6),
                          Expanded(child: Text(alert.title, style: const TextStyle(color: Colors.white))),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        );
      },
    );
  }
}

class _CoursesTab extends StatelessWidget {
  const _CoursesTab({required this.selectedSubTab, required this.onSubTabChanged});

  final int selectedSubTab;
  final ValueChanged<int> onSubTabChanged;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        Container(
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(
            color: const Color(0xFF1A2438),
            borderRadius: BorderRadius.circular(10),
          ),
          child: SegmentedButton<int>(
            showSelectedIcon: false,
            style: const ButtonStyle(
              textStyle: MaterialStatePropertyAll(TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
              foregroundColor: MaterialStatePropertyAll(Color(0xFFC7D5E8)),
            ),
            segments: const [
              ButtonSegment(value: 0, label: Text('Current')),
              ButtonSegment(value: 1, label: Text('Completed')),
              ButtonSegment(value: 2, label: Text('Recommended')),
            ],
            selected: {selectedSubTab},
            onSelectionChanged: (selection) => onSubTabChanged(selection.first),
          ),
        ),
        const SizedBox(height: 10),
        _DarkPanel(
          title: selectedSubTab == 0
              ? 'Current Courses'
              : selectedSubTab == 1
                  ? 'Completed Courses'
                  : 'Recommended Courses',
          child: Column(
            children: const [
              ListTile(
                dense: true,
                leading: Icon(Icons.book_outlined, color: Color(0xFF84B8FF)),
                title: Text('Operating Systems', style: TextStyle(color: Colors.white)),
                subtitle: Text('3 Credits', style: TextStyle(color: Color(0xFFC4D2E6))),
              ),
              ListTile(
                dense: true,
                leading: Icon(Icons.book_outlined, color: Color(0xFF84B8FF)),
                title: Text('Database Systems', style: TextStyle(color: Colors.white)),
                subtitle: Text('3 Credits', style: TextStyle(color: Color(0xFFC4D2E6))),
              ),
              ListTile(
                dense: true,
                leading: Icon(Icons.book_outlined, color: Color(0xFF84B8FF)),
                title: Text('Computer Networks', style: TextStyle(color: Colors.white)),
                subtitle: Text('2 Credits', style: TextStyle(color: Color(0xFFC4D2E6))),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ExamsTab extends StatelessWidget {
  const _ExamsTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: const [
        _DarkPanel(
          title: 'Upcoming Exams',
          child: Column(
            children: [
              ListTile(
                leading: Icon(Icons.event_note_outlined, color: Color(0xFF84B8FF)),
                title: Text('Database Systems', style: TextStyle(color: Colors.white)),
                subtitle: Text('14 Mar 2026 - 09:00 AM', style: TextStyle(color: Color(0xFFC4D2E6))),
              ),
              ListTile(
                leading: Icon(Icons.event_note_outlined, color: Color(0xFF84B8FF)),
                title: Text('Discrete Math', style: TextStyle(color: Colors.white)),
                subtitle: Text('17 Mar 2026 - 11:00 AM', style: TextStyle(color: Color(0xFFC4D2E6))),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ResultsTab extends StatelessWidget {
  const _ResultsTab({required this.data, required this.student});

  final SeniorData data;
  final Student student;

  @override
  Widget build(BuildContext context) {
    final transcriptService = TranscriptService(
      baseUrl: 'http://localhost:8000/api',
      tokenProvider: () async => null,
    );

    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        _DarkPanel(
          title: 'GPA Trend',
          child: SizedBox(height: 200, child: _ResultLine(values: data.resultTrend)),
        ),
        const SizedBox(height: 10),
        FilledButton(
          onPressed: () async {
            try {
              final transcript = await transcriptService.getStudentTranscript(
                student.id.toString(),
              );
              if (!context.mounted) return;
              showDialog(
                context: context,
                builder: (ctx) => AlertDialog(
                  backgroundColor: const Color(0xFF1A2438),
                  title: const Text('Academic Transcript', style: TextStyle(color: Colors.white)),
                  content: SizedBox(
                    width: double.maxFinite,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Student: ${transcript.student.name ?? 'N/A'}',
                            style: const TextStyle(color: Colors.white)),
                        const SizedBox(height: 8),
                        Text('Admission: ${transcript.student.admissionNo ?? 'N/A'}',
                            style: TextStyle(color: Color(0xFFC4D2E6))),
                        const SizedBox(height: 12),
                        Text('Cumulative GPA: ${transcript.cumulativeAverage.toStringAsFixed(2)}',
                            style: const TextStyle(color: Color(0xFF5EA3FF), fontWeight: FontWeight.w800)),
                        const SizedBox(height: 8),
                        Text('Total Terms: ${transcript.totalTerms}',
                            style: const TextStyle(color: Color(0xFFC4D2E6))),
                      ],
                    ),
                  ),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.of(ctx).pop(),
                      child: const Text('Close'),
                    ),
                  ],
                ),
              );
            } catch (e) {
              if (!context.mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
              );
            }
          },
          style: FilledButton.styleFrom(
            backgroundColor: const Color(0xFF2A6CC4),
            padding: const EdgeInsets.symmetric(vertical: 14),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
          child: const Text('View Transcript', style: TextStyle(fontWeight: FontWeight.w800)),
        ),
      ],
    );
  }
}

class _TimetableTab extends StatelessWidget {
  const _TimetableTab({required this.data});

  final SeniorData data;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        _DarkPanel(
          title: 'Timetable',
          child: Column(
            children: data.schedule
                .map(
                  (item) => ListTile(
                    dense: true,
                    leading: const Icon(Icons.access_time, color: Color(0xFF84B8FF)),
                    title: Text(item.title, style: const TextStyle(color: Colors.white)),
                    subtitle: Text('${item.time} - ${item.location}', style: const TextStyle(color: Color(0xFFC4D2E6))),
                  ),
                )
                .toList(),
          ),
        ),
      ],
    );
  }
}

class _AiTutorTab extends StatelessWidget {
  const _AiTutorTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        const _DarkPanel(
          title: 'AI Tutor',
          child: ListTile(
            leading: Icon(Icons.smart_toy_outlined, color: Color(0xFF84B8FF)),
            title: Text('Start AI Study Session', style: TextStyle(color: Colors.white)),
            subtitle: Text('Topic-based explanations and revision help', style: TextStyle(color: Color(0xFFC4D2E6))),
          ),
        ),
        const SizedBox(height: 10),
        _DarkPanel(
          title: 'Study Plan Generator',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Generate a 14-day exam plan from your courses.', style: TextStyle(color: Color(0xFFC4D2E6))),
              const SizedBox(height: 8),
              FilledButton(
                onPressed: () {
                  // TODO(backend): connect AI study plan generator service.
                },
                style: FilledButton.styleFrom(backgroundColor: const Color(0xFF2A6CC4)),
                child: const Text('Generate Plan'),
              ),
            ],
          ),
        ),
        const SizedBox(height: 10),
        const _DarkPanel(
          title: 'Ask a Question',
          child: TextField(
            decoration: InputDecoration(
              hintText: 'Mock prompt: Explain normalization in DBMS...',
              hintStyle: TextStyle(color: Color(0xFF93A7C5)),
              enabledBorder: OutlineInputBorder(
                borderSide: BorderSide(color: Color(0xFF3A4E6D)),
              ),
              focusedBorder: OutlineInputBorder(
                borderSide: BorderSide(color: Color(0xFF5B96E4)),
              ),
            ),
            style: TextStyle(color: Colors.white),
          ),
        ),
      ],
    );
  }
}

class _FinanceTab extends StatelessWidget {
  const _FinanceTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: const [
        _DarkPanel(
          title: 'Finance',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Outstanding balance: KES 12,000', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
              SizedBox(height: 4),
              Text('Next deadline: 20 Mar 2026', style: TextStyle(color: Color(0xFFC4D2E6))),
              SizedBox(height: 8),
              Text('TODO(backend): wire invoices, payments, and receipts API.', style: TextStyle(color: Color(0xFF9EB4D0))),
            ],
          ),
        ),
      ],
    );
  }
}

class _DarkTile extends StatelessWidget {
  const _DarkTile({required this.title, required this.value, required this.icon});

  final String title;
  final String value;
  final IconData icon;

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
          Icon(icon, color: const Color(0xFF84B8FF), size: 20),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(color: Color(0xFF9EB4D0), fontSize: 12)),
                Text(value, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900)),
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
      decoration: BoxDecoration(
        color: const Color(0xFF1A2438),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFF2D3E5A)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900)),
          const SizedBox(height: 8),
          child,
        ],
      ),
    );
  }
}

class _ResultLine extends StatelessWidget {
  const _ResultLine({required this.values});

  final List<double> values;

  @override
  Widget build(BuildContext context) {
    return LineChart(
      LineChartData(
        minY: 2.8,
        maxY: 4.0,
        gridData: FlGridData(
          horizontalInterval: 0.2,
          drawVerticalLine: false,
          getDrawingHorizontalLine: (_) => const FlLine(color: Color(0xFF2C3E59), strokeWidth: 1),
        ),
        borderData: FlBorderData(show: false),
        lineTouchData: const LineTouchData(enabled: true),
        titlesData: FlTitlesData(
          rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          bottomTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              getTitlesWidget: (value, meta) => Text(
                'S${value.toInt() + 1}',
                style: const TextStyle(color: Color(0xFF9EB4D0), fontSize: 10),
              ),
              reservedSize: 18,
            ),
          ),
          leftTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              interval: 0.4,
              reservedSize: 30,
              getTitlesWidget: (value, meta) => Text(
                value.toStringAsFixed(1),
                style: const TextStyle(color: Color(0xFF9EB4D0), fontSize: 10),
              ),
            ),
          ),
        ),
        lineBarsData: [
          LineChartBarData(
            spots: [
              for (var i = 0; i < values.length; i++) FlSpot(i.toDouble(), values[i]),
            ],
            isCurved: true,
            barWidth: 3,
            color: const Color(0xFF5EA3FF),
            dotData: const FlDotData(show: true),
            belowBarData: BarAreaData(show: true, color: const Color(0x225EA3FF)),
          ),
        ],
      ),
    );
  }
}
