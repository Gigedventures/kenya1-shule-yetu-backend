class StudentStatement {
  const StudentStatement({
    required this.studentId,
    required this.bills,
    required this.payments,
    required this.summary,
  });

  final String studentId;
  final List<StudentBill> bills;
  final List<Payment> payments;
  final StatementSummary summary;

  factory StudentStatement.fromJson(Map<String, dynamic> json) {
    return StudentStatement(
      studentId: json['student_id']?.toString() ?? '',
      bills: (json['bills'] as List? ?? [])
          .map((e) => StudentBill.fromJson(e))
          .toList(),
      payments: (json['payments'] as List? ?? [])
          .map((e) => Payment.fromJson(e))
          .toList(),
      summary: StatementSummary.fromJson(json['summary'] ?? {}),
    );
  }

  double get outstandingBalance => summary.balance;
  double get totalBilled => summary.totalBilled;
  double get totalPaid => summary.totalPaid;
  bool get hasOutstandingBalance => outstandingBalance > 0;
}

class StatementSummary {
  const StatementSummary({
    required this.totalBilled,
    required this.totalPaid,
    required this.balance,
  });

  final double totalBilled;
  final double totalPaid;
  final double balance;

  factory StatementSummary.fromJson(Map<String, dynamic> json) {
    return StatementSummary(
      totalBilled: (json['total_billed'] as num?)?.toDouble() ?? 0.0,
      totalPaid: (json['total_paid'] as num?)?.toDouble() ?? 0.0,
      balance: (json['balance'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

class StudentBill {
  const StudentBill({
    required this.id,
    required this.studentId,
    required this.feeStructureId,
    required this.totalAmount,
    required this.paidAmount,
    required this.balance,
    required this.status,
    this.feeStructureName,
    this.invoiceNumber,
    this.dueDate,
  });

  final String id;
  final String studentId;
  final String feeStructureId;
  final double totalAmount;
  final double paidAmount;
  final double balance;
  final String status; // unpaid, partial, paid
  final String? feeStructureName;
  final String? invoiceNumber;
  final DateTime? dueDate;

  factory StudentBill.fromJson(Map<String, dynamic> json) {
    return StudentBill(
      id: json['id']?.toString() ?? '',
      studentId: json['student_id']?.toString() ?? '',
      feeStructureId: json['fee_structure_id']?.toString() ?? '',
      totalAmount: (json['total_amount'] as num?)?.toDouble() ?? 0.0,
      paidAmount: (json['paid_amount'] as num?)?.toDouble() ?? 0.0,
      balance: (json['balance'] as num?)?.toDouble() ?? 0.0,
      status: json['status']?.toString() ?? 'unpaid',
      feeStructureName: json['fee_structure']?['name']?.toString(),
      invoiceNumber: json['invoice_number']?.toString(),
      dueDate: json['due_date'] != null
          ? DateTime.tryParse(json['due_date'].toString())
          : null,
    );
  }

  double get progress => totalAmount > 0 ? paidAmount / totalAmount : 0.0;
  bool get isPaid => status == 'paid';
  bool get isPartial => status == 'partial';
  bool get isUnpaid => status == 'unpaid';
}

class Payment {
  const Payment({
    required this.id,
    required this.studentId,
    required this.amount,
    required this.status,
    required this.paymentMethod,
    required this.reference,
    required this.paymentDate,
    this.allocations = const [],
    this.receiptNumber,
  });

  final String id;
  final String studentId;
  final double amount;
  final String status;
  final String paymentMethod;
  final String? reference;
  final DateTime paymentDate;
  final List<PaymentAllocation> allocations;
  final String? receiptNumber;

  factory Payment.fromJson(Map<String, dynamic> json) {
    return Payment(
      id: json['id']?.toString() ?? '',
      studentId: json['student_id']?.toString() ?? '',
      amount: (json['amount'] as num?)?.toDouble() ?? 0.0,
      status: json['status']?.toString() ?? 'posted',
      paymentMethod: json['payment_method']?.toString() ?? '',
      reference: json['reference']?.toString(),
      paymentDate: DateTime.tryParse(json['payment_date']?.toString() ?? '') ?? DateTime.now(),
      allocations: (json['allocations'] as List? ?? [])
          .map((e) => PaymentAllocation.fromJson(e))
          .toList(),
      receiptNumber: json['receipt_number']?.toString(),
    );
  }
}

class PaymentAllocation {
  const PaymentAllocation({
    required this.id,
    required this.paymentId,
    required this.studentBillId,
    required this.allocatedAmount,
  });

  final String id;
  final String paymentId;
  final String studentBillId;
  final double allocatedAmount;

  factory PaymentAllocation.fromJson(Map<String, dynamic> json) {
    return PaymentAllocation(
      id: json['id']?.toString() ?? '',
      paymentId: json['payment_id']?.toString() ?? '',
      studentBillId: json['student_bill_id']?.toString() ?? '',
      allocatedAmount: (json['allocated_amount'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

class FeeStructure {
  const FeeStructure({
    required this.id,
    required this.name,
    this.items = const [],
  });

  final String id;
  final String name;
  final List<FeeItem> items;

  factory FeeStructure.fromJson(Map<String, dynamic> json) {
    return FeeStructure(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      items: (json['items'] as List? ?? [])
          .map((e) => FeeItem.fromJson(e))
          .toList(),
    );
  }

  double get totalAmount => items.fold(0.0, (sum, item) => sum + item.amount);
}

class FeeItem {
  const FeeItem({
    required this.id,
    required this.name,
    required this.amount,
    this.description,
  });

  final String id;
  final String name;
  final double amount;
  final String? description;

  factory FeeItem.fromJson(Map<String, dynamic> json) {
    return FeeItem(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      amount: (json['amount'] as num?)?.toDouble() ?? 0.0,
      description: json['description']?.toString(),
    );
  }
}