import 'package:flutter/material.dart';
import '../pos/windows.dart';

void main() {
  runApp(AnchetaAdminApp());
}

class AnchetaAdminApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Ancheta Admin',
      theme: ThemeData(
        primarySwatch: Colors.indigo,
        visualDensity: VisualDensity.adaptivePlatformDensity,
      ),
      home: windows(),
    );
  }
}
