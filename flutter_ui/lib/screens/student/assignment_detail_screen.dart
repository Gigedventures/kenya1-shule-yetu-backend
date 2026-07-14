/// Assignment detail screen - Detail layer.
library assignment_detail_screen;

import 'package:flutter/material.dart';
import '../../widgets/density/index.dart';
import '../../theme/density_tokens.dart';
import '../../models/student_models.dart';

class AssignmentDetailScreen extends StatelessWidget {
  const AssignmentDetailScreen({super.key, required this.assignment});

  final Assignment assignment;

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Scaffold(
      backgroundColor: config.bgBase,
      appBar: AppBar(
        title: Text(assignment.title, style: config.title),
        backgroundColor: config.bgSurface,
        elevation: 0,
        scrolledUnderElevation: 1,
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(config.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header card
            CompactCard(
              padding: EdgeInsets.all(config.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      _TypeBadge(type: assignment.type),
                      const Spacer(),
                      _StatusBadge(label: assignment.statusLabel, color: _statusColor(assignment.status)),
                    ],
                  ),
                  SizedBox(height: config.md),
                  Text(assignment.subject, style: config.metadata.copyWith(color: config.primary)),
                  SizedBox(height: config.xs),
                  Text(assignment.title, style: config.title.copyWith(fontSize: 20)),
                  SizedBox(height: config.lg),
                  Row(
                    children: [
                      _DetailItem(
                        icon: Icons.calendar_today,
                        label: 'Due',
                        value: assignment.formattedDueDate,
                        color: assignment.isOverdue ? config.error : config.success,
                      ),
                      SizedBox(width: config.lg),
                      _DetailItem(
                        icon: Icons.assignment,
                        label: 'Max Score',
                        value: assignment.maxScore.toString(),
                        color: config.info,
                      ),
                      if (assignment.score != null) ...[
                        SizedBox(width: config.lg),
                        _DetailItem(
                          icon: Icons.grade,
                          label: 'Your Score',
                          value: '${assignment.score!.toStringAsFixed(1)} (${assignment.percentage!.toStringAsFixed(1)}%)',
                          color: config.success,
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),

            SizedBox(height: config.lg),

            // Teacher info
            if (assignment.teacherName != 'TBD')
              InfoCard(
                title: 'Teacher',
                value: assignment.teacherName,
                icon: Icons.person_outline,
                iconColor: config.primary,
              ),

            SizedBox(height: config.lg),

            // Description
            CompactCard(
              padding: EdgeInsets.all(config.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Description', style: config.title),
                  SizedBox(height: config.md),
                  Text(
                    'This is a detailed description of the assignment. It would contain instructions, requirements, and any other relevant information for the student.',
                    style: config.body,
                  ),
                ],
              ),
            ),

            SizedBox(height: config.lg),

            // Attachments
            if (assignment.attachments.isNotEmpty) ...[
              CompactCard(
                padding: EdgeInsets.all(config.lg),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Attachments', style: config.title),
                    SizedBox(height: config.md),
                    ...assignment.attachments.map((a) => ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: Icon(Icons.insert_drive_file, color: config.primary),
                      title: Text(a, style: config.body),
                      trailing: Icon(Icons.download, size: config.iconSm, color: config.textMuted),
                      onTap: () {}, // Download attachment
                    )),
                  ],
                ),
              ),
              SizedBox(height: config.lg),
            ],

            // Submission info
            if (assignment.submission != null) ...[
              CompactCard(
                padding: EdgeInsets.all(config.lg),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Your Submission', style: config.title),
                    SizedBox(height: config.md),
                    _DetailRow(label: 'Submitted', value: _formatDateTime(assignment.submission!.submittedAt)),
                    if (assignment.submission!.gradedAt != null)
                      _DetailRow(label: 'Graded', value: _formatDateTime(assignment.submission!.gradedAt)),
                    if (assignment.submission!.feedback != null) ...[
                      SizedBox(height: config.md),
                      Text('Feedback', style: config.metadata.copyWith(color: config.textMuted)),
                      SizedBox(height: config.xs),
                      Text(assignment.submission!.feedback!, style: config.body),
                    ],
                  ],
                ),
              ),
              SizedBox(height: config.lg),
            ] else if (assignment.isPending) ...[
              // Submit button
              SizedBox(
                width: double.infinity,
                child: FilledButton.icon(
                  onPressed: () {}, // TODO: Submit assignment
                  icon: Icon(Icons.upload_file, size: config.iconSm),
                  label: Text('Submit Assignment', style: config.interactive),
                  style: FilledButton.styleFrom(padding: EdgeInsets.symmetric(vertical: config.md)),
                ),
              ),
            ],

            SizedBox(height: config.xxl),
          ],
        ),
      ),
    );
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'pending':
        return Colors.orange;
      case 'submitted':
        return Colors.blue;
      case 'graded':
        return Colors.green;
      case 'overdue':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  String _formatDateTime(DateTime? dt) {
    if (dt == null) return '—';
    return '${dt.day}/${dt.month}/${dt.year} ${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
  }
}

class _DetailItem extends StatelessWidget {
  const _DetailItem({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
  });

  final IconData icon;
  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    return Expanded(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: config.iconXs, color: color),
              SizedBox(width: 4),
              Text(label, style: config.metadata.copyWith(color: config.textMuted)),
            ],
          ),
          SizedBox(height: 2),
          Text(value, style: config.value.copyWith(color: color)),
        ],
      ),
    );
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
      padding: EdgeInsets.only(bottom: config.xs),
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

class _TypeBadge extends StatelessWidget {
  const _TypeBadge({required this.type});

  final String type;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    final (label, color) = switch (type.toLowerCase()) {
      'homework' => ('Homework', Colors.blue),
      'exam' => ('Exam', Colors.purple),
      'project' => ('Project', Colors.teal),
      _ => (type, Colors.grey),
    };

    return Container(
      padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(config.badgeRadius),
      ),
      child: Text(label, style: config.caption.copyWith(color: color, fontWeight: FontWeight.w600)),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  const _StatusBadge({required this.label, required this.color});

  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final config = context.density;
    return Container(
      padding: EdgeInsets.symmetric(horizontal: config.xs, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(config.badgeRadius),
      ),
      child: Text(label, style: config.caption.copyWith(color: color, fontWeight: FontWeight.w600)),
    );
  }
}