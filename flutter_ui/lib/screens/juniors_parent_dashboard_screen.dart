import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

import '../data/mock_data.dart';
import '../widgets/k1_bottom_nav.dart';
import '../widgets/k1_top_bar.dart';

class JuniorsParentDashboardScreen extends StatefulWidget {
  const JuniorsParentDashboardScreen({super.key});

  @override
  State<JuniorsParentDashboardScreen> createState() => _JuniorsParentDashboardScreenState();
}

class _JuniorsParentDashboardScreenState extends State<JuniorsParentDashboardScreen> {
  CbcLayer _selectedLayer = CbcLayer.grade1To6;
  int _learningSubTab = 0;

  static const _tabs = [
    Tab(text: 'Overview'),
    Tab(text: 'Learning'),
    Tab(text: 'Homework'),
    Tab(text: 'Attendance'),
    Tab(text: 'Transport'),
    Tab(text: 'Fees'),
    Tab(text: 'Chat'),
  ];

  @override
  Widget build(BuildContext context) {
    final layerData = MockData.juniorLayerData[_selectedLayer]!;
    return DefaultTabController(
      length: _tabs.length,
      child: Scaffold(
        backgroundColor: const Color(0xFFEAF0F8),
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
                        _Header(
                          layerData: layerData,
                          selectedLayer: _selectedLayer,
                          onLayerChanged: (layer) {
                            if (layer != null) {
                              setState(() {
                                _selectedLayer = layer;
                              });
                            }
                          },
                        ),
                        const SizedBox(height: 10),
                        Container(
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const TabBar(
                            tabs: _tabs,
                            isScrollable: true,
                            labelColor: Color(0xFF123A6A),
                            unselectedLabelColor: Color(0xFF5B708E),
                            indicatorColor: Color(0xFF1D5EB3),
                            dividerColor: Colors.transparent,
                          ),
                        ),
                        const SizedBox(height: 10),
                        Expanded(
                          child: TabBarView(
                            children: [
                              _OverviewTab(layerData: layerData),
                              _LearningTab(
                                layerData: layerData,
                                selectedSubTab: _learningSubTab,
                                onSubTabChanged: (value) {
                                  setState(() {
                                    _learningSubTab = value;
                                  });
                                },
                              ),
                              const _HomeworkTab(),
                              _AttendanceTab(layerData: layerData),
                              const _TransportTab(),
                              const _FeesTab(),
                              const _ChatTab(),
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
        bottomNavigationBar: const K1BottomNav(index: 0),
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({
    required this.layerData,
    required this.selectedLayer,
    required this.onLayerChanged,
  });

  final JuniorLayerData layerData;
  final CbcLayer selectedLayer;
  final ValueChanged<CbcLayer?> onLayerChanged;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(10, 8, 10, 12),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0A4FA7), Color(0xFF3E83D8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const K1TopBar(title: 'Shule Yetu ', subtitle: 'Parent', dark: true),
          const SizedBox(height: 8),
          Text(
            '${layerData.learnerName} - ${layerData.gradeLabel}',
            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 13),
          ),
          const SizedBox(height: 10),
          CupertinoSlidingSegmentedControl<CbcLayer>(
            backgroundColor: const Color(0xFF6EA0DA),
            thumbColor: Colors.white,
            groupValue: selectedLayer,
            onValueChanged: onLayerChanged,
            children: const {
              CbcLayer.pp1Pp2: Padding(
                padding: EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                child: Text('PP1-PP2', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
              ),
              CbcLayer.grade1To6: Padding(
                padding: EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                child: Text('Grade 1-6', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
              ),
              CbcLayer.grade7To9: Padding(
                padding: EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                child: Text('Grade 7-9', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
              ),
            },
          ),
        ],
      ),
    );
  }
}

class _OverviewTab extends StatelessWidget {
  const _OverviewTab({required this.layerData});

  final JuniorLayerData layerData;

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
              children: layerData.metrics
                  .map(
                    (metric) => SizedBox(
                      width: tileWidth,
                      child: _CompactTile(
                        title: metric.label,
                        value: metric.value,
                        icon: metric.icon,
                      ),
                    ),
                  )
                  .toList(),
            ),
            const SizedBox(height: 10),
            _Panel(
              title: 'Alerts',
              child: ExpansionTile(
                tilePadding: EdgeInsets.zero,
                childrenPadding: EdgeInsets.zero,
                initiallyExpanded: true,
                title: Text('${layerData.alerts.length} active alerts', style: const TextStyle(fontWeight: FontWeight.w700)),
                children: layerData.alerts
                    .map(
                      (item) => ListTile(
                        dense: true,
                        contentPadding: EdgeInsets.zero,
                        title: Text(item.title, style: const TextStyle(fontSize: 13)),
                        leading: Icon(
                          item.severity == 'high' ? Icons.priority_high : Icons.notifications_active_outlined,
                          color: item.severity == 'high' ? const Color(0xFFD84315) : const Color(0xFF2E6BB8),
                        ),
                      ),
                    )
                    .toList(),
              ),
            ),
            const SizedBox(height: 10),
            _Panel(
              title: 'Today Schedule',
              child: Column(
                children: layerData.todaySchedule
                    .map(
                      (item) => Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: Row(
                          children: [
                            SizedBox(
                              width: 54,
                              child: Text(item.time, style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF143965))),
                            ),
                            Expanded(
                              child: Text('${item.title} - ${item.location}', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                            ),
                          ],
                        ),
                      ),
                    )
                    .toList(),
              ),
            ),
            const SizedBox(height: 10),
            _Panel(
              title: 'Today Snapshot',
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(height: 72, child: _SparkLine(values: layerData.sparkline)),
                  const SizedBox(height: 8),
                  const Text('Daily engagement trend', style: TextStyle(color: Color(0xFF54708F), fontSize: 12)),
                ],
              ),
            ),
          ],
        );
      },
    );
  }
}

class _LearningTab extends StatelessWidget {
  const _LearningTab({
    required this.layerData,
    required this.selectedSubTab,
    required this.onSubTabChanged,
  });

  final JuniorLayerData layerData;
  final int selectedSubTab;
  final ValueChanged<int> onSubTabChanged;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        SizedBox(
          height: 126,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: layerData.activities.length,
            separatorBuilder: (_, __) => const SizedBox(width: 8),
            itemBuilder: (context, index) {
              final activity = layerData.activities[index];
              return Container(
                width: 170,
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFD8E3F1)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(activity.icon, color: const Color(0xFF1D5EB3)),
                    const SizedBox(height: 8),
                    Text(activity.title, style: const TextStyle(fontWeight: FontWeight.w800)),
                    const SizedBox(height: 4),
                    Text(activity.subtitle, style: const TextStyle(color: Color(0xFF5E738F), fontSize: 12)),
                  ],
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 10),
        Container(
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
          ),
          child: SegmentedButton<int>(
            showSelectedIcon: false,
            style: const ButtonStyle(
              textStyle: MaterialStatePropertyAll(TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
            ),
            segments: const [
              ButtonSegment(value: 0, label: Text('Daily Practice')),
              ButtonSegment(value: 1, label: Text('Streaks/Badges')),
              ButtonSegment(value: 2, label: Text('Strand Chart')),
            ],
            selected: {selectedSubTab},
            onSelectionChanged: (selection) => onSubTabChanged(selection.first),
          ),
        ),
        const SizedBox(height: 10),
        if (selectedSubTab == 0)
          _Panel(
            title: 'Subject Progress',
            child: Column(
              children: layerData.progress
                  .map(
                    (item) => Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(child: Text(item.subject, style: const TextStyle(fontWeight: FontWeight.w700))),
                              Text('${(item.value * 100).round()}%', style: const TextStyle(fontWeight: FontWeight.w800)),
                            ],
                          ),
                          const SizedBox(height: 6),
                          ClipRRect(
                            borderRadius: BorderRadius.circular(8),
                            child: LinearProgressIndicator(
                              minHeight: 8,
                              value: item.value,
                              backgroundColor: const Color(0xFFE2EAF5),
                              color: const Color(0xFF2D7BD0),
                            ),
                          ),
                        ],
                      ),
                    ),
                  )
                  .toList(),
            ),
          ),
        if (selectedSubTab == 1)
          Column(
            children: const [
              _CompactTile(title: 'Current Streak', value: '9 days', icon: Icons.local_fire_department_outlined),
              SizedBox(height: 8),
              _CompactTile(title: 'Badges Earned', value: '14', icon: Icons.verified_outlined),
            ],
          ),
        if (selectedSubTab == 2)
          _Panel(
            title: 'Progress by Strand',
            child: SizedBox(height: 180, child: _StrandBar(values: layerData.strandChart)),
          ),
      ],
    );
  }
}

class _HomeworkTab extends StatelessWidget {
  const _HomeworkTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: const [
        _Panel(
          title: 'Due Today',
          child: Column(
            children: [
              ListTile(leading: Icon(Icons.description_outlined), title: Text('Math worksheet: fractions'), subtitle: Text('Due 6:00 PM')),
              ListTile(leading: Icon(Icons.description_outlined), title: Text('English reading log'), subtitle: Text('Due 7:30 PM')),
            ],
          ),
        ),
        SizedBox(height: 10),
        _Panel(
          title: 'Teacher Notes',
          child: Text('Practice 20 minutes on reading coach before submission.'),
        ),
      ],
    );
  }
}

class _AttendanceTab extends StatelessWidget {
  const _AttendanceTab({required this.layerData});

  final JuniorLayerData layerData;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.only(bottom: 16),
      children: [
        const _CompactTile(title: 'This Term', value: '96.4%', icon: Icons.fact_check_outlined),
        const SizedBox(height: 10),
        _Panel(
          title: 'Weekly Attendance Trend',
          child: SizedBox(height: 180, child: _SparkLine(values: layerData.sparkline)),
        ),
      ],
    );
  }
}

class _TransportTab extends StatelessWidget {
  const _TransportTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: EdgeInsets.only(bottom: 16),
      children: [
        _Panel(
          title: 'Bus Status',
          child: ListTile(
            leading: Icon(Icons.directions_bus_outlined, color: Color(0xFF1D5EB3)),
            title: Text('Bus KDC 245W is en route'),
            subtitle: Text('Estimated arrival: 4:25 PM'),
          ),
        ),
      ],
    );
  }
}

class _FeesTab extends StatelessWidget {
  const _FeesTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: EdgeInsets.only(bottom: 16),
      children: [
        _Panel(
          title: 'Finance',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Outstanding: KES 6,500', style: TextStyle(fontWeight: FontWeight.w800)),
              SizedBox(height: 4),
              Text('Next due date: 15 Mar 2026'),
              SizedBox(height: 8),
              Text('TODO(backend): connect fee statements and payment endpoints.'),
            ],
          ),
        ),
      ],
    );
  }
}

class _ChatTab extends StatelessWidget {
  const _ChatTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: EdgeInsets.only(bottom: 16),
      children: [
        _Panel(
          title: 'Chat',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              ListTile(leading: Icon(Icons.person_outline), title: Text('Class Teacher'), subtitle: Text('Reply pending')),
              ListTile(leading: Icon(Icons.support_agent_outlined), title: Text('Transport Office'), subtitle: Text('Online now')),
              Text('TODO(backend): plug real parent-teacher chat API.'),
            ],
          ),
        ),
      ],
    );
  }
}

class _CompactTile extends StatelessWidget {
  const _CompactTile({required this.title, required this.value, required this.icon});

  final String title;
  final String value;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFD8E3F1)),
      ),
      child: Row(
        children: [
          Icon(icon, color: const Color(0xFF1D5EB3), size: 20),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(color: Color(0xFF637A98), fontSize: 12)),
                Text(value, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF153A66))),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Panel extends StatelessWidget {
  const _Panel({required this.title, required this.child});

  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFD8E3F1)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF153A66))),
          const SizedBox(height: 8),
          child,
        ],
      ),
    );
  }
}

class _SparkLine extends StatelessWidget {
  const _SparkLine({required this.values});

  final List<double> values;

  @override
  Widget build(BuildContext context) {
    return LineChart(
      LineChartData(
        gridData: const FlGridData(show: false),
        borderData: FlBorderData(show: false),
        titlesData: const FlTitlesData(show: false),
        lineTouchData: const LineTouchData(enabled: false),
        lineBarsData: [
          LineChartBarData(
            spots: [
              for (var i = 0; i < values.length; i++) FlSpot(i.toDouble(), values[i]),
            ],
            isCurved: true,
            barWidth: 2.8,
            color: const Color(0xFF1D5EB3),
            dotData: const FlDotData(show: false),
            belowBarData: BarAreaData(show: true, color: const Color(0x331D5EB3)),
          ),
        ],
      ),
    );
  }
}

class _StrandBar extends StatelessWidget {
  const _StrandBar({required this.values});

  final List<double> values;

  @override
  Widget build(BuildContext context) {
    return BarChart(
      BarChartData(
        gridData: const FlGridData(show: false),
        borderData: FlBorderData(show: false),
        titlesData: FlTitlesData(
          leftTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          bottomTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 20,
              getTitlesWidget: (value, meta) => Text('S${value.toInt() + 1}', style: const TextStyle(fontSize: 10)),
            ),
          ),
        ),
        barGroups: [
          for (var i = 0; i < values.length; i++)
            BarChartGroupData(
              x: i,
              barRods: [
                BarChartRodData(
                  toY: values[i],
                  width: 14,
                  color: const Color(0xFF2D7BD0),
                  borderRadius: BorderRadius.circular(4),
                ),
              ],
            ),
        ],
      ),
    );
  }
}
