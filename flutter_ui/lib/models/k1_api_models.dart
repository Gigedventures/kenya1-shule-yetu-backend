/// Kenya1 / Shule Yetu unified API response models.
///
/// Standard response envelope for all Shule Yetu API endpoints.
class K1ApiResponse {
  final String? status;
  final String? message;
  final dynamic data;

  const K1ApiResponse({this.status, this.message, this.data});

  factory K1ApiResponse.fromJson(Map<String, dynamic> json) {
    return K1ApiResponse(
      status: json['status']?.toString(),
      message: json['message']?.toString(),
      data: json['data'],
    );
  }

  bool get isOk => status == 'ok';
}

/// Quick action tile shown on the K1 home screen.
class K1QuickAction {
  final String title;
  final String iconAsset;
  final String? subtitle;
  final String? route;

  const K1QuickAction({
    required this.title,
    required this.iconAsset,
    this.subtitle,
    this.route,
  });

  factory K1QuickAction.fromJson(Map<String, dynamic> json) {
    return K1QuickAction(
      title: json['title']?.toString() ?? '',
      iconAsset: json['icon_asset']?.toString() ?? '',
      subtitle: json['subtitle']?.toString(),
      route: json['route']?.toString(),
    );
  }
}