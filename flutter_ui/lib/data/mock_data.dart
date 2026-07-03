import 'package:flutter/material.dart';

import '../models/quick_service.dart';
import '../models/smart_feed_item.dart';
import '../models/update_item.dart';
import '../theme/app_icons.dart';

enum CbcLayer { pp1Pp2, grade1To6, grade7To9 }

class CompactMetric {
  const CompactMetric({required this.label, required this.value, required this.icon});

  final String label;
  final String value;
  final IconData icon;
}

class AlertItem {
  const AlertItem({required this.title, required this.severity});

  final String title;
  final String severity;
}

class ScheduleItem {
  const ScheduleItem({
    required this.time,
    required this.title,
    required this.location,
  });

  final String time;
  final String title;
  final String location;
}

class SubjectProgress {
  const SubjectProgress({required this.subject, required this.value});

  final String subject;
  final double value;
}

class LearningActivity {
  const LearningActivity({
    required this.title,
    required this.subtitle,
    required this.icon,
  });

  final String title;
  final String subtitle;
  final IconData icon;
}

class JuniorLayerData {
  const JuniorLayerData({
    required this.layerTitle,
    required this.learnerName,
    required this.gradeLabel,
    required this.metrics,
    required this.alerts,
    required this.todaySchedule,
    required this.subjects,
    required this.progress,
    required this.activities,
    required this.strandChart,
    required this.sparkline,
  });

  final String layerTitle;
  final String learnerName;
  final String gradeLabel;
  final List<CompactMetric> metrics;
  final List<AlertItem> alerts;
  final List<ScheduleItem> todaySchedule;
  final List<String> subjects;
  final List<SubjectProgress> progress;
  final List<LearningActivity> activities;
  final List<double> strandChart;
  final List<double> sparkline;
}

class SeniorKpi {
  const SeniorKpi({required this.label, required this.value, required this.icon});

  final String label;
  final String value;
  final IconData icon;
}

class SeniorData {
  const SeniorData({
    required this.studentName,
    required this.program,
    required this.semester,
    required this.kpis,
    required this.schedule,
    required this.updates,
    required this.alerts,
    required this.resultTrend,
    required this.courseCompletion,
  });

  final String studentName;
  final String program;
  final String semester;
  final List<SeniorKpi> kpis;
  final List<ScheduleItem> schedule;
  final List<String> updates;
  final List<AlertItem> alerts;
  final List<double> resultTrend;
  final List<double> courseCompletion;
}

class MockData {
  // TODO(backend): replace with repository calls from Kenya1/ShuleYetu APIs.
  static const kenyaQuickActions = [
    QuickService(title: 'K1 Wallet\nKES 12,450', iconAsset: AppIcons.wallet),
    QuickService(title: 'Scan & Pay', iconAsset: AppIcons.scanQr),
    QuickService(title: 'Send Money', iconAsset: AppIcons.sendMoney),
    QuickService(title: 'Request', iconAsset: AppIcons.request),
  ];

  static const quickServices = [
    QuickService(title: 'E-Soko', iconAsset: AppIcons.eSoko),
    QuickService(title: 'Just Eat!', iconAsset: AppIcons.justEat),
    QuickService(title: 'Twende', iconAsset: AppIcons.twende),
    QuickService(title: 'Shule Yetu', iconAsset: AppIcons.shuleYetu),
    QuickService(title: 'My Chamaa', iconAsset: AppIcons.myChamaa),
    QuickService(title: 'Hospital', iconAsset: AppIcons.hospital),
    QuickService(title: 'My Hasol!', iconAsset: AppIcons.myHasol),
    QuickService(title: 'Events & Tix', iconAsset: AppIcons.eventsTix),
  ];

  static const smartFeed = [
    SmartFeedItem(
      title: 'Chamaa Contribution Due!',
      subtitle: 'KES 5,000 by Today',
      cta: 'Pay Now',
      background: 0xFF1C3A78,
    ),
    SmartFeedItem(
      title: 'Amani School Fees Reminder',
      subtitle: 'Balance: KES 15,000',
      cta: 'Settle Now',
      background: 0xFF2B66B0,
    ),
    SmartFeedItem(
      title: '10% Off! Grilled Chicken Corner',
      subtitle: 'Limited offer today',
      cta: 'Order Now',
      background: 0xFF404040,
    ),
  ];

  static const exploreMore = [
    QuickService(title: 'E-Grocery', iconAsset: AppIcons.eGrocery),
    QuickService(title: 'Parcel Delivery', iconAsset: AppIcons.parcelDelivery),
    QuickService(title: 'Kenya Cademy', iconAsset: AppIcons.kenyaAcademy),
    QuickService(title: 'Property & Rent', iconAsset: AppIcons.propertyRent),
  ];

  static const financeCivic = [
    QuickService(title: 'Savings', iconAsset: AppIcons.savings),
    QuickService(title: 'Get a Loan', iconAsset: AppIcons.getLoan),
    QuickService(title: 'Wajibu!', iconAsset: AppIcons.wajibu),
    QuickService(title: 'Govt. Services', iconAsset: AppIcons.govtServices),
  ];

  static const parentUpdates = [
    UpdateItem(title: 'New Notice: Sports Day on Friday', color: 0xFFE84D2A),
    UpdateItem(title: 'Science Assignment Due Tomorrow', color: 0xFF1565C0),
  ];

  static const classSchedule = [
    ('Database Systems', '10:00 AM - 12:00 PM', 'Engineering Block'),
    ('Algorithms', '2:00 PM - 4:00 PM', 'Lecture Hall 3'),
  ];

  // CBC layer-aware dashboard data — replace with real API call when backend is ready.
  static const Map<CbcLayer, JuniorLayerData> juniorLayerData = {
    CbcLayer.pp1Pp2: JuniorLayerData(
      layerTitle: 'PP1-PP2',
      learnerName: 'Amani Njeri',
      gradeLabel: 'PP2 Tulips',
      metrics: [
        CompactMetric(label: 'Attendance', value: 'Present', icon: Icons.check_circle_outline),
        CompactMetric(label: 'Homework', value: '1 due', icon: Icons.assignment_outlined),
        CompactMetric(label: 'Transport', value: 'At gate', icon: Icons.directions_bus_outlined),
        CompactMetric(label: 'Meals', value: 'Served', icon: Icons.restaurant_outlined),
      ],
      alerts: [
        AlertItem(title: 'Show and tell items needed tomorrow.', severity: 'medium'),
        AlertItem(title: 'Uniform check on Friday morning.', severity: 'low'),
      ],
      todaySchedule: [
        ScheduleItem(time: '8:30', title: 'Language Play', location: 'Early Years Room'),
        ScheduleItem(time: '10:00', title: 'Creative Art', location: 'Art Corner'),
        ScheduleItem(time: '12:00', title: 'Outdoor Play', location: 'Field'),
      ],
      subjects: ['Language Activities', 'Math Activities', 'Environmental'],
      progress: [
        SubjectProgress(subject: 'Language Activities', value: 0.74),
        SubjectProgress(subject: 'Math Activities', value: 0.68),
        SubjectProgress(subject: 'Environmental', value: 0.72),
      ],
      activities: [
        LearningActivity(title: 'AI Tutor', subtitle: 'Story prompts and phonics', icon: Icons.smart_toy_outlined),
        LearningActivity(title: 'Daily Practice', subtitle: 'Tracing letters', icon: Icons.edit_note_outlined),
        LearningActivity(title: 'Games & Quizzes', subtitle: 'Shapes and colors', icon: Icons.sports_esports_outlined),
        LearningActivity(title: 'Reading Coach', subtitle: 'Sight words', icon: Icons.menu_book_outlined),
        LearningActivity(title: 'Math Coach', subtitle: 'Count and match', icon: Icons.calculate_outlined),
      ],
      strandChart: [42, 48, 56, 58, 64],
      sparkline: [52, 53, 57, 55, 60, 64, 66],
    ),
    CbcLayer.grade1To6: JuniorLayerData(
      layerTitle: 'Grade 1-6',
      learnerName: 'Brian Otieno',
      gradeLabel: 'Grade 4A',
      metrics: [
        CompactMetric(label: 'Attendance', value: '98%', icon: Icons.check_circle_outline),
        CompactMetric(label: 'Homework', value: '2 due', icon: Icons.assignment_outlined),
        CompactMetric(label: 'Transport', value: 'On route', icon: Icons.directions_bus_outlined),
        CompactMetric(label: 'Fees', value: 'KES 6,500', icon: Icons.payments_outlined),
      ],
      alerts: [
        AlertItem(title: 'Science model due Thursday.', severity: 'high'),
        AlertItem(title: 'PTA meeting on Saturday 10:00 AM.', severity: 'medium'),
      ],
      todaySchedule: [
        ScheduleItem(time: '8:00', title: 'Mathematics', location: 'Class 4A'),
        ScheduleItem(time: '9:40', title: 'English', location: 'Class 4A'),
        ScheduleItem(time: '11:20', title: 'Science & Tech', location: 'Lab 1'),
        ScheduleItem(time: '2:00', title: 'Games', location: 'Playground'),
      ],
      subjects: ['Math', 'English', 'Kiswahili', 'Science', 'Social Studies'],
      progress: [
        SubjectProgress(subject: 'Math', value: 0.8),
        SubjectProgress(subject: 'English', value: 0.75),
        SubjectProgress(subject: 'Kiswahili', value: 0.71),
        SubjectProgress(subject: 'Science', value: 0.77),
        SubjectProgress(subject: 'Social Studies', value: 0.69),
      ],
      activities: [
        LearningActivity(title: 'AI Tutor', subtitle: 'Homework hints', icon: Icons.smart_toy_outlined),
        LearningActivity(title: 'Daily Practice', subtitle: '20-minute worksheet', icon: Icons.edit_note_outlined),
        LearningActivity(title: 'Games & Quizzes', subtitle: 'Weekly challenge', icon: Icons.sports_esports_outlined),
        LearningActivity(title: 'Reading Coach', subtitle: 'Comprehension goals', icon: Icons.menu_book_outlined),
        LearningActivity(title: 'Math Coach', subtitle: 'Fractions mastery', icon: Icons.calculate_outlined),
      ],
      strandChart: [58, 60, 64, 70, 73, 78],
      sparkline: [61, 63, 62, 66, 69, 71, 74],
    ),
    CbcLayer.grade7To9: JuniorLayerData(
      layerTitle: 'Grade 7-9',
      learnerName: 'Sharon Wanjiru',
      gradeLabel: 'Grade 8 North',
      metrics: [
        CompactMetric(label: 'Attendance', value: '95%', icon: Icons.check_circle_outline),
        CompactMetric(label: 'Homework', value: '3 due', icon: Icons.assignment_outlined),
        CompactMetric(label: 'Transport', value: 'Boarded', icon: Icons.directions_bus_outlined),
        CompactMetric(label: 'Clubs', value: 'Debate', icon: Icons.groups_outlined),
      ],
      alerts: [
        AlertItem(title: 'Pre-tech project presentation tomorrow.', severity: 'high'),
        AlertItem(title: 'Inter-school games consent required.', severity: 'medium'),
      ],
      todaySchedule: [
        ScheduleItem(time: '7:50', title: 'Integrated Science', location: 'Science Block'),
        ScheduleItem(time: '10:10', title: 'Pre-Technical Studies', location: 'Workshop'),
        ScheduleItem(time: '1:20', title: 'Social Studies', location: 'G8 North'),
        ScheduleItem(time: '3:10', title: 'Clubs', location: 'Main Hall'),
      ],
      subjects: ['Integrated Science', 'Pre-Technical', 'Mathematics', 'Social Studies', 'Languages'],
      progress: [
        SubjectProgress(subject: 'Integrated Science', value: 0.73),
        SubjectProgress(subject: 'Pre-Technical', value: 0.76),
        SubjectProgress(subject: 'Mathematics', value: 0.7),
        SubjectProgress(subject: 'Social Studies', value: 0.78),
        SubjectProgress(subject: 'Languages', value: 0.74),
      ],
      activities: [
        LearningActivity(title: 'AI Tutor', subtitle: 'Topic revision mode', icon: Icons.smart_toy_outlined),
        LearningActivity(title: 'Daily Practice', subtitle: 'Exam-style questions', icon: Icons.edit_note_outlined),
        LearningActivity(title: 'Games & Quizzes', subtitle: 'Subject league', icon: Icons.sports_esports_outlined),
        LearningActivity(title: 'Reading Coach', subtitle: 'Summary drills', icon: Icons.menu_book_outlined),
        LearningActivity(title: 'Math Coach', subtitle: 'Algebra drills', icon: Icons.calculate_outlined),
      ],
      strandChart: [54, 57, 62, 66, 71, 74],
      sparkline: [58, 59, 61, 64, 63, 68, 70],
    ),
  };

  // Senior dashboard data — replace with real API call when backend is ready.
  static const SeniorData seniorData = SeniorData(
    studentName: 'Kelvin Mwangi',
    program: 'Year 2 | BSc. Computer Science',
    semester: 'Semester: Sept-Dec 2026',
    kpis: [
      SeniorKpi(label: 'GPA', value: '3.78', icon: Icons.auto_graph_outlined),
      SeniorKpi(label: 'Credits', value: '64/120', icon: Icons.school_outlined),
      SeniorKpi(label: 'Next Exam', value: '14 Mar', icon: Icons.event_note_outlined),
      SeniorKpi(label: 'Attendance', value: '92%', icon: Icons.fact_check_outlined),
    ],
    schedule: [
      ScheduleItem(time: '09:00', title: 'Data Structures', location: 'Engineering Block'),
      ScheduleItem(time: '11:00', title: 'Operating Systems', location: 'Lab 2'),
      ScheduleItem(time: '14:00', title: 'Discrete Math', location: 'Hall 3'),
    ],
    updates: [
      'Internship applications open until March 21.',
      'Library extends reading hours during exam week.',
      'Innovation challenge registration closes Friday.',
    ],
    alerts: [
      AlertItem(title: 'Exam card clearance pending KES 12,000.', severity: 'high'),
      AlertItem(title: 'Course evaluation form due this week.', severity: 'medium'),
    ],
    resultTrend: [3.2, 3.35, 3.45, 3.56, 3.63, 3.78],
    courseCompletion: [12, 16, 11, 19, 14, 18],
  );

  static LinearGradient heroBlueGradient = const LinearGradient(
    colors: [Color(0xFF1F5DB8), Color(0xFF6EA4F5)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
}
