// services/api_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  // Example login method
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse(
          'http://10.151.10.93:8000/api/login'), // Replace with your API URL http://10.151.10.93:8000/api/items
      headers: {
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(
          response.body); // Adjust based on your API response structure
    } else {
      throw Exception('Failed to login');
    }
  }
}
