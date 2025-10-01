export const konversiStatus = (status) => {
  switch (status) {
    case 'paid':
      return 'Lunas';
    case 'unpaid':
      return 'Belum lunas';
    case 'under-review':
      return 'Sedang ditinjau';
    case 'rejected':
      return 'Ditolak';
    case 'done':
      return 'Selesai';
    default:
      return 'Status tidak diketahui';
  }
};
// Helper methods
export const formatCurrency = (num) => `Rp${Number(num).toLocaleString('id-ID')}`;

export const printWithDocumentPrint = (ORDER, push) => {
  const printWindow = window.open('', '_blank');
  if (!printWindow) {
    push.error({
      title: '❌ Error',
      message: 'Pop-up blocked. Please allow pop-ups for this site.',
    });
    return;
  }

  const htmlContent = `
    <html>
      <head>
        <title>Struk Pembelian</title>
        <style>
          body {
            font-family: monospace;
            font-size: 12px;
            white-space: pre;
            padding: 20px;
          }
          .center {
            text-align: center;
          }
          .bold {
            font-weight: bold;
          }
          .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
          }
        </style>
      </head>
      <body onload="window.print(); window.close();">
        <div class="center bold">SIPEKA</div>

        <br>
        Tanggal     : ${ORDER.created_at || '-'}
        Transaksi   : ${ORDER.transaction_code || '-'}

        <div class="separator"></div>
        <div class="bold">Daftar Belanja:</div>

${ORDER.items
  .map((item) => {
    const name = item.item.name.padEnd(20, ' ').slice(0, 20);
    const qty = `x${item.quantity}`.padEnd(5, ' ');
    const price = formatCurrency(item.item.price).padStart(12, ' ');
    return `${name} ${qty} ${price}`;
  })
  .join('\n')}

        <div class="separator"></div>
        Total Bayar : ${formatCurrency(ORDER.total_amount)}
        <br><br>
        <div class="center">-- Terima Kasih --</div>
      </body>
    </html>
  `;

  printWindow.document.open();
  printWindow.document.write(htmlContent);
  printWindow.document.close();
};
