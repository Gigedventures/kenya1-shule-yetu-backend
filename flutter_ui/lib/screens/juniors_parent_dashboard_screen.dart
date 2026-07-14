import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

import '../data/mock_data.dart';
import '../models/chat_models.dart';
import '../models/fee_models.dart';
import '../services/chat_service.dart';
import '../services/fee_service.dart';
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

  // Map CBC layer to demo student IDs from MockUsersData
  String _studentIdForLayer(CbcLayer layer) {
    switch (layer) {
      case CbcLayer.pp1Pp2:
        return '6';
      case CbcLayer.grade1To6:
        return '1';
      case CbcLayer.grade7To9:
        return '11';
    }
  }

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
                              _FeesTab(studentId: _studentIdForLayer(_selectedLayer)),
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
              textStyle: WidgetStatePropertyAll(TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
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

class _FeesTab extends StatefulWidget {
  const _FeesTab({required this.studentId});

  final String studentId;

  @override
  State<_FeesTab> createState() => _FeesTabState();
}

class _FeesTabState extends State<_FeesTab> {
  late final FeeService _feeService;
  StudentStatement? _statement;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _feeService = FeeService(
      baseUrl: 'http://10.0.2.2:8000', // Android emulator -> host machine
      tokenProvider: () async => null, // TODO: integrate with auth service
    );
    _loadStatement();
  }

  Future<void> _loadStatement() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final statement = await _feeService.getStudentStatement(widget.studentId);
      if (mounted) {
        setState(() {
          _statement = statement;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return ListView(
        padding: EdgeInsets.only(bottom: 16),
        children: [
          _Panel(
            title: 'Finance',
            child: const Center(
              child: Padding(
                padding: EdgeInsets.all(24),
                child: CircularProgressIndicator(),
              ),
            ),
          ),
        ],
      );
    }

    if (_error != null) {
      return ListView(
        padding: EdgeInsets.only(bottom: 16),
        children: [
          _Panel(
            title: 'Finance',
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Failed to load fee statement',
                    style: TextStyle(color: Colors.red, fontWeight: FontWeight.w800)),
                SizedBox(height: 8),
                Text(_error!, style: const TextStyle(fontSize: 12, color: Colors.grey)),
                SizedBox(height: 12),
                FilledButton(
                  onPressed: _loadStatement,
                  child: const Text('Retry'),
                ),
              ],
            ),
          ),
        ],
      );
    }

    final statement = _statement!;
    final summary = statement.summary;

    return ListView(
      padding: EdgeInsets.only(bottom: 16),
      children: [
        _Panel(
          title: 'Fee Statement',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Summary cards
              Row(
                children: [
                  Expanded(
                    child: _SummaryCard(
                      label: 'Total Billed',
                      value: 'KES ${summary.totalBilled.toStringAsFixed(0)}',
                      icon: Icons.receipt_long,
                      color: const Color(0xFF1D5EB3),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _SummaryCard(
                      label: 'Total Paid',
                      value: 'KES ${summary.totalPaid.toStringAsFixed(0)}',
                      icon: Icons.check_circle_outline,
                      color: const Color(0xFF0F9D7A),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              _SummaryCard(
                label: 'Outstanding Balance',
                value: 'KES ${summary.balance.toStringAsFixed(0)}',
                icon: Icons.warning_amber_rounded,
                color: summary.balance > 0 ? const Color(0xFFD84315) : const Color(0xFF0F9D7A),
                isWide: true,
              ),
              const SizedBox(height: 16),
              // Bills list
              if (statement.bills.isNotEmpty) ...[
                const Text('Fee Bills',
                    style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
                const SizedBox(height: 8),
                ...statement.bills.map((bill) => _BillTile(bill: bill)).toList(),
              ] else ...[
                const Text('No fee bills found',
                    style: TextStyle(color: Colors.grey, fontSize: 13)),
              ],
              const SizedBox(height: 16),
              // Payments list
              if (statement.payments.isNotEmpty) ...[
                const Text('Recent Payments',
                    style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
                const SizedBox(height: 8),
                ...statement.payments.take(5).map((payment) => _PaymentTile(payment: payment)).toList(),
              ],
              const SizedBox(height: 16),
              // Pay button
              if (summary.balance > 0)
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF1D5EB3),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    onPressed: () => _showPaymentDialog(context),
                    icon: const Icon(Icons.payment),
                    label: const Text('Make Payment',
                        style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                  ),
                ),
            ],
          ),
        ),
      ],
    );
  }

  void _showPaymentDialog(BuildContext context) {
    final amountController = TextEditingController();
    final referenceController = TextEditingController();
    String selectedMethod = 'M-Pesa';

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Make Payment'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: amountController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Amount (KES)',
                prefixText: 'KES ',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: selectedMethod,
              decoration: const InputDecoration(
                labelText: 'Payment Method',
                border: OutlineInputBorder(),
              ),
              items: const [
                DropdownMenuItem(value: 'M-Pesa', child: Text('M-Pesa')),
                DropdownMenuItem(value: 'Card', child: Text('Card')),
                DropdownMenuItem(value: 'Bank Transfer', child: Text('Bank Transfer')),
                DropdownMenuItem(value: 'Cash', child: Text('Cash')),
              ],
              onChanged: (value) => selectedMethod = value ?? 'M-Pesa',
            ),
            const SizedBox(height: 12),
            TextField(
              controller: referenceController,
              decoration: const InputDecoration(
                labelText: 'Reference (optional)',
                border: OutlineInputBorder(),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () async {
              final amount = double.tryParse(amountController.text);
              if (amount == null || amount <= 0) return;
              Navigator.pop(context);
              try {
                await _feeService.recordPayment(
                  studentId: widget.studentId,
                  amount: amount,
                  paymentMethod: selectedMethod,
                  reference: referenceController.text.isEmpty
                      ? null
                      : referenceController.text,
                );
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Payment recorded successfully')),
                  );
                  _loadStatement();
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Payment failed: $e')),
                  );
                }
              }
            },
            child: const Text('Pay'),
          ),
        ],
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  const _SummaryCard({
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
    this.isWide = false,
  });

  final String label;
  final String value;
  final IconData icon;
  final Color color;
  final bool isWide;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: isWide ? double.infinity : null,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: color, size: 20),
              const SizedBox(width: 8),
              Text(label,
                  style: TextStyle(color: Colors.grey[600], fontWeight: FontWeight.w600)),
            ],
          ),
          const SizedBox(height: 6),
          Text(value,
              style: TextStyle(
                  color: color,
                  fontWeight: FontWeight.w900,
                  fontSize: isWide ? 18 : 16)),
        ],
      ),
    );
  }
}

class _BillTile extends StatelessWidget {
  const _BillTile({required this.bill});

  final StudentBill bill;

  @override
  Widget build(BuildContext context) {
    Color statusColor;
    switch (bill.status) {
      case 'paid':
        statusColor = const Color(0xFF0F9D7A);
        break;
      case 'partial':
        statusColor = const Color(0xFFF59E0B);
        break;
      default:
        statusColor = const Color(0xFFD84315);
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFD8E3F1)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(bill.feeStructureName ?? 'Fee Bill',
                    style: const TextStyle(fontWeight: FontWeight.w800)),
              ),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(bill.status.toUpperCase(),
                    style: TextStyle(
                        color: statusColor,
                        fontWeight: FontWeight.w800,
                        fontSize: 11)),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Total: KES ${bill.totalAmount.toStringAsFixed(0)}',
                        style: const TextStyle(fontSize: 12, color: Colors.grey)),
                    Text('Paid: KES ${bill.paidAmount.toStringAsFixed(0)}',
                        style: const TextStyle(fontSize: 12, color: Colors.grey)),
                    Text('Balance: KES ${bill.balance.toStringAsFixed(0)}',
                        style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFFD84315))),
                  ],
                ),
              ),
              if (bill.dueDate != null)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text('Due: ${_formatDate(bill.dueDate!)}',
                        style: const TextStyle(fontSize: 12, color: Colors.grey)),
                  ],
                ),
            ],
          ),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(999),
            child: LinearProgressIndicator(
              value: bill.progress.clamp(0.0, 1.0),
              minHeight: 6,
              backgroundColor: const Color(0xFFE2EAF5),
              valueColor: AlwaysStoppedAnimation(statusColor),
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}

class _PaymentTile extends StatelessWidget {
  const _PaymentTile({required this.payment});

  final Payment payment;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFD),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFDCE6F6)),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: const Color(0xFF1D5EB3).withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.receipt, color: Color(0xFF1D5EB3), size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('KES ${payment.amount.toStringAsFixed(0)}',
                    style: const TextStyle(fontWeight: FontWeight.w900)),
                Text('${payment.paymentMethod} • ${_formatDate(payment.paymentDate)}',
                    style: const TextStyle(fontSize: 12, color: Colors.grey)),
                if (payment.reference != null)
                  Text('Ref: ${payment.reference}',
                      style: const TextStyle(fontSize: 11, color: Colors.grey)),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFF0F9D7A).withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(999),
            ),
            child: Text(payment.status.toUpperCase(),
                style: const TextStyle(
                    color: Color(0xFF0F9D7A),
                    fontWeight: FontWeight.w800,
                    fontSize: 11)),
          ),
        ],
      ),
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }
}

class _ChatTab extends StatefulWidget {
  const _ChatTab();

  @override
  State<_ChatTab> createState() => _ChatTabState();
}

class _ChatTabState extends State<_ChatTab> {
  late final ChatService _chatService;
  List<ChatThread> _threads = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _chatService = ChatService(
      baseUrl: 'http://10.0.2.2:8000',
      tokenProvider: () async => null, // TODO: integrate with auth service
    );
    _loadThreads();
  }

  Future<void> _loadThreads() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final threads = await _chatService.getChatThreads();
      if (mounted) {
        setState(() {
          _threads = threads;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return ListView(
        padding: EdgeInsets.only(bottom: 16),
        children: [
          _Panel(
            title: 'Chat',
            child: const Center(
              child: Padding(
                padding: EdgeInsets.all(24),
                child: CircularProgressIndicator(),
              ),
            ),
          ),
        ],
      );
    }

    if (_error != null) {
      return ListView(
        padding: EdgeInsets.only(bottom: 16),
        children: [
          _Panel(
            title: 'Chat',
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Failed to load conversations',
                    style: TextStyle(color: Colors.red, fontWeight: FontWeight.w800)),
                SizedBox(height: 8),
                Text(_error!, style: const TextStyle(fontSize: 12, color: Colors.grey)),
                SizedBox(height: 12),
                FilledButton(
                  onPressed: _loadThreads,
                  child: const Text('Retry'),
                ),
              ],
            ),
          ),
        ],
      );
    }

    if (_threads.isEmpty) {
      return ListView(
        padding: EdgeInsets.only(bottom: 16),
        children: [
          _Panel(
            title: 'Chat',
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('No conversations yet',
                    style: TextStyle(color: Colors.grey, fontSize: 14)),
                const SizedBox(height: 12),
                FilledButton.icon(
                  onPressed: () => _showNewChatDialog(context),
                  icon: const Icon(Icons.add_comment),
                  label: const Text('Start New Conversation'),
                ),
              ],
            ),
          ),
        ],
      );
    }

    return ListView(
      padding: EdgeInsets.only(bottom: 16),
      children: [
        _Panel(
          title: 'Conversations',
          child: Column(
            children: _threads
                .map((thread) => _ThreadTile(
                      thread: thread,
                      onTap: () => _openThread(context, thread),
                    ))
                .toList(),
          ),
        ),
        const SizedBox(height: 12),
        _Panel(
          title: 'Actions',
          child: SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () => _showNewChatDialog(context),
              icon: const Icon(Icons.add_comment),
              label: const Text('Start New Conversation'),
            ),
          ),
        ),
      ],
    );
  }

  void _openThread(BuildContext context, ChatThread thread) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => _ThreadDetailScreen(
          thread: thread,
          chatService: _chatService,
          onMessageSent: _loadThreads,
        ),
      ),
    );
  }

  void _showNewChatDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => _NewChatDialog(chatService: _chatService),
    );
  }
}

class _ThreadTile extends StatelessWidget {
  const _ThreadTile({
    required this.thread,
    required this.onTap,
  });

  final ChatThread thread;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
        child: Row(
          children: [
            Stack(
              children: [
                CircleAvatar(
                  radius: 24,
                  backgroundColor: const Color(0xFF1D5EB3).withValues(alpha: 0.1),
                  backgroundImage: thread.participantAvatarUrl != null
                      ? NetworkImage(thread.participantAvatarUrl!)
                      : null,
                  child: thread.participantAvatarUrl == null
                      ? const Icon(Icons.person, color: Color(0xFF1D5EB3), size: 24)
                      : null,
                ),
                if (thread.isOnline)
                  Positioned(
                    right: 0,
                    bottom: 0,
                    child: Container(
                      width: 12,
                      height: 12,
                      decoration: BoxDecoration(
                        color: const Color(0xFF0F9D7A),
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.white, width: 2),
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(thread.participantName,
                            style: const TextStyle(fontWeight: FontWeight.w800),
                            overflow: TextOverflow.ellipsis),
                      ),
                      Text(_formatTime(thread.lastMessageTime),
                          style: const TextStyle(fontSize: 11, color: Colors.grey)),
                    ],
                  ),
                  const SizedBox(height: 2),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFF1D5EB3).withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text(thread.participantRole,
                            style: const TextStyle(
                                color: Color(0xFF1D5EB3),
                                fontWeight: FontWeight.w700,
                                fontSize: 10)),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(thread.lastMessage,
                            style: const TextStyle(fontSize: 12, color: Colors.grey),
                            overflow: TextOverflow.ellipsis,
                            maxLines: 1),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            if (thread.unreadCount > 0)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFFD84315),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text('${thread.unreadCount}',
                    style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w800,
                        fontSize: 11)),
              ),
          ],
        ),
      ),
    );
  }

  String _formatTime(DateTime date) {
    final now = DateTime.now();
    final diff = now.difference(date);
    if (diff.inDays > 0) {
      return '${diff.inDays}d';
    } else if (diff.inHours > 0) {
      return '${diff.inHours}h';
    } else if (diff.inMinutes > 0) {
      return '${diff.inMinutes}m';
    } else {
      return 'now';
    }
  }
}

class _ThreadDetailScreen extends StatefulWidget {
  const _ThreadDetailScreen({
    required this.thread,
    required this.chatService,
    required this.onMessageSent,
  });

  final ChatThread thread;
  final ChatService chatService;
  final VoidCallback onMessageSent;

  @override
  State<_ThreadDetailScreen> createState() => _ThreadDetailScreenState();
}

class _ThreadDetailScreenState extends State<_ThreadDetailScreen> {
  List<ChatMessage> _messages = [];
  bool _isLoading = true;
  final _messageController = TextEditingController();
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _loadMessages();
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadMessages() async {
    try {
      final messages = await widget.chatService.getThreadMessages(widget.thread.id);
      if (mounted) {
        setState(() {
          _messages = messages;
          _isLoading = false;
        });
        _scrollToBottom();
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty) return;

    _messageController.clear();
    try {
      await widget.chatService.sendMessage(
        threadId: widget.thread.id,
        body: text,
      );
      widget.onMessageSent();
      _loadMessages();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to send: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(widget.thread.participantName,
                style: const TextStyle(fontWeight: FontWeight.w800)),
            Text(widget.thread.participantRole,
                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500)),
          ],
        ),
        actions: [
          if (widget.thread.isOnline)
            Container(
              margin: const EdgeInsets.only(right: 12),
              width: 10,
              height: 10,
              decoration: const BoxDecoration(
                color: Color(0xFF0F9D7A),
                shape: BoxShape.circle,
              ),
            ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : ListView.builder(
                    controller: _scrollController,
                    padding: const EdgeInsets.all(16),
                    itemCount: _messages.length,
                    itemBuilder: (context, index) {
                      final message = _messages[index];
                      return _MessageBubble(message: message);
                    },
                  ),
          ),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(top: BorderSide(color: Colors.grey[300]!)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _messageController,
                    decoration: const InputDecoration(
                      hintText: 'Type a message...',
                      border: OutlineInputBorder(),
                      contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                    onSubmitted: (_) => _sendMessage(),
                  ),
                ),
                const SizedBox(width: 8),
                FilledButton(
                  onPressed: _sendMessage,
                  child: const Icon(Icons.send),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _MessageBubble extends StatelessWidget {
  const _MessageBubble({required this.message});

  final ChatMessage message;

  @override
  Widget build(BuildContext context) {
    final isMe = message.isFromCurrentUser;
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment: isMe ? MainAxisAlignment.end : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          if (!isMe) ...[
            CircleAvatar(
              radius: 14,
              backgroundColor: const Color(0xFF1D5EB3).withValues(alpha: 0.1),
              backgroundImage: message.senderAvatarUrl != null
                  ? NetworkImage(message.senderAvatarUrl!)
                  : null,
              child: message.senderAvatarUrl == null
                  ? const Icon(Icons.person, color: Color(0xFF1D5EB3), size: 14)
                  : null,
            ),
            const SizedBox(width: 8),
          ],
          Flexible(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: isMe ? const Color(0xFF1D5EB3) : Colors.grey[100],
                borderRadius: BorderRadius.circular(16).copyWith(
                  bottomLeft: isMe ? const Radius.circular(16) : const Radius.circular(4),
                  bottomRight: isMe ? const Radius.circular(4) : const Radius.circular(16),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (!isMe)
                    Text(message.senderName,
                        style: const TextStyle(
                            fontWeight: FontWeight.w700,
                            fontSize: 11,
                            color: Color(0xFF1D5EB3))),
                  Text(message.body,
                      style: TextStyle(
                          color: isMe ? Colors.white : Colors.black87,
                          fontSize: 14)),
                  const SizedBox(height: 4),
                  Text(_formatTime(message.sentAt),
                      style: TextStyle(
                          color: isMe ? Colors.white70 : Colors.grey,
                          fontSize: 10)),
                ],
              ),
            ),
          ),
          if (isMe) ...[
            const SizedBox(width: 8),
            CircleAvatar(
              radius: 14,
              backgroundColor: const Color(0xFF0F9D7A).withValues(alpha: 0.1),
              child: const Icon(Icons.person, color: Color(0xFF0F9D7A), size: 14),
            ),
          ],
        ],
      ),
    );
  }

  String _formatTime(DateTime date) {
    return '${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}'; 
  }
}

class _NewChatDialog extends StatefulWidget {
  const _NewChatDialog({required this.chatService});

  final ChatService chatService;

  @override
  State<_NewChatDialog> createState() => _NewChatDialogState();
}

class _NewChatDialogState extends State<_NewChatDialog> {
  List<ChatContact> _contacts = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadContacts();
  }

  Future<void> _loadContacts() async {
    try {
      final contacts = await widget.chatService.getContacts();
      if (mounted) {
        setState(() {
          _contacts = contacts;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Start New Conversation'),
      content: SizedBox(
        width: double.maxFinite,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text('Failed to load contacts',
                          style: TextStyle(color: Colors.red, fontWeight: FontWeight.w800)),
                      SizedBox(height: 8),
                      Text(_error!, style: const TextStyle(fontSize: 12, color: Colors.grey)),
                    ],
                  )
                : _contacts.isEmpty
                    ? const Text('No contacts available')
                    : ListView.builder(
                        shrinkWrap: true,
                        itemCount: _contacts.length,
                        itemBuilder: (context, index) {
                          final contact = _contacts[index];
                          return ListTile(
                            leading: CircleAvatar(
                              backgroundColor: contact.isOnline
                                  ? const Color(0xFF0F9D7A)
                                  : Colors.grey,
                              child: const Icon(Icons.person, color: Colors.white),
                            ),
                            title: Text(contact.name,
                                style: const TextStyle(fontWeight: FontWeight.w700)),
                            subtitle: Text('${contact.role} • ${contact.isOnline ? 'Online' : 'Offline'}'),
                            onTap: () => _startChat(context, contact),
                          );
                        },
                      ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Cancel'),
        ),
      ],
    );
  }

  void _startChat(BuildContext context, ChatContact contact) {
    Navigator.pop(context);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Starting chat with ${contact.name}...')),
    );
    // TODO: Call createThread API and navigate to thread
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