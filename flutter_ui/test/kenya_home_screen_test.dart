import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shule_yetu_flutter_ui/screens/kenya_home_screen.dart';

void main() {
  testWidgets('KenyaHomeScreen renders key sections', (tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: KenyaHomeScreen(),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('K1 Navigation'), findsOneWidget);
    expect(find.text('Wallet & Balances'), findsOneWidget);
    expect(find.text('Recommended Modules'), findsOneWidget);
    expect(find.text('Notice Board'), findsOneWidget);
  });
}
