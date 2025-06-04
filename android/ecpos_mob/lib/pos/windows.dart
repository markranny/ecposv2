import 'package:flutter/material.dart';

class windows extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Row(
        children: [
          _buildSidebar(),
          Expanded(child: _buildMainContent()),
        ],
      ),
    );
  }

  Widget _buildSidebar() {
    return Container(
      width: 60,
      color: Colors.indigo[900],
      child: Column(
        children: [
          SizedBox(height: 20),
          _sidebarIcon(Icons.admin_panel_settings, 'ADMIN'),
          _sidebarIcon(Icons.grid_view, ''),
          _sidebarIcon(Icons.shopping_cart, ''),
          _sidebarIcon(Icons.handshake, ''),
          _sidebarIcon(Icons.local_shipping, ''),
          _sidebarIcon(Icons.bar_chart, ''),
          _sidebarIcon(Icons.description, ''),
          _sidebarIcon(Icons.reply, ''),
        ],
      ),
    );
  }

  Widget _sidebarIcon(IconData icon, String label) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16.0),
      child: Column(
        children: [
          Icon(icon, color: Colors.white),
          if (label.isNotEmpty)
            Text(label, style: TextStyle(color: Colors.white, fontSize: 10)),
        ],
      ),
    );
  }

  Widget _buildMainContent() {
    return Column(
      children: [
        _buildHeader(),
        Expanded(child: _buildPurchaseWindows()),
      ],
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text('ANCHETA',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
          Row(
            children: [
              _buildNavTab('Purchase', isActive: true),
              _buildNavTab('Party Cake'),
              _buildNavTab('Reseller'),
              _buildNavTab('Tie Up'),
              _buildNavTab('Advance Order'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildNavTab(String label, {bool isActive = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8.0),
      child: ElevatedButton(
        onPressed: () {},
        child: Text(label),
        style: ElevatedButton.styleFrom(
          backgroundColor: isActive ? Colors.indigo : Colors.white,
          foregroundColor: isActive ? Colors.white : Colors.black,
        ),
      ),
    );
  }

  Widget _buildPurchaseWindows() {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: GridView.count(
        crossAxisCount: 2,
        childAspectRatio: 2,
        mainAxisSpacing: 16,
        crossAxisSpacing: 16,
        children: [
          _buildPurchaseWindow('Purchase Window 1'),
          _buildPurchaseWindow('Purchase Window 2'),
          _buildPurchaseWindow('Purchase Window 3'),
          _buildPurchaseWindow('Purchase Window 4'),
        ],
      ),
    );
  }

  Widget _buildPurchaseWindow(String title) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.indigo,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Center(
        child: Text(
          title,
          style: TextStyle(color: Colors.white, fontSize: 18),
        ),
      ),
    );
  }
}
