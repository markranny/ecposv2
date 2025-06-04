<script>
import ExcelJS from 'exceljs';

export default {
  props: {
    orders: {
      type: Array,
      required: true,
    },
  },
  computed: {
    flattenedOrders() {
      return this.calculateFlattenedOrders(this.orders);
    }
  },
  methods: {
    async exportToExcel() {
      try {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Flattened Orders');

        // Define columns
        const columns = [
          { header: 'ITEMNAME', key: 'ITEMNAME', width: 20 }
          // Add other columns dynamically based on data
        ];

        // Extract unique STORENAMEs
        const storeNames = new Set();
        this.flattenedOrders.forEach(order => {
          Object.keys(order).forEach(key => {
            if (key !== 'ITEMNAME') {
              storeNames.add(key);
            }
          });
        });

        // Add STORENAME columns
        storeNames.forEach(storeName => {
          columns.push({ header: storeName, key: storeName, width: 10 });
        });

        // Set columns in the worksheet
        worksheet.columns = columns;

        // Add rows
        this.flattenedOrders.forEach(order => {
          const row = {
            ITEMNAME: order.ITEMNAME
          };
          storeNames.forEach(storeName => {
            row[storeName] = order[storeName] || 0; // If storeName doesn't exist, default to 0
          });
          worksheet.addRow(row);
        });

        // Generate Excel file
        const today = new Date();
        const filename = `FlattenedOrders_${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}.xlsx`;
        const buffer = await workbook.xlsx.writeBuffer();
        this.saveExcelFile(buffer, filename);
      } catch (error) {
        console.error('Error exporting to Excel:', error);
      }
    },
    saveExcelFile(buffer, filename) {
      // Convert workbook buffer to Blob and create download link
      const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = filename;
      link.click();
    },
    calculateFlattenedOrders(orders) {
      // Calculate grouped orders and transform into flattenedOrders format
      const groupedOrders = orders.reduce((acc, order) => {
        const STORENAME = order.STORENAME;
        const itemName = order.ITEMNAME;
        const counted = parseInt(order.COUNTED.trim()) || 0;

        if (!acc[itemName]) {
          acc[itemName] = {};
        }

        if (!acc[itemName][STORENAME]) {
          acc[itemName][STORENAME] = 0;
        }

        acc[itemName][STORENAME] += counted;
        return acc;
      }, {});

      return Object.entries(groupedOrders).map(([itemName, storeCount]) => ({
        ITEMNAME: itemName,
        ...Object.entries(storeCount).reduce((acc, [STORENAME, count]) => {
          acc[STORENAME] = count;
          return acc;
        }, {}),
      }));
    }
  }
};
</script>