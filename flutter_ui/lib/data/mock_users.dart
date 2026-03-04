class Student {
  const Student({
    required this.id,
    required this.name,
    required this.className,
    required this.guardianId,
  });

  final int id;
  final String name;
  final String className;
  final int guardianId;
}

class Guardian {
  const Guardian({
    required this.id,
    required this.name,
    required this.phone,
    required this.activeModules,
  });

  final int id;
  final String name;
  final String phone;
  final List<String> activeModules;
}

class MockUsersData {
  static final List<Guardian> guardians = List.generate(25, (index) {
    final id = index + 1;
    return Guardian(
      id: id,
      name: _guardianNames[index % _guardianNames.length],
      phone: '+254700${(100000 + (id * 377)).toString().padLeft(6, '0')}',
      activeModules: id == 1
          ? const ['Shule Yetu', 'E-Soko', 'E-Pharmacy', 'Just Eat', 'Events']
          : const ['Shule Yetu', 'E-Soko', 'Events'],
    );
  });

  static final List<Student> students = List.generate(30, (index) {
    final id = index + 1;
    return Student(
      id: id,
      name: _studentNames[index % _studentNames.length],
      className:
          'Grade ${((id - 1) % 8) + 1} ${_streams[(id - 1) % _streams.length]}',
      guardianId: ((id - 1) % 25) + 1,
    );
  });

  static Guardian maryOtieno() => guardians.first;

  static final List<String> _streams = ['Blue', 'Green', 'Red', 'Gold'];

  static final List<String> _guardianNames = [
    'Mary Otieno',
    'James Mwangi',
    'Lilian Achieng',
    'Peter Njoroge',
    'Faith Wanjiru',
    'Dennis Kiptoo',
    'Mercy Atieno',
    'John Kamau',
    'Ann Nyambura',
    'David Ouma',
  ];

  static final List<String> _studentNames = [
    'Brian Otieno',
    'Aisha Wanjiku',
    'Kevin Kiptoo',
    'Joy Akinyi',
    'Ian Mwangi',
    'Mercy Chebet',
    'Noel Ochieng',
    'Maya Njeri',
    'Ethan Kibet',
    'Faith Atieno',
  ];
}
