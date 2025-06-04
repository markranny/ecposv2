<script setup>
import { saveAs } from 'file-saver';
import ExcelJS from 'exceljs';
import SuccessButton from '@/Components/Buttons/SuccessButton.vue';
import ExcelIcon from "@/Components/Svgs/Excel.vue";

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    headers: {
        type: Array,
        required: true,
    },
    fileName: {
      type: String,
      default: 'data.xlsx'
    },
    rowNameProps: {
      type: Array,
      required: true,
    }
});

const exportToExcel = async () => {
  try {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Sheet1');
    
    // Define the columns based on the screenshot
    const columns = [
      { header: 'PRODUCTCODE', key: 'itemid', width: 20 },
      { header: 'DESCRIPTION', key: 'itemname', width: 30 },
      { header: 'BARCODE', key: 'barcode', width: 15 },
      { header: 'CATEGORY', key: 'itemgroup', width: 15 },
      { header: 'RETAILGROUP', key: 'specialgroup', width: 15 },
      { header: 'SRP', key: 'price', width: 10 },
      { header: 'MANILA', key: 'manilaprice', width: 10 },
      { header: 'MALL', key: 'mallprice', width: 10 },
      { header: 'GRABFOOD', key: 'grabfoodprice', width: 12 },
      { header: 'FOODPANDA', key: 'foodpandaprice', width: 12 }
    ];
    
    // Add columns to worksheet
    worksheet.columns = columns;
    
    // Style header row
    const headerRow = worksheet.addRow(columns.map(col => col.header));
    headerRow.eachCell((cell) => {
      cell.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: '0000FF' } // Blue background
      };
      cell.font = {
        color: { argb: 'FFFFFF' }, // White text
        bold: true
      };
      cell.alignment = {
        vertical: 'middle',
        horizontal: 'center'
      };
    });
    
    // Add data rows
    props.data.forEach(item => {
      const rowValues = columns.map(column => item[column.key] || '');
      worksheet.addRow(rowValues);
    });
    
    // Apply borders to all cells
    worksheet.eachRow((row, rowNumber) => {
      row.eachCell((cell) => {
        cell.border = {
          top: { style: 'thin' },
          left: { style: 'thin' },
          bottom: { style: 'thin' },
          right: { style: 'thin' }
        };
        
        // Apply center alignment to all cells except description
        if (cell.col !== 2) { // Description column
          cell.alignment = {
            horizontal: 'center',
            vertical: 'middle'
          };
        }
      });
    });
    
    // Generate Excel file
    const buffer = await workbook.xlsx.writeBuffer();
    
    // Save the Excel file
    saveAs(new Blob([buffer]), props.fileName || 'products.xlsx');
  } catch (error) {
    console.error('Error exporting to Excel:', error);
  }
};

// This function is no longer needed with the new export approach
// Keeping the component props intact for backward compatibility
</script>

<template>
    <SuccessButton type="button" @click="exportToExcel">
        <ExcelIcon class="h-4"></ExcelIcon>
    </SuccessButton>
</template>