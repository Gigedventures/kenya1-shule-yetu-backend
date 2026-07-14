/// Attendance detail screen - Detail layer.
library attendance_detail_screen;

import 'package:flutter/material.dart';
import '../../widgets/density/index.dart';
import '../../theme/density_tokens.dart';
import '../../models/student_models.dart';

class AttendanceDetailScreen extends StatelessWidget {
  const AttendanceDetailScreen({super.key, required this.record});

  final AttendanceRecord record;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final statusColor = _parseColor(record.statusColor);

    return Scaffold(
      backgroundColor: config.bgBase,
      appBar: AppBar(
        title: Text('Attendance Detail', style: config.title),
        backgroundColor: config.bgSurface,
        elevation: 0,
        scrolledUnderElevation: 1,
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(config.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status header
            Container(
              width: double.infinity,
              padding: EdgeInsets.all(config.lg),
              decoration: BoxDecoration(
                color: statusColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(config.radiusMd),
                border: Border.all(color: statusColor.withValues(alpha: 0.3)),
              ),
              child: Column(
                children: [
                  Container(
                    width: 64,
                    height: 64,
                    decoration: BoxDecoration(
                      color: statusColor,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(_statusIcon(record.status), size: 32, color: Colors.white),
                  ),
                  SizedBox(height: config.md),
                  Text(record.statusLabel, style: config.title.copyWith(fontSize: 24, color: statusColor)),
                  SizedBox(height: config.xs),
                  Text(_formatDate(record.date), style: config.body.copyWith(color: config.textSecondary)),
                ],
              ),
            ),

            SizedBox(height: config.lg),

            // Details
            CompactCard(
              padding: EdgeInsets.all(config.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Details', style: config.title),
                  SizedBox(height: config.md),
                  _DetailRow(label: 'Class', value: record.className),
                  _DetailRow(label: 'Subject', value: record.subject),
                  _DetailRow(label: 'Marked by', value: record.markedBy),
                  if (record.checkInTime != null)
                    _DetailRow(label: 'Check-in', value: record.checkInTime!),
                  if (record.checkOutTime != null)
                    _DetailRow(label: 'Check-out', value: record.checkOutTime!),
                  if (record.notes != null && record.notes!.isNotEmpty) ...[
                    SizedBox(height: config.md),
                    Text('Notes', style: config.metadata.copyWith(color: config.textMuted)),
                    SizedBox(height: config.xs),
                    Text(record.notes!, style: config.body),
                  ],
                ],
              ),
            ),

            SizedBox(height: config.lg),

            // Calendar heatmap placeholder
            CompactCard(
              padding: EdgeInsets.all(config.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Attendance History', style: config.title),
                      TextButton(
                        onPressed: () {}, // TODO: View full calendar
                        child: Text('View all', style: config.interactive),
                      ),
                    ],
                  ),
                  SizedBox(height: config.md),
                  _buildMiniHeatmap(context),
                ],
              ),
            ),

            SizedBox(height: config.xxl),
          ],
        ),
      ),
    );
  }

  Widget _buildMiniHeatmap(BuildContext context) {
    final config = context.density;
    // Placeholder for attendance heatmap
    return Container(
      height: 120,
      decoration: BoxDecoration(
        color: config.bgBase,
        borderRadius: BorderRadius.circular(config.radiusSm),
        border: Border.all(color: config.borderLight),
      ),
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.calendar_month, size: 32, color: config.textMuted),
            SizedBox(height: config.sm),
            Text('Attendance calendar', style: config.body.copyWith(color: config.textMuted)),
            SizedBox(height: config.xs),
            Text('Heatmap visualization coming soon', style: config.caption.copyWith(color: config.textMuted)),
          ],
        ),
      ),
    );
  }

  IconData _statusIcon(String status) {
    switch (status) {
      case 'present':
        return Icons.check;
      case 'absent':
        return Icons.close;
      case 'late':
        return Icons.access_time;
      case 'excused':
        return Icons.verified;
      default:
        return Icons.help;
    }
  }

  Color _parseColor(String hex) {
    try {
      return Color(int.parse(hex.replaceFirst('#', '0xFF')));
    } catch (_) {
      return Colors.grey;
    }
  }

  String _formatDate(DateTime date) {
    return '${_dayName(date.weekday)}, ${date.day} ${_monthName(date.month)} ${date.year}';
  }

  String _dayName(int weekday) {
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    return days[weekday - 1];
  }

  String _monthName(int month) {
    const months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    return months[month - 1];
  }
}

class _DetailRow extends StatelessWidget {
  const _DetailRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    return Padding(
      padding: EdgeInsets.only(bottom: config.sm),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(label, style: config.metadata.copyWith(color: config.textMuted)),
          ),
          Expanded(child: Text(value, style: config.body)),
        ],
      ),
    );
  }
}