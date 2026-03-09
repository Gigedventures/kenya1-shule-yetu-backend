class Parent {
  const Parent({
    required this.id,
    required this.name,
    required this.phone,
    required this.email,
    required this.location,
    required this.walletBalance,
    required this.childrenIds,
    required this.activeModules,
    required this.recentOrders,
    required this.deliveryAddress,
    required this.avatarUrl,
  });

  final int id;
  final String name;
  final String phone;
  final String email;
  final String location;
  final double walletBalance;
  final List<int> childrenIds;
  final List<String> activeModules;
  final List<String> recentOrders;
  final String deliveryAddress;
  final String avatarUrl;
}

class Guardian extends Parent {
  const Guardian({
    required super.id,
    required super.name,
    required super.phone,
    required super.email,
    required super.location,
    required super.walletBalance,
    required super.childrenIds,
    required super.activeModules,
    required super.recentOrders,
    required super.deliveryAddress,
    required super.avatarUrl,
  });
}
