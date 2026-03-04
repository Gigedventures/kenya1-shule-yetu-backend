import 'package:flutter/material.dart';

import '../theme/k1_colors.dart';

class WalletPulseWidget extends StatelessWidget {
  const WalletPulseWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return Theme(
      data: ThemeData(dividerColor: Colors.transparent),
      child: ExpansionTile(
        tilePadding: EdgeInsets.zero,
        childrenPadding: EdgeInsets.zero,
        initiallyExpanded: true,
        title: const Text(
          'KES 12,450',
          style: TextStyle(
              fontWeight: FontWeight.w900, color: K1Colors.text, fontSize: 14),
        ),
        subtitle: const Text(
          'Wallet balance',
          style: TextStyle(
              fontWeight: FontWeight.w700,
              color: K1Colors.muted,
              fontSize: 11.5),
        ),
        children: const [
          SizedBox(height: 4),
          Row(
            children: [
              Expanded(child: _WalletChip(label: 'Wallets', value: '3')),
              SizedBox(width: 6),
              Expanded(child: _WalletChip(label: 'Bills', value: '2')),
              SizedBox(width: 6),
              Expanded(child: _WalletChip(label: 'Rewards', value: '184')),
            ],
          ),
          SizedBox(height: 8),
          _TransactionRow(title: 'School fees payment', amount: '-KES 4,000'),
          SizedBox(height: 4),
          _TransactionRow(title: 'Wallet top-up', amount: '+KES 2,500'),
        ],
      ),
    );
  }
}

class _WalletChip extends StatelessWidget {
  const _WalletChip({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 7),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF5E8),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: const Color(0xFFFFD8AB)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label,
              style: const TextStyle(color: K1Colors.muted, fontSize: 10.5)),
          const SizedBox(height: 2),
          Text(value,
              style: const TextStyle(
                  color: K1Colors.text,
                  fontWeight: FontWeight.w900,
                  fontSize: 12)),
        ],
      ),
    );
  }
}

class _TransactionRow extends StatelessWidget {
  const _TransactionRow({required this.title, required this.amount});

  final String title;
  final String amount;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF8EE),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: const Color(0xFFFFE1B8)),
      ),
      child: Row(
        children: [
          Expanded(
              child: Text(title,
                  style: const TextStyle(
                      color: K1Colors.text,
                      fontWeight: FontWeight.w700,
                      fontSize: 11))),
          Text(amount,
              style: const TextStyle(
                  color: K1Colors.orangeDark,
                  fontWeight: FontWeight.w800,
                  fontSize: 11)),
        ],
      ),
    );
  }
}
